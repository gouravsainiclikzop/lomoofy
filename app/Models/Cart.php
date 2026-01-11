<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Cart extends Model
{
    protected $fillable = [
        'session_id',
        'customer_id',
        'coupon_code',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'expires_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    /**
     * Boot method to set expiration date
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($cart) {
            if (empty($cart->expires_at)) {
                $cart->expires_at = Carbon::now()->addDays(30);
            }
        });
    }

    /**
     * Cart items relationship
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Customer relationship
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Coupon relationship
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_code', 'code');
    }

    /**
     * Check if cart is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get total items count
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Recalculate cart totals
     */
    public function recalculateTotals()
    {
        $this->load('items.product', 'items.variant', 'coupon');
        
        // Calculate subtotal using centralized pricing from ProductVariant
        // For exclusive items: base price + GST = inclusive price
        // For inclusive items: use price as-is
        $subtotal = 0;
        
        foreach ($this->items as $item) {
            $product = $item->product;
            $variant = $item->variant;
            if (!$product || !$variant) {
                continue;
            }
            
            // Use centralized pricing method from ProductVariant
            $gstType = $product->gst_type ?? true; // Default to inclusive
            $gstPercentage = $product->gst_percentage ?? 0;
            
            // Get pricing data using centralized method
            $pricing = $variant->getPricingData($gstType, $gstPercentage);
            
            // Use display_final_price which already accounts for GST calculations
            $itemFinalPrice = $pricing['display_final_price'];
            $quantity = $item->quantity ?? 1;
            
            // Add to subtotal (inclusive price after discounts)
            $subtotal += $itemFinalPrice * $quantity;
        }
        
        $this->subtotal = $subtotal;
        
        // Calculate discount from coupon if exists (after subtotal is calculated)
        $discountAmount = 0;
        if ($this->coupon_code && $this->coupon) {
            $coupon = $this->coupon;
            
            // Check if coupon has required methods, otherwise use basic calculation
            if (method_exists($coupon, 'isActive') && method_exists($coupon, 'canBeUsed') && method_exists($coupon, 'calculateDiscount')) {
                if ($coupon->isActive() && $coupon->canBeUsed()) {
                    // Check minimum order amount if property exists
                    if (!property_exists($coupon, 'min_order_amount') || !$coupon->min_order_amount || $subtotal >= $coupon->min_order_amount) {
                        $discountAmount = $coupon->calculateDiscount($subtotal);
                    }
                }
            } else {
                // Fallback to basic discount calculation
                if (property_exists($coupon, 'discount_type') && property_exists($coupon, 'discount_value')) {
                    if ($coupon->discount_type === 'percentage') {
                        $discountAmount = ($subtotal * $coupon->discount_value) / 100;
                    } else {
                        $discountAmount = min($coupon->discount_value, $subtotal);
                    }
                }
            }
        }
        $this->discount_amount = $discountAmount;
        
        // Tax is already included in subtotal (inclusive prices), so set to 0
        $this->tax_amount = 0;
        
        // Calculate shipping
        $allItemsFreeShipping = $this->items->every(function($item) {
            return $item->product && $item->product->free_shipping;
        });
        
        $hasNonShippingItems = $this->items->contains(function($item) {
            return $item->product && !$item->product->requires_shipping;
        });
        
        if ($allItemsFreeShipping || $hasNonShippingItems) {
            $shippingAmount = 0;
        } else {
            $freeShippingThreshold = 0;
            $defaultShippingCost = 0;
            $shippingAmount = $subtotal > $freeShippingThreshold ? 0 : $defaultShippingCost;
        }
        
        $this->shipping_amount = $shippingAmount;
        
        // Calculate total following proper e-commerce flow:
        // 1. Subtotal (sum of all items with inclusive prices)
        // 2. Apply Discount (subtotal - discount)
        // 3. Add Shipping
        // Total = Subtotal - Discount + Shipping (all prices are already inclusive)
        $this->total_amount = $subtotal - $discountAmount + $shippingAmount;
        
        $this->save();
        
        return $this;
    }

    /**
     * Scope for active (non-expired) carts
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', Carbon::now());
        });
    }

    /**
     * Merge guest cart with customer cart on login
     * This method handles the cart merging when a guest user logs in
     */
    public static function mergeGuestCartWithCustomerCart($customerId, $sessionId)
    {
        try {
            
            // Find guest cart (session-based) - try exact match first
            $guestCart = self::where('session_id', $sessionId)
                ->whereNull('customer_id')
                ->active()
                ->with('items')
                ->first();

            // If no exact match found, try to find any recent guest cart for this customer
            // This is a fallback for when session IDs don't match
            if (!$guestCart) {
               
                // Get all guest carts ordered by most recent
                $allGuestCarts = self::whereNull('customer_id')
                    ->active()
                    ->with('items')
                    ->orderBy('updated_at', 'desc')
                    ->get();
                

                // Use the most recent guest cart with items as fallback
                $guestCart = $allGuestCarts->filter(function($cart) {
                    return $cart->items->count() > 0;
                })->first();

                if ($guestCart) {
                   
                }
            }

            if (!$guestCart || $guestCart->items->isEmpty()) {
               
                return null;
            }

            // Find existing customer cart
            $customerCart = self::where('customer_id', $customerId)
                ->whereNull('session_id')
                ->active()
                ->with('items')
                ->first();

            if (!$customerCart) {
                // No existing customer cart, just convert guest cart to customer cart
                
                $guestCart->update([
                    'customer_id' => $customerId,
                    'session_id' => null,
                ]);
                
                $guestCart->recalculateTotals();
                
                
                return $guestCart;
            }


            // Merge items from guest cart to customer cart
            foreach ($guestCart->items as $guestItem) {
                // Check if customer cart already has this product/variant combination
                $existingItem = $customerCart->items->where('product_id', $guestItem->product_id)
                    ->where('product_variant_id', $guestItem->product_variant_id)
                    ->first();

                if ($existingItem) {
                    // Update quantity and total price
                    $newQuantity = $existingItem->quantity + $guestItem->quantity;
                    $existingItem->update([
                        'quantity' => $newQuantity,
                        'total_price' => $newQuantity * $existingItem->unit_price,
                    ]);
                    
                } else {
                    // Create new cart item in customer cart
                    $customerCart->items()->create([
                        'product_id' => $guestItem->product_id,
                        'product_variant_id' => $guestItem->product_variant_id,
                        'quantity' => $guestItem->quantity,
                        'unit_price' => $guestItem->unit_price,
                        'total_price' => $guestItem->total_price,
                        'reserved_stock' => $guestItem->reserved_stock,
                    ]);
                    
                }
            }

            // Copy coupon if customer cart doesn't have one but guest cart does
            if ($guestCart->coupon_code && !$customerCart->coupon_code) {
                $customerCart->update([
                    'coupon_code' => $guestCart->coupon_code,
                ]);
                
            }

            // Recalculate customer cart totals
            $customerCart->recalculateTotals();

            // Delete the guest cart as it's no longer needed
            $guestCart->items()->delete();
            $guestCart->delete();


            return $customerCart;

        } catch (\Exception $e) {
           
            
            // Don't throw exception to avoid breaking login process
            return null;
        }
    }

}
