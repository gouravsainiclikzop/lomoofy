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
        $this->load('items', 'coupon');
        
        // Calculate subtotal
        $subtotal = $this->items->sum('total_price');
        $this->subtotal = $subtotal;
        
        // Calculate discount from coupon if exists
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
        
        // Calculate tax (0% for now - can be configured later)
        $taxRate = 0;
        $taxableAmount = $subtotal - $discountAmount;
        $taxAmount = $taxableAmount * $taxRate;
        $this->tax_amount = $taxAmount;
        
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
        
        // Calculate total
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

}
