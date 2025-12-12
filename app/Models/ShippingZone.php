<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
        'pincodes',
        'states',
        'cities',
        'country',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'pincodes' => 'array',
        'states' => 'array',
        'cities' => 'array',
        'status' => 'string',
        'sort_order' => 'integer',
    ];

    /**
     * Get all rates for this zone
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
     * Scope to get active zones only
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
     * Find zone by pincode
     */
    public static function findByPincode($pincode)
    {
        return static::where('status', 'active')
            ->where('type', 'pincode')
            ->where(function($query) use ($pincode) {
                $query->whereJsonContains('pincodes', $pincode)
                      ->orWhereJsonContains('pincodes', (string)$pincode);
            })
            ->orderBy('sort_order')
            ->first();
    }

    /**
     * Find zone by state
     */
    public static function findByState($state)
    {
        return static::where('status', 'active')
            ->where('type', 'state')
            ->where(function($query) use ($state) {
                $query->whereJsonContains('states', $state)
                      ->orWhere('states', 'like', '%' . $state . '%');
            })
            ->orderBy('sort_order')
            ->first();
    }

    /**
     * Find zone by city
     */
    public static function findByCity($city)
    {
        return static::where('status', 'active')
            ->where('type', 'city')
            ->where(function($query) use ($city) {
                $query->whereJsonContains('cities', $city)
                      ->orWhere('cities', 'like', '%' . $city . '%');
            })
            ->orderBy('sort_order')
            ->first();
    }

    /**
     * Get zone coverage description
     */
    public function getCoverageDescriptionAttribute()
    {
        $parts = [];
        
        if ($this->pincodes && count($this->pincodes) > 0) {
            $count = count($this->pincodes);
            $parts[] = $count . ' pincode' . ($count > 1 ? 's' : '');
        }
        
        if ($this->states && count($this->states) > 0) {
            $parts[] = implode(', ', array_slice($this->states, 0, 3)) . 
                      (count($this->states) > 3 ? '...' : '');
        }
        
        if ($this->cities && count($this->cities) > 0) {
            $count = count($this->cities);
            $parts[] = $count . ' cit' . ($count > 1 ? 'ies' : 'y');
        }
        
        return $parts ? implode(', ', $parts) : 'All areas';
    }
}
