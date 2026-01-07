<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display carts listing page
     */
    public function index()
    {
        return view('admin.carts.index');
    }

    /**
     * Get carts data for DataTables
     */
    public function getData(Request $request)
    {
        $query = Cart::with(['customer', 'items.product', 'items.variant', 'coupon']);

        // Search
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('session_id', 'like', "%{$search}%")
                  ->orWhere('coupon_code', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by status (active/expired)
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'expired') {
                $query->where('expires_at', '<=', now());
            }
        }

        $totalRecords = Cart::count();
        $filteredRecords = $query->count();

        $carts = $query->orderBy('created_at', 'desc')
                      ->skip($request->start ?? 0)
                      ->take($request->length ?? 10)
                      ->get();

        $data = $carts->map(function($cart) {
            $totalItems = $cart->items->sum('quantity');
            $isExpired = $cart->isExpired();
            
            return [
                'id' => $cart->id,
                'customer_name' => $cart->customer ? $cart->customer->full_name : 'Guest',
                'customer_email' => $cart->customer ? $cart->customer->email : ($cart->session_id ? 'Session: ' . substr($cart->session_id, 0, 8) : 'N/A'),
                'session_id' => $cart->session_id ? substr($cart->session_id, 0, 12) . '...' : 'N/A',
                'total_items' => $totalItems,
                'subtotal' => (float)$cart->subtotal,
                'discount_amount' => (float)$cart->discount_amount,
                'tax_amount' => (float)$cart->tax_amount,
                'shipping_amount' => (float)$cart->shipping_amount,
                'total_amount' => (float)$cart->total_amount,
                'coupon_code' => $cart->coupon_code ?? 'N/A',
                'status' => $isExpired ? 'expired' : 'active',
                'expires_at' => $cart->expires_at ? $cart->expires_at->format('M d, Y H:i') : 'N/A',
                'created_at' => $cart->created_at->format('M d, Y H:i'),
            ];
        });

        return response()->json([
            'draw' => intval($request->get('draw', 1)),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    /**
     * Get cart details
     */
    public function show($id)
    {
        $cart = Cart::with(['customer', 'items.product', 'items.variant.inventoryStocks'])
                   ->findOrFail($id);
        
        // Load coupon separately
        if ($cart->coupon_code) {
            $cart->load(['coupon' => function($q) use ($cart) {
                $q->where('code', $cart->coupon_code);
            }]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $cart->id,
                'customer' => $cart->customer ? [
                    'id' => $cart->customer->id,
                    'name' => $cart->customer->full_name,
                    'email' => $cart->customer->email,
                    'phone' => $cart->customer->phone,
                ] : null,
                'session_id' => $cart->session_id,
                'coupon' => $cart->coupon ? [
                    'code' => $cart->coupon->code,
                    'discount_type' => $cart->coupon->discount_type,
                    'discount_value' => (float)$cart->coupon->discount_value,
                ] : null,
                'items' => $cart->items->map(function($item) {
                    $variant = $item->variant;
                    
                    // Calculate final price after variant discount
                    $calculateItemFinalPrice = function($variant) {
                        if (!$variant) {
                            return 0;
                        }
                        
                        $basePrice = $variant->price ?? 0;
                        $salePrice = $variant->sale_price ?? null;
                        $discountType = $variant->discount_type ?? '';
                        $discountValue = $variant->discount_value ?? 0;
                        $discountActive = $variant->discount_active ?? false;
                        
                        // Round base price
                        $basePrice = round($basePrice);
                        
                        // Round sale price if it exists
                        $roundedSalePrice = null;
                        if ($salePrice !== null) {
                            $roundedSalePrice = round($salePrice);
                        }
                        
                        // Calculate final price
                        $priceToDiscount = $basePrice;
                        if ($roundedSalePrice !== null && $roundedSalePrice < $basePrice) {
                            $priceToDiscount = $roundedSalePrice;
                        }
                        
                        $finalPrice = $priceToDiscount;
                        
                        // Apply discount if active
                        if ($discountActive && $discountType && $discountValue > 0) {
                            if ($discountType === 'percentage') {
                                $discountAmount = ($priceToDiscount * $discountValue) / 100;
                                $finalPrice = max(0, $priceToDiscount - $discountAmount);
                            } elseif ($discountType === 'amount' || $discountType === 'flat') {
                                $finalPrice = max(0, $priceToDiscount - $discountValue);
                            }
                        } elseif ($roundedSalePrice !== null && $roundedSalePrice < $basePrice) {
                            $finalPrice = $roundedSalePrice;
                        }
                        
                        // Round final price
                        return round($finalPrice);
                    };
                    
                    $variantPrice = $variant ? ($variant->price ?? 0) : 0;
                    $variantSalePrice = $variant ? ($variant->sale_price ?? null) : null;
                    $discountType = $variant ? ($variant->discount_type ?? null) : null;
                    $discountValue = $variant ? ($variant->discount_value ?? 0) : 0;
                    $discountActive = $variant ? ($variant->discount_active ?? false) : false;
                    
                    $finalPriceAfterDiscount = $calculateItemFinalPrice($variant);
                    $variantDiscountAmount = 0;
                    
                    // Calculate variant discount amount
                    if ($discountActive && $discountType && $discountValue > 0) {
                        $priceToDiscount = $variantSalePrice ?? $variantPrice;
                        if ($discountType === 'percentage') {
                            $variantDiscountAmount = ($priceToDiscount * $discountValue) / 100;
                        } elseif ($discountType === 'amount' || $discountType === 'flat') {
                            $variantDiscountAmount = $discountValue;
                        }
                    }
                    
                    // Calculate available stock (same logic as inventory page)
                    $availableStock = null;
                    if ($variant) {
                        $product = $item->product;
                        $stockSource = $variant;
                        
                        if ($stockSource && $stockSource->manage_stock) {
                            // Use warehouse inventory if available, otherwise use variant stock_quantity
                            $warehouseStock = $variant->inventoryStocks()->sum('quantity');
                            $warehouseReserved = $variant->inventoryStocks()->sum('reserved_quantity');
                            
                            // If has inventory_stocks records, use warehouse total (even if 0)
                            // Otherwise, use variant stock_quantity
                            if ($variant->inventoryStocks()->count() > 0) {
                                $availableStock = max(0, $warehouseStock - $warehouseReserved);
                            } else {
                                $availableStock = $variant->stock_quantity ?? 0;
                            }
                        } else {
                            // If not managing stock, still show available stock for display purposes
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
                        }
                    } else {
                        // For products without variants, use product stock_quantity
                        $product = $item->product;
                        $availableStock = $product->stock_quantity ?? 0;
                    }
                    
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
                        'reserved_stock' => $item->reserved_stock,
                        'available_stock' => $availableStock !== null ? (int)$availableStock : null,
                        'image_url' => $item->product->image_url,
                        // Variant pricing details
                        'variant_price' => $variantPrice ? (float)$variantPrice : null,
                        'variant_sale_price' => $variantSalePrice ? (float)$variantSalePrice : null,
                        'discount_type' => $discountType,
                        'discount_value' => $discountValue ? (float)$discountValue : null,
                        'discount_active' => $discountActive,
                        'variant_discount_amount' => (float)$variantDiscountAmount,
                        'final_price_after_discount' => (float)$finalPriceAfterDiscount,
                    ];
                }),
                'summary' => [
                    'subtotal' => (float)$cart->subtotal,
                    'tax_amount' => (float)$cart->tax_amount,
                    'shipping_amount' => (float)$cart->shipping_amount,
                    'discount_amount' => (float)$cart->discount_amount,
                    'total_amount' => (float)$cart->total_amount,
                    'total_items' => $cart->items->sum('quantity'),
                ],
                'status' => $cart->isExpired() ? 'expired' : 'active',
                'expires_at' => $cart->expires_at ? $cart->expires_at->toIso8601String() : null,
                'created_at' => $cart->created_at->toIso8601String(),
                'updated_at' => $cart->updated_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Delete cart
     */
    public function destroy($id)
    {
        $cart = Cart::findOrFail($id);
        
        // Release all reserved stock
        foreach ($cart->items as $item) {
            $stockSource = $item->getStockSource();
            if ($stockSource && $stockSource->manage_stock && $item->reserved_stock > 0) {
                $stockSource->increment('stock_quantity', $item->reserved_stock);
            }
        }
        
        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart deleted successfully',
        ]);
    }
}
