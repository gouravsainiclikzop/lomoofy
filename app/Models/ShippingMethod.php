<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'estimated_days_min',
        'estimated_days_max',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'status' => 'string',
        'estimated_days_min' => 'integer',
        'estimated_days_max' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get all rates for this method
     */
    public function rates()
    {
        return $this->hasMany(ShippingRate::class);
    }

    /**
     * Get active rates only
     */
    public function activeRates()
    {
        return $this->hasMany(ShippingRate::class)->where('status', 'active');
    }

    /**
     * Scope to get active methods only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get estimated delivery time as string
     */
    public function getEstimatedDeliveryAttribute()
    {
        if ($this->estimated_days_min && $this->estimated_days_max) {
            if ($this->estimated_days_min === $this->estimated_days_max) {
                return $this->estimated_days_min . ' day' . ($this->estimated_days_min > 1 ? 's' : '');
            }
            return $this->estimated_days_min . '-' . $this->estimated_days_max . ' days';
        }
        
        if ($this->estimated_days_min) {
            return $this->estimated_days_min . '+ days';
        }
        
        return 'Standard delivery';
    }
}
