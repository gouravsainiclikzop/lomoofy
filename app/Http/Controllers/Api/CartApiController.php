<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CartApiController extends Controller
{
    /**
     * Get or create cart for user/session
     */
    private function getOrCreateCart(Request $request): Cart
    {
        // Get authenticated customer (if using Sanctum token)
        $customer = $request->user();
        $customerId = ($customer && $customer instanceof \App\Models\Customer) ? $customer->id : null;
        
        // Get session_id from request body, query parameter, header, or generate new one
        // Priority: request body > query parameter > header > session
        $sessionId = $request->input('session_id') 
                  ?? $request->query('session_id') 
                  ?? $request->header('X-Session-ID') 
                  ?? session()->getId();

        // If customer is authenticated, prioritize customer_id
        // Otherwise, use session_id for guest carts
        $cart = Cart::where(function($query) use ($customerId, $sessionId) {
            if ($customerId) {
                $query->where('customer_id', $customerId);
            } else {
                $query->where('session_id', $sessionId);
            }
        })->active()->first();

        if (!$cart) {
            $cart = Cart::create([
                'session_id' => $customerId ? null : $sessionId,
                'customer_id' => $customerId,
                'expires_at' => now()->addDays(30),
            ]);
        } else {
            // If cart exists with session_id but customer is now authenticated, link it to customer
            if ($customerId && !$cart->customer_id) {
                $cart->customer_id = $customerId;
                $cart->session_id = null; // Clear session_id when linked to customer
                $cart->save();
            }
        }

        return $cart;
    }

    /**
     * Get cart summary
     * GET /api/cart
     */
    public function index(Request $request)
    {
        try {
            $cart = $this->getOrCreateCart($request);
            $cart->load(['items.product', 'items.variant', 'coupon']);

            $this->recalculateCart($cart);

            return response()->json([
                'success' => true,
                'data' => [
                    'cart_id' => $cart->id,
                    'items' => $cart->items->map(function($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->name,
                            'product_sku' => $item->product->sku,
                            'variant_id' => $item->product_variant_id,
                            'variant_name' => $item->variant?->name,
                            'variant_sku' => $item->variant?->sku,
                            'quantity' => $item->quantity,
                            'unit_price' => (float)$item->unit_price,
                            'total_price' => (float)$item->total_price,
                            'image_url' => $item->product->image_url,
                            'available_stock' => $item->getAvailableStock(),
                        ];
                    }),
                    'summary' => [
                        'subtotal' => (float)$cart->subtotal,
                        'tax_amount' => (float)$cart->tax_amount,
                        'shipping_amount' => (float)$cart->shipping_amount,
                        'discount_amount' => (float)$cart->discount_amount,
                        'total_amount' => (float)$cart->total_amount,
                        'total_items' => $cart->total_items,
                    ],
                    'coupon' => $cart->coupon_code ? [
                        'code' => $cart->coupon_code,
                        'discount_type' => $cart->coupon->discount_type,
                        'discount_value' => (float)$cart->coupon->discount_value,
                        'discount_amount' => (float)$cart->discount_amount,
                    ] : null,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'CART_FETCH_ERROR',
                    'message' => 'Failed to fetch cart: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Add item to cart
     * POST /api/cart/items
     */
    public function addItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ],
            ], 422);
        }

        DB::beginTransaction();
        try {
            $cart = $this->getOrCreateCart($request);
            $product = Product::findOrFail($request->product_id);
            
            // Get variant - if not provided, get the first active variant (all products have variants)
            if ($request->product_variant_id) {
                $variant = ProductVariant::where('id', $request->product_variant_id)
                    ->where('product_id', $product->id)
                    ->where('is_active', true)
                    ->first();
                
                if (!$variant) {
                    return response()->json([
                        'success' => false,
                        'error' => [
                            'code' => 'VARIANT_NOT_FOUND',
                            'message' => 'Product variant not found or does not belong to this product, or variant is inactive',
                            'product_id' => $product->id,
                            'product_variant_id' => $request->product_variant_id,
                        ],
                    ], 404);
                }
            } else {
                // Get first active variant for the product (simple products have a single variant)
                $variant = $product->variants()->where('is_active', true)->orderBy('sort_order')->first();
                
                if (!$variant) {
                    return response()->json([
                        'success' => false,
                        'error' => [
                            'code' => 'NO_VARIANT_AVAILABLE',
                            'message' => 'No active variant available for this product',
                            'product_id' => $product->id,
                        ],
                    ], 400);
                }
            }

            // Validate product is published
            if ($product->status !== 'published') {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'PRODUCT_UNAVAILABLE',
                        'message' => 'Product is not available for purchase',
                    ],
                ], 400);
            }

            // Get stock source (always use variant since all products have variants)
            $stockSource = $variant;
            $requestedQuantity = $request->quantity;

            // Check existing cart item
            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->where('product_variant_id', $request->product_variant_id)
                ->first();

            if ($existingItem) {
                $newQuantity = $existingItem->quantity + $requestedQuantity;
            } else {
                $newQuantity = $requestedQuantity;
            }

            // Validate inventory availability
            if ($stockSource->manage_stock) {
                $availableStock = $stockSource->stock_quantity ?? 0;
                
                if ($availableStock < $newQuantity) {
                    return response()->json([
                        'success' => false,
                        'error' => [
                            'code' => 'INSUFFICIENT_STOCK',
                            'message' => 'Insufficient stock available',
                            'available_stock' => $availableStock,
                            'requested_quantity' => $newQuantity,
                        ],
                    ], 400);
                }
            }

            // Calculate price (always use variant since all products have variants)
            $unitPrice = $variant->price ?? $product->price;
            if (method_exists($variant, 'isOnSale') && $variant->isOnSale()) {
                $unitPrice = $variant->sale_price ?? $unitPrice;
            } elseif ($product->isOnSale()) {
                $unitPrice = $product->sale_price ?? $unitPrice;
            }

            // Add or update cart item
            if ($existingItem) {
                $quantityIncrease = $requestedQuantity;
                $existingItem->quantity = $newQuantity;
                $existingItem->unit_price = $unitPrice;
                $existingItem->total_price = $unitPrice * $newQuantity;
                $existingItem->reserved_stock += $quantityIncrease;
                $existingItem->save();
                $cartItem = $existingItem;
            } else {
                $cartItem = CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $newQuantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $unitPrice * $newQuantity,
                    'reserved_stock' => $newQuantity,
                ]);
            }

            // Reserve stock
            if ($stockSource->manage_stock) {
                $stockSource->decrement('stock_quantity', $requestedQuantity);
            }

            // Recalculate cart
            $this->recalculateCart($cart);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully',
                'data' => [
                    'cart_item_id' => $cartItem->id,
                    'product_id' => $cartItem->product_id,
                    'product_variant_id' => $cartItem->product_variant_id,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => (float)$cartItem->unit_price,
                    'total_price' => (float)$cartItem->total_price,
                    'variant' => [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'sku' => $variant->sku,
                        'price' => (float)($variant->price ?? $product->price),
                    ],
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'CART_ADD_ERROR',
                    'message' => 'Failed to add item to cart: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     * PUT /api/cart/items/{itemId}
     * 
     * Supports two ways to identify the cart item:
     * 1. By cart_item_id: Use the itemId parameter (numeric cart_item_id from GET /api/cart response)
     * 2. By variant_id: Provide variant_id in request body (more intuitive for frontend)
     */
    public function updateItem(Request $request, $itemId)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
            'variant_id' => 'nullable|exists:product_variants,id',
            'product_variant_id' => 'nullable|exists:product_variants,id', // Alias for variant_id
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ],
            ], 422);
        }

        DB::beginTransaction();
        try {
            $cart = $this->getOrCreateCart($request);
            
            // Try to find cart item - support both cart_item_id and variant_id
            $cartItem = null;
            
            // Priority 1: If variant_id is provided in request body, find by variant_id
            $variantId = $request->input('variant_id') ?? $request->input('product_variant_id');
            if ($variantId) {
                $cartItem = CartItem::where('cart_id', $cart->id)
                    ->where('product_variant_id', $variantId)
                    ->first();
            }
            
            // Priority 2: If not found and itemId is numeric, try to find by cart_item_id
            if (!$cartItem && is_numeric($itemId)) {
                $cartItem = CartItem::where('cart_id', $cart->id)
                    ->where('id', $itemId)
                    ->first();
            }
            
            if (!$cartItem) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'CART_ITEM_NOT_FOUND',
                        'message' => 'Cart item not found. It may have been removed or does not belong to this cart.',
                        'cart_id' => $cart->id,
                        'item_id' => $itemId,
                        'variant_id' => $variantId,
                        'hint' => 'Provide either cart_item_id in URL or variant_id in request body',
                    ],
                ], 404);
            }
            
            $oldQuantity = $cartItem->quantity;
            $newQuantity = $request->quantity;
            $quantityDiff = $newQuantity - $oldQuantity;

            // Get stock source
            $stockSource = $cartItem->getStockSource();

            // Validate inventory if increasing quantity
            if ($quantityDiff > 0 && $stockSource && $stockSource->manage_stock) {
                $availableStock = $stockSource->stock_quantity ?? 0;
                
                if ($availableStock < $quantityDiff) {
                    return response()->json([
                        'success' => false,
                        'error' => [
                            'code' => 'INSUFFICIENT_STOCK',
                            'message' => 'Insufficient stock available',
                            'available_stock' => $availableStock,
                            'requested_increase' => $quantityDiff,
                        ],
                    ], 400);
                }

                // Reserve additional stock
                $stockSource->decrement('stock_quantity', $quantityDiff);
                $cartItem->reserved_stock += $quantityDiff;
            } elseif ($quantityDiff < 0 && $stockSource && $stockSource->manage_stock) {
                // Release stock if decreasing quantity
                $stockSource->increment('stock_quantity', abs($quantityDiff));
                $cartItem->reserved_stock = max(0, $cartItem->reserved_stock - abs($quantityDiff));
            }

            // Update cart item
            $cartItem->quantity = $newQuantity;
            $cartItem->total_price = $cartItem->unit_price * $newQuantity;
            $cartItem->save();

            // Recalculate cart
            $this->recalculateCart($cart);

            DB::commit();

            // Load relationships for response
            $cartItem->load('variant', 'product');
            
            return response()->json([
                'success' => true,
                'message' => 'Cart item updated successfully',
                'data' => [
                    'cart_item_id' => $cartItem->id,
                    'product_id' => $cartItem->product_id,
                    'product_variant_id' => $cartItem->product_variant_id,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => (float)$cartItem->unit_price,
                    'total_price' => (float)$cartItem->total_price,
                    'variant' => $cartItem->variant ? [
                        'id' => $cartItem->variant->id,
                        'name' => $cartItem->variant->name,
                        'sku' => $cartItem->variant->sku,
                    ] : null,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'CART_UPDATE_ERROR',
                    'message' => 'Failed to update cart item: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Remove item from cart
     * DELETE /api/cart/items/{itemId}
     * 
     * Supports two ways to identify the cart item:
     * 1. By cart_item_id: Use the itemId parameter (numeric cart_item_id from GET /api/cart response)
     * 2. By variant_id: Provide variant_id in query parameter or request body (more intuitive for frontend)
     */
    public function removeItem(Request $request, $itemId)
    {
        DB::beginTransaction();
        try {
            $cart = $this->getOrCreateCart($request);
            
            // Try to find cart item - support both cart_item_id and variant_id
            $cartItem = null;
            
            // Priority 1: If variant_id is provided in query or request body, find by variant_id
            $variantId = $request->input('variant_id') 
                      ?? $request->input('product_variant_id') 
                      ?? $request->query('variant_id') 
                      ?? $request->query('product_variant_id');
            
            if ($variantId) {
                $cartItem = CartItem::where('cart_id', $cart->id)
                    ->where('product_variant_id', $variantId)
                    ->first();
            }
            
            // Priority 2: If not found and itemId is numeric, try to find by cart_item_id
            if (!$cartItem && is_numeric($itemId)) {
                $cartItem = CartItem::where('cart_id', $cart->id)
                    ->where('id', $itemId)
                    ->first();
            }
            
            if (!$cartItem) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'CART_ITEM_NOT_FOUND',
                        'message' => 'Cart item not found. It may have been removed or does not belong to this cart.',
                        'cart_id' => $cart->id,
                        'item_id' => $itemId,
                        'variant_id' => $variantId,
                        'hint' => 'Provide either cart_item_id in URL or variant_id in query/body',
                    ],
                ], 404);
            }

            // Release reserved stock
            $stockSource = $cartItem->getStockSource();
            if ($stockSource && $stockSource->manage_stock && $cartItem->reserved_stock > 0) {
                $stockSource->increment('stock_quantity', $cartItem->reserved_stock);
            }

            $cartItem->delete();

            // Recalculate cart
            $this->recalculateCart($cart);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'CART_REMOVE_ERROR',
                    'message' => 'Failed to remove item from cart: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Apply coupon to cart
     * POST /api/cart/coupon
     */
    public function applyCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ],
            ], 422);
        }

        try {
            $cart = $this->getOrCreateCart($request);
            $coupon = Coupon::where('code', strtoupper($request->coupon_code))->first();

            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'COUPON_NOT_FOUND',
                        'message' => 'Coupon code not found',
                    ],
                ], 404);
            }

            // Validate coupon
            $validation = $this->validateCoupon($coupon, $cart, $request->user()?->id);
            if (!$validation['valid']) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => $validation['code'],
                        'message' => $validation['message'],
                    ],
                ], 400);
            }

            // Apply coupon
            $cart->coupon_code = $coupon->code;
            $cart->save();

            // Recalculate cart
            $this->recalculateCart($cart);

            return response()->json([
                'success' => true,
                'message' => 'Coupon applied successfully',
                'data' => [
                    'coupon_code' => $coupon->code,
                    'discount_type' => $coupon->discount_type,
                    'discount_value' => (float)$coupon->discount_value,
                    'discount_amount' => (float)$cart->discount_amount,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COUPON_APPLY_ERROR',
                    'message' => 'Failed to apply coupon: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Remove coupon from cart
     * DELETE /api/cart/coupon
     */
    public function removeCoupon(Request $request)
    {
        try {
            $cart = $this->getOrCreateCart($request);
            $cart->coupon_code = null;
            $cart->save();

            // Recalculate cart
            $this->recalculateCart($cart);

            return response()->json([
                'success' => true,
                'message' => 'Coupon removed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COUPON_REMOVE_ERROR',
                    'message' => 'Failed to remove coupon: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Validate coupon
     */
    private function validateCoupon(Coupon $coupon, Cart $cart, $customerId = null): array
    {
        // Check if coupon is active
        if (!$coupon->status) {
            return [
                'valid' => false,
                'code' => 'COUPON_INACTIVE',
                'message' => 'Coupon is not active',
            ];
        }

        // Check expiry
        if ($coupon->end_date && $coupon->end_date->isPast()) {
            return [
                'valid' => false,
                'code' => 'COUPON_EXPIRED',
                'message' => 'Coupon has expired',
            ];
        }

        if ($coupon->start_date && $coupon->start_date->isFuture()) {
            return [
                'valid' => false,
                'code' => 'COUPON_NOT_STARTED',
                'message' => 'Coupon is not yet valid',
            ];
        }

        // Check usage limits
        if ($coupon->max_uses && $coupon->uses >= $coupon->max_uses) {
            return [
                'valid' => false,
                'code' => 'COUPON_LIMIT_REACHED',
                'message' => 'Coupon usage limit has been reached',
            ];
        }

        // Check minimum order amount
        $cartSubtotal = $cart->items->sum('total_price');
        if ($coupon->min_order_amount && $cartSubtotal < $coupon->min_order_amount) {
            return [
                'valid' => false,
                'code' => 'COUPON_MIN_ORDER_NOT_MET',
                'message' => 'Minimum order amount not met. Required: ₹' . number_format($coupon->min_order_amount, 2),
                'required_amount' => (float)$coupon->min_order_amount,
                'current_amount' => (float)$cartSubtotal,
            ];
        }

        return ['valid' => true];
    }

    /**
     * Recalculate cart totals
     */
    private function recalculateCart(Cart $cart): void
    {
        $cart->load('items', 'coupon');

        // Calculate subtotal
        $subtotal = $cart->items->sum('total_price');
        $cart->subtotal = $subtotal;

        // Calculate discount
        $discountAmount = 0;
        if ($cart->coupon_code && $cart->coupon) {
            $coupon = $cart->coupon;
            if ($coupon->discount_type === 'percentage') {
                $discountAmount = ($subtotal * $coupon->discount_value) / 100;
            } else {
                $discountAmount = min($coupon->discount_value, $subtotal);
            }
        }
        $cart->discount_amount = $discountAmount;

        // Calculate tax
        // TODO: Make this configurable via settings/configuration table
        $taxRate = 0; // Can be moved to config/settings (0 = 0%, 0.10 = 10%, etc.)
        $taxableAmount = $subtotal - $discountAmount;
        $taxAmount = $taxableAmount * $taxRate;
        $cart->tax_amount = $taxAmount;

        // Calculate shipping
        // Check if all items have free shipping
        $allItemsFreeShipping = $cart->items->every(function($item) {
            return $item->product && $item->product->free_shipping;
        });
        
        // Check if any item doesn't require shipping
        $hasNonShippingItems = $cart->items->contains(function($item) {
            return $item->product && !$item->product->requires_shipping;
        });
        
        if ($allItemsFreeShipping || $hasNonShippingItems) {
            // If all items have free shipping OR cart has digital/non-shipping items, shipping is 0
            $shippingAmount = 0;
        } else {
            // Default shipping calculation: free if subtotal > 1000, else 50
            // TODO: Make this configurable via settings/configuration table
            $freeShippingThreshold = 0; // Can be moved to config/settings
            $defaultShippingCost = 0; // Can be moved to config/settings
            $shippingAmount = $subtotal > $freeShippingThreshold ? 0 : $defaultShippingCost;
        }
        
        $cart->shipping_amount = $shippingAmount;

        // Calculate total
        $cart->total_amount = $subtotal - $discountAmount + $taxAmount + $shippingAmount;
        $cart->save();
    }
}
