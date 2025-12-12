<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Coupon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_uses',
        'uses',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_uses' => 'integer',
        'uses' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => 'boolean',
    ];

    /**
     * Check if coupon is active
     */
    public function isActive(): bool
    {
        if (!$this->status) {
            return false;
        }

        if (!$this->start_date || !$this->end_date) {
            return false;
        }

        $now = Carbon::now();
        return $now->between($this->start_date, $this->end_date);
    }

    /**
     * Check if coupon can be used
     */
    public function canBeUsed(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        if ($this->max_uses && $this->uses >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount amount
     */
    public function calculateDiscount($orderAmount): float
    {
        if ($this->discount_type === 'percentage') {
            return ($orderAmount * $this->discount_value) / 100;
        }

        return min($this->discount_value, $orderAmount);
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('uses');
    }

    /**
     * Scope for active coupons
     */
    public function scopeActive($query)
    {
        return $query->where('status', true)
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now());
    }

    /**
     * Scope for valid coupons (active and not expired)
     */
    public function scopeValid($query)
    {
        return $query->where('status', true)
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->where(function($q) {
                $q->whereNull('max_uses')
                  ->orWhereColumn('uses', '<', 'max_uses');
            });
    }
}
