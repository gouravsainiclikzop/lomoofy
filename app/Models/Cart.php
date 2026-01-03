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
        
        // Calculate subtotal (base price - no computation for inclusive items)
        $subtotal = 0;
        
        // Calculate GST from cart items
        // For tax-inclusive items: tax is already included in price, don't add it again
        // For tax-exclusive items: calculate tax and add it separately
        $taxAmount = 0;
        
        foreach ($this->items as $item) {
            $product = $item->product;
            $variant = $item->variant;
            if (!$product) {
                continue;
            }
            
            // Get GST settings from product
            $gstType = $product->gst_type ?? true; // Default to inclusive
            $gstPercentage = $product->gst_percentage ?? 0;
            
            // Get original variant price (sale_price or price)
            $originalVariantPrice = null;
            if ($variant) {
                $originalVariantPrice = $variant->sale_price ?? $variant->price ?? null;
            }
            
            // Current item unit price (stored in cart, already GST-inclusive)
            $itemUnitPrice = $item->unit_price ?? 0;
            $quantity = $item->quantity ?? 1;
            
            // Calculate base price per unit for subtotal
            $basePricePerUnit = 0;
            $itemGstAmount = 0;
            
            if ($gstPercentage > 0) {
                if ($gstType) {
                    // GST inclusive: Use original variant price as base (no extraction)
                    // Don't recalculate tax - it's already in the price
                    $basePricePerUnit = $originalVariantPrice !== null ? $originalVariantPrice : $itemUnitPrice;
                    $itemGstAmount = 0; // Tax already included, don't add it again
                } else {
                    // GST exclusive: Extract base price from unit_price (unit_price = base + tax)
                    $basePricePerUnit = $itemUnitPrice / (1 + ($gstPercentage / 100));
                    $itemGstAmount = $basePricePerUnit * ($gstPercentage / 100);
                }
            } else {
                // No GST for this item
                $basePricePerUnit = $originalVariantPrice !== null ? $originalVariantPrice : $itemUnitPrice;
                $itemGstAmount = 0;
            }
            
            // Add to subtotal (base price * quantity)
            $subtotal += $basePricePerUnit * $quantity;
            
            // Add tax amount (only for exclusive items)
            $taxAmount += $itemGstAmount * $quantity;
        }
        
        $this->tax_amount = $taxAmount;
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
        
        // Calculate total (Subtotal - Discount + Tax to add + Shipping)
        // For inclusive items, tax is already in subtotal, so only add tax for exclusive items
        $this->total_amount = $subtotal - $discountAmount + $taxAmount + $shippingAmount;
        
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
