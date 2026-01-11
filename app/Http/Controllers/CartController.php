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
                    $product = $item->product;
                    
                    // Use centralized pricing method from ProductVariant
                    $variantPrice = $variant ? ($variant->price ?? 0) : 0;
                    $variantSalePrice = $variant ? ($variant->sale_price ?? null) : null;
                    $discountType = $variant ? ($variant->discount_type ?? null) : null;
                    $discountValue = $variant ? ($variant->discount_value ?? 0) : 0;
                    $discountActive = $variant ? ($variant->discount_active ?? false) : false;
                    
                    $finalPriceAfterDiscount = 0;
                    $variantDiscountAmount = 0;
                    
                    // Initialize pricing data
                    $pricing = null;
                    $gstType = true;
                    $gstPercentage = 0;
                    
                    if ($variant && $product) {
                        // Get GST settings from product
                        $gstType = $product->gst_type ?? true;
                        $gstPercentage = $product->gst_percentage ?? 0;
                        
                        // Use centralized pricing method
                        $pricing = $variant->getPricingData($gstType, $gstPercentage);
                        $finalPriceAfterDiscount = $pricing['final_price'];
                        
                        // Calculate variant discount amount for display
                    if ($discountActive && $discountType && $discountValue > 0) {
                        $priceToDiscount = $variantSalePrice ?? $variantPrice;
                        if ($discountType === 'percentage') {
                            $variantDiscountAmount = ($priceToDiscount * $discountValue) / 100;
                            } elseif (in_array($discountType, ['amount', 'flat'])) {
                            $variantDiscountAmount = $discountValue;
                            }
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
                        'product_slug' => $item->product->slug ?? null,
                        'product_sku' => $item->product->sku,
                        'variant_id' => $item->product_variant_id,
                        'variant_name' => $item->variant?->name,
                        'variant_sku' => $item->variant?->sku,
                        'quantity' => $item->quantity,
                        'unit_price' => (float)$item->unit_price,
                        'total_price' => (float)$item->total_price,
                        'reserved_stock' => $item->reserved_stock,
                        'available_stock' => $availableStock !== null ? (int)$availableStock : null,
                        'manage_stock' => $variant ? ($variant->manage_stock ?? false) : false,
                        'image_url' => $item->product->image_url,
                        // Variant pricing details (for legacy support)
                        'variant_price' => $variantPrice ? (float)$variantPrice : null,
                        'variant_sale_price' => $variantSalePrice ? (float)$variantSalePrice : null,
                        'discount_type' => $discountType,
                        'discount_value' => $discountValue ? (float)$discountValue : null,
                        'discount_active' => $discountActive,
                        'variant_discount_amount' => (float)$variantDiscountAmount,
                        'final_price_after_discount' => (float)$finalPriceAfterDiscount,
                        // GST settings (for legacy fallback)
                        'gst_type' => $gstType,
                        'gst_percentage' => (float)$gstPercentage,
                        // Full pricing data from centralized method (preferred)
                        'pricing' => $pricing ? [
                            'base_price' => (float)$pricing['base_price'],
                            'sale_price' => $pricing['sale_price'] ? (float)$pricing['sale_price'] : null,
                            'final_price' => (float)$pricing['final_price'],
                            'display_base_price' => (float)$pricing['display_base_price'],
                            'display_final_price' => (float)$pricing['display_final_price'],
                            'display_savings' => (float)$pricing['display_savings'],
                            'display_base_price_rounded' => (float)($pricing['display_base_price_rounded'] ?? round($pricing['display_base_price'])),
                            'display_final_price_rounded' => (float)($pricing['display_final_price_rounded'] ?? round($pricing['display_final_price'])),
                            'display_savings_rounded' => (float)($pricing['display_savings_rounded'] ?? round($pricing['display_savings'])),
                            'show_base_price' => $pricing['show_base_price'],
                            'tax_label' => $pricing['tax_label'],
                            'discount_badge_text' => $pricing['discount_badge_text'],
                            'has_discount_or_sale' => $pricing['has_discount_or_sale'],
                            'is_on_sale' => $pricing['is_on_sale'],
                            'has_active_discount' => $pricing['has_active_discount'],
                            'total_savings' => (float)$pricing['total_savings'],
                            'gst_amount' => (float)$pricing['gst_amount'],
                        ] : null,
                        // Size and color attributes for display
                        'size_value' => $item->size_value,
                        'color_value' => $item->color_value,
                        'all_attributes' => $item->all_attributes ?? [],
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
