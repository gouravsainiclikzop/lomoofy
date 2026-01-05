<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartApiController extends Controller
{
    /**
     * Get cart summary
     * GET /api/cart
     */
    public function index(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $customerId = ($customer && $customer instanceof \App\Models\Customer) ? $customer->id : null;
        
        $sessionId = $request->input('session_id') 
                  ?? $request->query('session_id') 
                  ?? $request->header('X-Session-ID') 
                  ?? session()->getId();
        
        // When user is logged in, check both customer_id and session_id
        // This handles cases where cart was created before login
        $cart = Cart::where(function($query) use ($customerId, $sessionId) {
            if ($customerId) {
                // Check both customer_id and session_id when logged in
                $query->where(function($q) use ($customerId, $sessionId) {
                    $q->where('customer_id', $customerId)
                      ->orWhere('session_id', $sessionId);
                });
            } else {
                // Guest user - only check session_id
                $query->where('session_id', $sessionId);
            }
        })->active()->first();
        
        // If cart found and user is logged in, update cart to use customer_id
        if ($cart && $customerId && !$cart->customer_id) {
            $cart->customer_id = $customerId;
            $cart->session_id = null; // Clear session_id when customer_id is set
            $cart->save();
        }
        
        if (!$cart) {
            return response()->json([
                'success' => true,
                'data' => [
                    'items' => [],
                    'summary' => [
                        'subtotal' => 0,
                        'tax_amount' => 0,
                        'shipping_amount' => 0,
                        'discount_amount' => 0,
                        'total_amount' => 0,
                    ],
                    'coupon' => null,
                ],
            ]);
        }
        
        // Load cart items with relationships
        $cart->load([
            'items.product.primaryImage',
            'items.product.images' => function($q) {
                $q->orderBy('sort_order')->orderBy('id')->limit(1);
            },
            'items.variant.images' => function($q) {
                $q->orderBy('is_primary', 'desc')->orderBy('sort_order')->orderBy('id');
            },
            'items.variant.inventoryStocks',
            'items.variant',
            'coupon'
        ]);
        
        // Automatically adjust cart item quantities to match available stock
        // IMPORTANT: We NEVER delete items when stock is 0 - items are marked as out of stock instead
        // This allows users to see what was in their cart and decide whether to remove items manually
        $quantityAdjusted = false;
        foreach ($cart->items as $item) {
            $product = $item->product;
            $variant = $item->variant;
            $stockSource = $variant ?: $product;
            
            if ($stockSource && $stockSource->manage_stock) {
                // Use warehouse inventory if available, otherwise use stock_quantity
                if ($variant) {
                    $warehouseStock = $variant->inventoryStocks()->sum('quantity');
                    // If has inventory_stocks records, use warehouse total (even if 0)
                    // Otherwise, use variant stock_quantity
                    if ($variant->inventoryStocks()->count() > 0) {
                        $availableStock = $warehouseStock;
                    } else {
                        $availableStock = $variant->stock_quantity ?? 0;
                    }
                    // Calculate stock status from actual quantity (not database value)
                    $stockStatus = ($availableStock > 0) ? 'in_stock' : 'out_of_stock';
                } else {
                    // For products without variants, use product stock_quantity
                    $availableStock = $product->stock_quantity ?? 0;
                    // Calculate stock status from actual quantity (not database value)
                    $stockStatus = ($availableStock > 0) ? 'in_stock' : 'out_of_stock';
                }
                
                // Mark as out of stock if stock is 0
                $isOutOfStock = ($availableStock <= 0);
                
                // If cart quantity exceeds available stock, adjust it (but only if stock is available)
                // Do NOT adjust if stock is 0 - keep the item quantity so it shows as out of stock
                if ($item->quantity > $availableStock && $availableStock > 0 && !$isOutOfStock) {
                    $item->quantity = $availableStock;
                    $item->total_price = $item->unit_price * $availableStock;
                    $item->save();
                    $quantityAdjusted = true;
                }
                // Items with stock 0 are kept in cart and marked as out of stock in the response
                // Users can manually remove them if they want
            }
        }
        
        // Reload items after adjustments
        if ($quantityAdjusted) {
            $cart->load('items');
        }
        
        // Recalculate totals to ensure they're up to date
        $cart->recalculateTotals();
        
        // Reload relationships after recalculation
        $cart->load([
            'items.product.primaryImage',
            'items.product.images' => function($q) {
                $q->orderBy('sort_order')->orderBy('id')->limit(1);
            },
            'items.variant.images' => function($q) {
                $q->orderBy('is_primary', 'desc')->orderBy('sort_order')->orderBy('id');
            },
            'items.variant',
            'coupon'
        ]);
        
        $items = $cart->items->map(function($item) {
            $product = $item->product;
            $variant = $item->variant;
        
            if (!$product) {
                return [
                    'id' => $item->id,
                    'product_id' => null,
                    'product_name' => 'Product unavailable',
                    'product_slug' => null,
                    'variant_id' => $variant ? $variant->id : null,
                    'variant_name' => $variant ? $variant->name : null,
                    'quantity' => $item->quantity,
                    'unit_price' => (float)$item->unit_price,
                    'total_price' => (float)$item->total_price,
                    'image_url' => asset('assets/images/placeholder.jpg'),
                ];
            }
        
            // Get variant image only - don't fallback to product image
            $imageUrl = asset('assets/images/placeholder.jpg'); // Default placeholder
            
            if ($variant && $variant->images && $variant->images->count() > 0) {
                // Try to get primary variant image first
                $primaryVariantImage = $variant->images->where('is_primary', true)->first();
                if ($primaryVariantImage) {
                    $imageUrl = asset('storage/' . $primaryVariantImage->image_path);
                } else {
                    // Use first variant image
                    $firstVariantImage = $variant->images->first();
                    if ($firstVariantImage) {
                        $imageUrl = asset('storage/' . $firstVariantImage->image_path);
                    }
                }
            }
        
            // Check stock availability
            $stockSource = $variant ?: $product;
            $isInStock = true;
            $availableStock = null;
            $manageStock = false;
            $isOutOfStock = false;
            
            // Calculate stock availability - always calculate from actual quantity
            if ($stockSource && $stockSource->manage_stock) {
                $manageStock = true;
                // Use warehouse inventory if available, otherwise use stock_quantity
                if ($variant) {
                    $warehouseStock = $variant->inventoryStocks()->sum('quantity');
                    // If has inventory_stocks records, use warehouse total (even if 0)
                    // Otherwise, use variant stock_quantity
                    if ($variant->inventoryStocks()->count() > 0) {
                        $availableStock = $warehouseStock;
                    } else {
                        $availableStock = $variant->stock_quantity ?? 0;
                    }
                    // Calculate stock status from actual quantity (not database value)
                    $stockStatus = ($availableStock > 0) ? 'in_stock' : 'out_of_stock';
                } else {
                    // For products without variants, use product stock_quantity
                    $availableStock = $product->stock_quantity ?? 0;
                    // Calculate stock status from actual quantity (not database value)
                    $stockStatus = ($availableStock > 0) ? 'in_stock' : 'out_of_stock';
                }
                
                // Mark as out of stock if stock is 0
                $isOutOfStock = ($availableStock <= 0);
                
                // Item is in stock only if available stock meets quantity requirement AND not out of stock
                $isInStock = !$isOutOfStock && ($availableStock >= $item->quantity);
            } else {
                // If not managing stock, check quantity anyway for display purposes
                if ($variant) {
                    $warehouseStock = $variant->inventoryStocks()->sum('quantity');
                    if ($variant->inventoryStocks()->count() > 0) {
                        $availableStock = $warehouseStock;
                    } else {
                        $availableStock = $variant->stock_quantity ?? 0;
                    }
                } else {
                    $availableStock = $product->stock_quantity ?? 0;
                }
                
                // Even if not managing stock, if quantity is 0, mark as out of stock
                $isOutOfStock = ($availableStock <= 0);
                $isInStock = !$isOutOfStock;
            }
            
            // Parse variant attributes for display - get ALL attributes
            $colorValue = '';
            $sizeValue = '';
            $allAttributes = [];
            
            if ($variant && $variant->attributes) {
                $parsed = \App\Http\Controllers\FrontendController::parseVariantAttributes($variant->attributes);
                
                // Get color value from new format
                if ($parsed['color'] && isset($parsed['color']['label'])) {
                    $colorValue = $parsed['color']['label'];
                    $allAttributes[] = ['label' => 'Color', 'value' => $colorValue];
                }
                
                // Get ALL variable attributes (size, length, material, etc.)
                if (isset($parsed['variable']) && is_array($parsed['variable'])) {
                    foreach ($parsed['variable'] as $key => $value) {
                        $attrLabel = ucfirst(str_replace('_', ' ', $key));
                        $attrValue = is_array($value) 
                            ? (isset($value['label']) ? $value['label'] : (isset($value['value']) ? $value['value'] : ''))
                            : (string)$value;
                        
                        if ($attrValue) {
                            $allAttributes[] = ['label' => $attrLabel, 'value' => $attrValue];
                            
                            // Also set sizeValue for backward compatibility
                            if (strtolower($key) === 'size' && empty($sizeValue)) {
                                $sizeValue = $attrValue;
                            }
                            // Also check for length
                            elseif (strtolower($key) === 'length' && empty($sizeValue)) {
                                $sizeValue = $attrValue;
                            }
                        }
                    }
                }
            }
            
            // Build variant display name with attributes
            $variantDisplayName = $variant ? $variant->name : null;
            if ($variantDisplayName && ($colorValue || $sizeValue)) {
                $attrParts = [];
                if ($colorValue) {
                    $attrParts[] = 'Color: ' . $colorValue;
                }
                if ($sizeValue) {
                    $attrParts[] = 'Size: ' . $sizeValue;
                }
                if (!empty($attrParts)) {
                    $variantDisplayName .= ' (' . implode(', ', $attrParts) . ')';
                }
            }
            
            // Get GST settings from product for display
            $gstType = $product->gst_type ?? true;
            $gstPercentage = $product->gst_percentage ?? 0;
            
            // Get variant prices for display
            $originalVariantPrice = null; // This will be used for display fallback
            $variantSalePrice = null;
            $variantPrice = null;
            if ($variant) {
                $variantPrice = $variant->price ?? null; // Base price
                $variantSalePrice = $variant->sale_price ?? null;
                // For display, use sale price if available, otherwise base price
                $originalVariantPrice = $variantSalePrice ?? $variantPrice ?? null;
            }
            
            return [
                'id' => $item->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_slug' => $product->slug,
                'variant_id' => $variant ? $variant->id : null,
                'variant_name' => $variantDisplayName ?: ($variant ? $variant->name : null),
                'color_value' => $colorValue,
                'size_value' => $sizeValue,
                'all_attributes' => $allAttributes, // All variant attributes for complete display
                'quantity' => $item->quantity,
                'unit_price' => (float)$item->unit_price,
                'total_price' => (float)$item->total_price,
                'original_variant_price' => $originalVariantPrice ? (float)$originalVariantPrice : null, // Original variant price for display
                'variant_price' => $variantPrice ? (float)$variantPrice : null,
                'variant_sale_price' => $variantSalePrice ? (float)$variantSalePrice : null,
                'discount_type' => $variant ? ($variant->discount_type ?? null) : null,
                'discount_value' => $variant ? ($variant->discount_value ? (float)$variant->discount_value : null) : null,
                'discount_active' => $variant ? ($variant->discount_active ?? false) : false,
                'image_url' => $imageUrl,
                'in_stock' => $isInStock,
                'out_of_stock' => $isOutOfStock,
                'available_stock' => $availableStock ?? 0,
                'manage_stock' => $manageStock,
                'gst_type' => $gstType,
                'gst_percentage' => $gstPercentage,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items->values()->all(),
                'summary' => [
                    'subtotal' => (float)$cart->subtotal,
                    'tax_amount' => (float)$cart->tax_amount,
                    'shipping_amount' => (float)$cart->shipping_amount,
                    'discount_amount' => (float)$cart->discount_amount,
                    'total_amount' => (float)$cart->total_amount,
                ],
                'coupon' => $cart->coupon ? [
                    'code' => $cart->coupon->code,
                    'discount_type' => $cart->coupon->discount_type,
                    'discount_value' => $cart->coupon->discount_value,
                ] : null,
            ],
        ]);
    }

    /**
     * Get cart count
     * GET /api/cart/count
     */
    public function count(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $customerId = ($customer && $customer instanceof \App\Models\Customer) ? $customer->id : null;
        
        // When user is logged in, ignore session_id from query parameters and use customer_id only
        // For guest users, use session_id
        $sessionId = null;
        if (!$customerId) {
            $sessionId = $request->input('session_id') 
                      ?? $request->query('session_id') 
                      ?? $request->header('X-Session-ID') 
                      ?? session()->getId();
        }
        
        // When user is logged in, check both customer_id and session_id
        // This handles cases where cart was created before login
        $cart = Cart::where(function($query) use ($customerId, $sessionId) {
            if ($customerId) {
                // Check both customer_id and session_id when logged in
                $query->where(function($q) use ($customerId, $sessionId) {
                    $q->where('customer_id', $customerId);
                    // Only check session_id if it was provided (for migration cases)
                    if ($sessionId) {
                        $q->orWhere('session_id', $sessionId);
                    }
                });
            } else {
                // Guest user - only check session_id
                $query->where('session_id', $sessionId);
            }
        })->active()->first();
        
        // If cart found and user is logged in, update cart to use customer_id
        if ($cart && $customerId && !$cart->customer_id) {
            $cart->customer_id = $customerId;
            $cart->session_id = null; // Clear session_id when customer_id is set
            $cart->save();
        }
        
        // Count unique products instead of summing variant quantities
        $count = 0;
        if ($cart && $cart->items) {
            $count = $cart->items->pluck('product_id')->unique()->count();
        }
        
        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * Add item to cart
     * POST /api/cart/items
     */
    public function addItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1|max:100',
            'session_id' => 'nullable|string',
        ]);
        
        $customer = Auth::guard('customer')->user();
        $customerId = ($customer && $customer instanceof \App\Models\Customer) ? $customer->id : null;
        
        $sessionId = $request->input('session_id') 
                  ?? $request->header('X-Session-ID') 
                  ?? session()->getId();
        
        DB::beginTransaction();
        try {
            // When user is logged in, check both customer_id and session_id
            $cart = Cart::where(function($query) use ($customerId, $sessionId) {
                if ($customerId) {
                    // Check both customer_id and session_id when logged in
                    $query->where(function($q) use ($customerId, $sessionId) {
                        $q->where('customer_id', $customerId)
                          ->orWhere('session_id', $sessionId);
                    });
                } else {
                    // Guest user - only check session_id
                    $query->where('session_id', $sessionId);
                }
            })->active()->first();
            
            // If cart found and user is logged in, update cart to use customer_id
            if ($cart && $customerId && !$cart->customer_id) {
                $cart->customer_id = $customerId;
                $cart->session_id = null; // Clear session_id when customer_id is set
                $cart->save();
            }
            
            if (!$cart) {
                $cart = Cart::create([
                    'session_id' => $customerId ? null : $sessionId,
                    'customer_id' => $customerId,
                    'subtotal' => 0,
                    'tax_amount' => 0,
                    'shipping_amount' => 0,
                    'discount_amount' => 0,
                    'total_amount' => 0,
                ]);
            }
            
            // Get product
            $product = \App\Models\Product::find($request->product_id);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'error' => ['message' => 'Product not found'],
                ], 404);
            }
            
            // Get product variant - if variant_id is not provided, use first available variant
            $variant = null;
            $variantId = $request->product_variant_id;
            
            if ($variantId) {
                $variant = \App\Models\ProductVariant::where('id', $variantId)
                    ->where('product_id', $request->product_id)
                    ->first();
                
                if (!$variant) {
                    return response()->json([
                        'success' => false,
                        'error' => ['message' => 'Product variant not found'],
                    ], 404);
                }
            } else {
                // If no variant_id provided, get the first active variant for this product
                $variant = $product->variants()->where('is_active', true)->first();
                
                if (!$variant) {
                    return response()->json([
                        'success' => false,
                        'error' => ['message' => 'No active variant found for this product. Please select a variant.'],
                    ], 404);
                }
                
                $variantId = $variant->id;
            }
            
            // Helper function to calculate GST-inclusive price
            $calculateGstInclusivePrice = function($basePrice, $gstType, $gstPercentage) {
                if ($basePrice <= 0) return 0;
                
                // If gst_type is true, price is already inclusive
                if ($gstType === true || $gstType === 1 || $gstType === '1') {
                    return $basePrice;
                }
                
                // If gst_type is false, add GST
                if ($gstType === false || $gstType === 0 || $gstType === '0') {
                    $gstPercent = $gstPercentage ?? 0;
                    if ($gstPercent > 0) {
                        return $basePrice + ($basePrice * $gstPercent / 100);
                    }
                }
                
                return $basePrice;
            };
            
            // Get GST settings from product
            $gstType = $product->gst_type ?? true; // Default to inclusive
            $gstPercentage = $product->gst_percentage ?? 0;
            
            // Calculate GST-inclusive price
            $basePrice = $variant->sale_price ?? $variant->price ?? 0;
            $gstInclusivePrice = $calculateGstInclusivePrice($basePrice, $gstType, $gstPercentage);
            
            // Check if item already exists
            $existingItem = $cart->items()
                ->where('product_id', $request->product_id)
                ->where('product_variant_id', $variantId)
                ->first();
            
            if ($existingItem) {
                $existingItem->quantity += $request->quantity;
                $existingItem->unit_price = $gstInclusivePrice; // Update to GST-inclusive price
                $existingItem->total_price = $existingItem->quantity * $existingItem->unit_price;
                $existingItem->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $request->product_id,
                    'product_variant_id' => $variantId,
                    'quantity' => $request->quantity,
                    'unit_price' => $gstInclusivePrice,
                    'total_price' => $gstInclusivePrice * $request->quantity,
                ]);
            }
            
            // Recalculate cart totals
            $cart->refresh();
            $cart->recalculateTotals();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Item added to cart',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Failed to add item: ' . $e->getMessage()],
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     * PUT /api/cart/items/{itemId}
     */
    public function updateItem(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
            'session_id' => 'nullable|string',
        ]);
        
        $customer = Auth::guard('customer')->user();
        $customerId = ($customer && $customer instanceof \App\Models\Customer) ? $customer->id : null;
        
        $sessionId = $request->input('session_id') 
                  ?? $request->header('X-Session-ID') 
                  ?? session()->getId();
        
        // When user is logged in, check both customer_id and session_id
        $cart = Cart::where(function($query) use ($customerId, $sessionId) {
            if ($customerId) {
                // Check both customer_id and session_id when logged in
                $query->where(function($q) use ($customerId, $sessionId) {
                    $q->where('customer_id', $customerId)
                      ->orWhere('session_id', $sessionId);
                });
            } else {
                // Guest user - only check session_id
                $query->where('session_id', $sessionId);
            }
        })->active()->first();
        
        // If cart found and user is logged in, update cart to use customer_id
        if ($cart && $customerId && !$cart->customer_id) {
            $cart->customer_id = $customerId;
            $cart->session_id = null;
            $cart->save();
        }
        
        if (!$cart) {
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Cart not found'],
            ], 404);
        }
        
        $item = $cart->items()->find($itemId);
        if (!$item) {
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Cart item not found'],
            ], 404);
        }
        
        $item->quantity = $request->quantity;
        $item->total_price = $item->unit_price * $request->quantity;
        $item->save();
        
        $cart->recalculateTotals();
        
        return response()->json([
            'success' => true,
            'message' => 'Cart item updated',
        ]);
    }

    /**
     * Remove cart item
     * DELETE /api/cart/items/{itemId}
     */
    public function removeItem(Request $request, $itemId)
    {
        $customer = Auth::guard('customer')->user();
        $customerId = ($customer && $customer instanceof \App\Models\Customer) ? $customer->id : null;
        
        $sessionId = $request->input('session_id') 
                  ?? $request->header('X-Session-ID') 
                  ?? session()->getId();
        
        // When user is logged in, check both customer_id and session_id
        $cart = Cart::where(function($query) use ($customerId, $sessionId) {
            if ($customerId) {
                // Check both customer_id and session_id when logged in
                $query->where(function($q) use ($customerId, $sessionId) {
                    $q->where('customer_id', $customerId)
                      ->orWhere('session_id', $sessionId);
                });
            } else {
                // Guest user - only check session_id
                $query->where('session_id', $sessionId);
            }
        })->active()->first();
        
        // If cart found and user is logged in, update cart to use customer_id
        if ($cart && $customerId && !$cart->customer_id) {
            $cart->customer_id = $customerId;
            $cart->session_id = null;
            $cart->save();
        }
        
        if (!$cart) {
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Cart not found'],
            ], 404);
        }
        
        $item = $cart->items()->find($itemId);
        if (!$item) {
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Cart item not found'],
            ], 404);
        }
        
        $item->delete();
        $cart->recalculateTotals();
        
        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
        ]);
    }

    /**
     * Apply coupon
     * POST /api/cart/coupon
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string',
            'session_id' => 'nullable|string',
        ]);
        
        $customer = Auth::guard('customer')->user();
        $customerId = ($customer && $customer instanceof \App\Models\Customer) ? $customer->id : null;
        
        $sessionId = $request->input('session_id') 
                  ?? $request->header('X-Session-ID') 
                  ?? session()->getId();
        
        // When user is logged in, check both customer_id and session_id
        $cart = Cart::where(function($query) use ($customerId, $sessionId) {
            if ($customerId) {
                // Check both customer_id and session_id when logged in
                $query->where(function($q) use ($customerId, $sessionId) {
                    $q->where('customer_id', $customerId)
                      ->orWhere('session_id', $sessionId);
                });
            } else {
                // Guest user - only check session_id
                $query->where('session_id', $sessionId);
            }
        })->active()->with('items')->first();
        
        // If cart found and user is logged in, update cart to use customer_id
        if ($cart && $customerId && !$cart->customer_id) {
            $cart->customer_id = $customerId;
            $cart->session_id = null;
            $cart->save();
        }
        
        if (!$cart) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'CART_NOT_FOUND',
                    'message' => 'Cart not found',
                ],
            ], 404);
        }
        
        // Check if cart has items
        if ($cart->items->count() === 0) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'CART_EMPTY',
                    'message' => 'Cart is empty. Add items to cart before applying coupon.',
                ],
            ], 400);
        }
        
        // Find coupon by code
        $coupon = \App\Models\Coupon::where('code', strtoupper(trim($request->coupon_code)))->first();
        
        if (!$coupon) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COUPON_NOT_FOUND',
                    'message' => 'Coupon code not found. Please check and try again.',
                ],
            ], 404);
        }
        
        // Check if coupon is active
        if (!$coupon->isActive()) {
            $now = \Carbon\Carbon::now();
            if (!$coupon->status) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'COUPON_INACTIVE',
                        'message' => 'This coupon is not active.',
                    ],
                ], 400);
            }
            if ($coupon->start_date && $now->lt($coupon->start_date)) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'COUPON_NOT_STARTED',
                        'message' => 'This coupon is not yet valid.',
                    ],
                ], 400);
            }
            if ($coupon->end_date && $now->gt($coupon->end_date)) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'COUPON_EXPIRED',
                        'message' => 'This coupon has expired.',
                    ],
                ], 400);
            }
        }
        
        // Check if coupon can be used (usage limit)
        if (!$coupon->canBeUsed()) {
            if ($coupon->max_uses && $coupon->uses >= $coupon->max_uses) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'COUPON_LIMIT_REACHED',
                        'message' => 'This coupon has reached its usage limit.',
                    ],
                ], 400);
            }
        }
        
        // Calculate cart subtotal
        $subtotal = $cart->items->sum('total_price');
        
        // Check minimum order amount
        if ($coupon->min_order_amount && $subtotal < $coupon->min_order_amount) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COUPON_MIN_ORDER_NOT_MET',
                    'message' => 'Minimum order amount of ₹' . number_format($coupon->min_order_amount, 2) . ' required for this coupon.',
                ],
            ], 400);
        }
        
        // Apply coupon
        $cart->coupon_code = $coupon->code;
        $cart->save();
        $cart->recalculateTotals();
        
        // Reload cart with coupon relationship
        $cart->load('coupon');
        $discountAmount = $cart->discount_amount;
        
        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'data' => [
                'coupon' => [
                    'code' => $coupon->code,
                    'discount_type' => $coupon->discount_type,
                    'discount_value' => $coupon->discount_value,
                ],
                'discount_amount' => $discountAmount,
            ],
        ]);
    }

    /**
     * Remove coupon
     * DELETE /api/cart/coupon
     */
    public function removeCoupon(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $customerId = ($customer && $customer instanceof \App\Models\Customer) ? $customer->id : null;
        
        $sessionId = $request->input('session_id') 
                  ?? $request->header('X-Session-ID') 
                  ?? session()->getId();
        
        // When user is logged in, check both customer_id and session_id
        $cart = Cart::where(function($query) use ($customerId, $sessionId) {
            if ($customerId) {
                // Check both customer_id and session_id when logged in
                $query->where(function($q) use ($customerId, $sessionId) {
                    $q->where('customer_id', $customerId)
                      ->orWhere('session_id', $sessionId);
                });
            } else {
                // Guest user - only check session_id
                $query->where('session_id', $sessionId);
            }
        })->active()->first();
        
        // If cart found and user is logged in, update cart to use customer_id
        if ($cart && $customerId && !$cart->customer_id) {
            $cart->customer_id = $customerId;
            $cart->session_id = null;
            $cart->save();
        }
        
        if (!$cart) {
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Cart not found'],
            ], 404);
        }
        
        $cart->coupon_code = null;
        $cart->save();
        $cart->recalculateTotals();
        
        return response()->json([
            'success' => true,
            'message' => 'Coupon removed',
        ]);
    }
}
