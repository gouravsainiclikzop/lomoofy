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
        
        $cart = Cart::where(function($query) use ($customerId, $sessionId) {
            if ($customerId) {
                $query->where('customer_id', $customerId);
            } else {
                $query->where('session_id', $sessionId);
            }
        })->active()->first();
        
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
        
        $cart->load([
            'items.product.primaryImage',
            'items.product.images' => function($q) {
                $q->orderBy('sort_order')->orderBy('id')->limit(1);
            },
            'items.variant',
            'coupon'
        ]);
        
        $items = $cart->items->map(function($item) {
            $product = $item->product;
            $variant = $item->variant;
            
            $imageUrl = $product->primaryImage 
                ? asset('storage/' . $product->primaryImage->image_path)
                : ($product->images && $product->images->count() > 0
                    ? asset('storage/' . $product->images->first()->image_path)
                    : asset('frontend/images/product/1.jpg'));
            
            return [
                'id' => $item->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_slug' => $product->slug,
                'variant_id' => $variant ? $variant->id : null,
                'variant_name' => $variant ? $variant->name : null,
                'quantity' => $item->quantity,
                'unit_price' => (float)$item->unit_price,
                'total_price' => (float)$item->total_price,
                'image_url' => $imageUrl,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'summary' => [
                    'subtotal' => (float)$cart->subtotal,
                    'tax_amount' => (float)$cart->tax_amount,
                    'shipping_amount' => (float)$cart->shipping_amount,
                    'discount_amount' => (float)$cart->discount_amount,
                    'total_amount' => (float)$cart->total_amount,
                ],
                'coupon' => $cart->coupon ? [
                    'code' => $cart->coupon->code,
                    'discount_amount' => (float)$cart->discount_amount,
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
        
        $sessionId = $request->input('session_id') 
                  ?? $request->query('session_id') 
                  ?? $request->header('X-Session-ID') 
                  ?? session()->getId();
        
        $cart = Cart::where(function($query) use ($customerId, $sessionId) {
            if ($customerId) {
                $query->where('customer_id', $customerId);
            } else {
                $query->where('session_id', $sessionId);
            }
        })->active()->first();
        
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
                    'subtotal' => 0,
                    'tax_amount' => 0,
                    'shipping_amount' => 0,
                    'discount_amount' => 0,
                    'total_amount' => 0,
                ]);
            }
            
            // Get product variant
            $variant = \App\Models\ProductVariant::find($request->product_variant_id);
            if (!$variant) {
                return response()->json([
                    'success' => false,
                    'error' => ['message' => 'Product variant not found'],
                ], 404);
            }
            
            // Check if item already exists
            $existingItem = $cart->items()
                ->where('product_id', $request->product_id)
                ->where('product_variant_id', $request->product_variant_id)
                ->first();
            
            if ($existingItem) {
                $existingItem->quantity += $request->quantity;
                $existingItem->total_price = $existingItem->quantity * $existingItem->unit_price;
                $existingItem->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $request->product_id,
                    'product_variant_id' => $request->product_variant_id,
                    'quantity' => $request->quantity,
                    'unit_price' => $variant->sale_price ?? $variant->price,
                    'total_price' => ($variant->sale_price ?? $variant->price) * $request->quantity,
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
        
        $cart = Cart::where(function($query) use ($customerId, $sessionId) {
            if ($customerId) {
                $query->where('customer_id', $customerId);
            } else {
                $query->where('session_id', $sessionId);
            }
        })->active()->first();
        
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
        
        $cart = Cart::where(function($query) use ($customerId, $sessionId) {
            if ($customerId) {
                $query->where('customer_id', $customerId);
            } else {
                $query->where('session_id', $sessionId);
            }
        })->active()->first();
        
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
        
        $cart = Cart::where(function($query) use ($customerId, $sessionId) {
            if ($customerId) {
                $query->where('customer_id', $customerId);
            } else {
                $query->where('session_id', $sessionId);
            }
        })->active()->first();
        
        if (!$cart) {
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Cart not found'],
            ], 404);
        }
        
        // TODO: Implement coupon validation and application
        // For now, just set the coupon code
        $cart->coupon_code = $request->coupon_code;
        $cart->save();
        $cart->recalculateTotals();
        
        return response()->json([
            'success' => true,
            'message' => 'Coupon applied',
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
        
        $cart = Cart::where(function($query) use ($customerId, $sessionId) {
            if ($customerId) {
                $query->where('customer_id', $customerId);
            } else {
                $query->where('session_id', $sessionId);
            }
        })->active()->first();
        
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
