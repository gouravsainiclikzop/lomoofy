<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_zone_id',
        'shipping_method_id',
        'rate_type',
        'rate',
        'rate_per_kg',
        'rate_percentage',
        'min_value',
        'max_value',
        'fragile_surcharge',
        'oversized_surcharge',
        'hazardous_surcharge',
        'express_surcharge',
        'free_shipping_threshold',
        'status',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'rate_per_kg' => 'decimal:2',
        'rate_percentage' => 'decimal:2',
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
        'fragile_surcharge' => 'decimal:2',
        'oversized_surcharge' => 'decimal:2',
        'hazardous_surcharge' => 'decimal:2',
        'express_surcharge' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
        'status' => 'string',
    ];

    /**
     * Get the zone for this rate
     */
    public function zone()
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    /**
     * Get the method for this rate
     */
    public function method()
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }

    /**
     * Scope to get active rates only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Calculate shipping cost based on rate type
     */
    public function calculateCost($weight = 0, $orderTotal = 0, $shippingClass = 'standard')
    {
        // Check free shipping threshold
        if ($this->free_shipping_threshold && $orderTotal >= $this->free_shipping_threshold) {
            return 0;
        }

        $baseCost = 0;

        switch ($this->rate_type) {
            case 'flat_rate':
                $baseCost = $this->rate;
                break;

            case 'weight_based':
                $weightInKg = $weight / 1000; // Convert grams to kg
                $baseCost = $this->rate + ($weightInKg * ($this->rate_per_kg ?? 0));
                break;

            case 'price_based':
                $percentage = $this->rate_percentage ?? 0;
                $baseCost = ($orderTotal * $percentage) / 100;
                break;

            case 'distance_based':
                // For future implementation
                $baseCost = $this->rate;
                break;
        }

        // Add shipping class surcharges
        $surcharge = 0;
        switch ($shippingClass) {
            case 'fragile':
                $surcharge = $this->fragile_surcharge ?? 0;
                break;
            case 'oversized':
                $surcharge = $this->oversized_surcharge ?? 0;
                break;
            case 'hazardous':
                $surcharge = $this->hazardous_surcharge ?? 0;
                break;
            case 'express':
                $surcharge = $this->express_surcharge ?? 0;
                break;
        }

        return max(0, $baseCost + $surcharge);
    }

    /**
     * Check if rate applies to given weight/price
     */
    public function appliesTo($weight = 0, $orderTotal = 0)
    {
        if ($this->min_value !== null) {
            $value = $this->rate_type === 'weight_based' ? $weight : $orderTotal;
            if ($value < $this->min_value) {
                return false;
            }
        }

        if ($this->max_value !== null) {
            $value = $this->rate_type === 'weight_based' ? $weight : $orderTotal;
            if ($value > $this->max_value) {
                return false;
            }
        }

        return true;
    }
}
