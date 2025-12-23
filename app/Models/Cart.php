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
     * Recalculate cart totals
     */
    public function recalculateTotals()
    {
        $this->load('items', 'coupon');
        
        $subtotal = $this->items->sum('total_price');
        $this->subtotal = $subtotal;
        
        // Calculate discount from coupon if exists
        $discountAmount = 0;
        if ($this->coupon_code && $this->coupon) {
            // Reload coupon to ensure we have latest data
            $this->load('coupon');
            $coupon = $this->coupon;
            
            if ($coupon && $coupon->isActive() && $coupon->canBeUsed()) {
                // Check minimum order amount
                if (!$coupon->min_order_amount || $subtotal >= $coupon->min_order_amount) {
                    // Calculate discount using coupon's method
                    $discountAmount = $coupon->calculateDiscount($subtotal);
                }
            }
        }
        $this->discount_amount = $discountAmount;
        
        // Calculate tax (example: 10% tax)
        $taxRate = 0.10; // 10%
        $this->tax_amount = ($subtotal - $discountAmount) * $taxRate;
        
        // Shipping (can be calculated based on rules)
        $this->shipping_amount = 0; // TODO: Calculate shipping
        
        // Total
        $this->total_amount = $subtotal - $discountAmount + $this->tax_amount + $this->shipping_amount;
        
        $this->save();
    }
}
