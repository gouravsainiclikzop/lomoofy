<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderApiController extends Controller
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

        // If authenticated but no customer cart found, check for session_id cart to merge
        if (!$cart && $customerId && $sessionId) {
            $cart = Cart::where('session_id', $sessionId)
                ->whereNull('customer_id')
                ->active()
                ->first();
        }

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
     * Validate cart before checkout
     * GET /api/orders/validate-cart
     */
    public function validateCart(Request $request)
    {
        try {
            $cart = $this->getOrCreateCart($request);
            $cart->load(['items.product', 'items.variant']);

            $validationResult = $this->validateCartInternal($cart, $request->user()?->id);

            if (!$validationResult['valid']) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'CART_VALIDATION_FAILED',
                        'message' => 'Cart validation failed',
                        'errors' => $validationResult['errors'],
                        'warnings' => $validationResult['warnings'],
                    ],
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cart is valid',
                'data' => [
                    'cart_id' => $cart->id,
                    'total_items' => $cart->items->count(),
                    'total_amount' => (float)$cart->total_amount,
                    'warnings' => $validationResult['warnings'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Failed to validate cart: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Internal cart validation method
     */
    private function validateCartInternal(Cart $cart, $customerId = null): array
    {
        $errors = [];
        $warnings = [];

        // Check if cart is empty
        if ($cart->items->isEmpty()) {
            return [
                'valid' => false,
                'errors' => [[
                    'code' => 'CART_EMPTY',
                    'message' => 'Cart is empty',
                ]],
                'warnings' => [],
            ];
        }

        // Validate each item
        foreach ($cart->items as $item) {
            $product = $item->product;
            $variant = $item->variant;
            $stockSource = $variant ?? $product;

            // Check if product is still published
            if ($product->status !== 'published') {
                $errors[] = [
                    'item_id' => $item->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'code' => 'PRODUCT_UNAVAILABLE',
                    'message' => 'Product is no longer available',
                ];
                continue;
            }

            // Check stock availability
            if ($stockSource && $stockSource->manage_stock) {
                $availableStock = $stockSource->stock_quantity ?? 0;
                if ($availableStock < $item->quantity) {
                    $errors[] = [
                        'item_id' => $item->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'variant_id' => $variant?->id,
                        'code' => 'INSUFFICIENT_STOCK',
                        'message' => 'Insufficient stock available',
                        'available_stock' => $availableStock,
                        'requested_quantity' => $item->quantity,
                    ];
                } elseif ($availableStock < $item->reserved_stock) {
                    // Stock was reserved but is now less
                    $warnings[] = [
                        'item_id' => $item->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'code' => 'STOCK_CHANGED',
                        'message' => 'Available stock has changed since item was added',
                        'available_stock' => $availableStock,
                        'reserved_stock' => $item->reserved_stock,
                    ];
                }
            }

            // Check price changes
            $currentPrice = $variant ? ($variant->price ?? $product->price) : $product->price;
            if ($variant && method_exists($variant, 'isOnSale') && $variant->isOnSale()) {
                $currentPrice = $variant->sale_price ?? $currentPrice;
            } elseif ($product->isOnSale()) {
                $currentPrice = $product->sale_price ?? $currentPrice;
            }

            if (abs($currentPrice - $item->unit_price) > 0.01) {
                $warnings[] = [
                    'item_id' => $item->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'code' => 'PRICE_CHANGED',
                    'message' => 'Product price has changed',
                    'old_price' => (float)$item->unit_price,
                    'new_price' => (float)$currentPrice,
                ];
            }
        }

        // Validate coupon if applied
        if ($cart->coupon_code) {
            $coupon = Coupon::where('code', $cart->coupon_code)->first();
            if ($coupon) {
                $validation = $this->validateCoupon($coupon, $cart, $customerId);
                if (!$validation['valid']) {
                    $errors[] = [
                        'code' => $validation['code'],
                        'message' => $validation['message'],
                    ];
                }
            } else {
                $errors[] = [
                    'code' => 'COUPON_NOT_FOUND',
                    'message' => 'Applied coupon is no longer valid',
                ];
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Create order from cart
     * POST /api/orders
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shipping_address' => 'required|array',
            'shipping_address.address_line1' => 'required|string|max:255',
            'shipping_address.city' => 'required|string|max:100',
            'shipping_address.state' => 'required|string|max:100',
            'shipping_address.zip_code' => 'required|string|max:20',
            'shipping_address.country' => 'required|string|max:100',
            'shipping_address.phone' => 'required|string|max:20',
            'billing_address' => 'nullable|array',
            'payment_method' => 'required|in:cod,card,upi,netbanking',
            'payment_status' => 'nullable|in:pending,paid,failed',
            'notes' => 'nullable|string|max:1000',
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
            $cart->load(['items.product', 'items.variant', 'coupon']);

            // Validate cart first
            $validationResult = $this->validateCartInternal($cart);
            if (!$validationResult['valid']) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'CART_VALIDATION_FAILED',
                        'message' => 'Cart validation failed',
                        'errors' => $validationResult['errors'],
                        'warnings' => $validationResult['warnings'],
                    ],
                ], 400);
            }

            // Get customer ID (from authenticated customer)
            $customer = $request->user();
            if (!$customer || !($customer instanceof \App\Models\Customer)) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'AUTHENTICATION_REQUIRED',
                        'message' => 'Authentication required to create order. Please login first.',
                    ],
                ], 401);
            }
            $customerId = $customer->id;

            // Lock inventory and create order
            $order = Order::create([
                'source' => 'online',
                'customer_id' => $customerId,
                'status' => 'pending',
                'subtotal' => $cart->subtotal,
                'tax_amount' => $cart->tax_amount,
                'shipping_amount' => $cart->shipping_amount,
                'discount_amount' => $cart->discount_amount,
                'total_amount' => $cart->total_amount,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_status ?? 'pending',
                // Store addresses as JSON in notes or create separate address table
                // For now, storing in notes field
                'notes' => json_encode([
                    'shipping_address' => $request->shipping_address,
                    'billing_address' => $request->billing_address ?? $request->shipping_address,
                    'order_notes' => $request->notes,
                ]),
            ]);

            // Create order items and deduct stock
            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;
                $variant = $cartItem->variant;
                $stockSource = $variant ?? $product;

                // Final stock check and lock
                if ($stockSource && $stockSource->manage_stock) {
                    $availableStock = $stockSource->stock_quantity ?? 0;
                    if ($availableStock < $cartItem->quantity) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'error' => [
                                'code' => 'INSUFFICIENT_STOCK',
                                'message' => 'Insufficient stock for product: ' . $product->name,
                                'product_id' => $product->id,
                                'available_stock' => $availableStock,
                                'requested_quantity' => $cartItem->quantity,
                            ],
                        ], 400);
                    }

                    // Deduct stock (transaction-safe)
                    $stockSource->decrement('stock_quantity', $cartItem->quantity);
                }

                // Create order item
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'variant_name' => $variant?->name,
                    'variant_sku' => $variant?->sku,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->unit_price,
                    'total_price' => $cartItem->total_price,
                ]);
            }

            // Increment coupon usage if applied
            if ($cart->coupon_code && $cart->coupon) {
                $cart->coupon->increment('uses');
            }

            // Clear cart
            $cart->items()->delete();
            $cart->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'total_amount' => (float)$order->total_amount,
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                    'created_at' => $order->created_at->toIso8601String(),
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ORDER_CREATION_ERROR',
                    'message' => 'Failed to create order: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Validate coupon
     */
    private function validateCoupon(Coupon $coupon, Cart $cart, $customerId = null): array
    {
        if (!$coupon->status) {
            return [
                'valid' => false,
                'code' => 'COUPON_INACTIVE',
                'message' => 'Coupon is not active',
            ];
        }

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

        if ($coupon->max_uses && $coupon->uses >= $coupon->max_uses) {
            return [
                'valid' => false,
                'code' => 'COUPON_LIMIT_REACHED',
                'message' => 'Coupon usage limit has been reached',
            ];
        }

        $cartSubtotal = $cart->items->sum('total_price');
        if ($coupon->min_order_amount && $cartSubtotal < $coupon->min_order_amount) {
            return [
                'valid' => false,
                'code' => 'COUPON_MIN_ORDER_NOT_MET',
                'message' => 'Minimum order amount not met',
                'required_amount' => (float)$coupon->min_order_amount,
                'current_amount' => (float)$cartSubtotal,
            ];
        }

        return ['valid' => true];
    }
}
