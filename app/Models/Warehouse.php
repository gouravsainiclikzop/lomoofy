<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'state',
        'country',
        'status',
        'is_default',
    ];

    protected $casts = [
        'status' => 'string',
        'is_default' => 'boolean',
    ];

    /**
     * Get all locations for this warehouse
     */
    public function locations()
    {
        return $this->hasMany(WarehouseLocation::class);
    }

    /**
     * Get active locations only
     */
    public function activeLocations()
    {
        return $this->hasMany(WarehouseLocation::class)->where('status', 'active');
    }

    /**
     * Scope to get active warehouses only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to order by name
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    /**
     * Scope to get default/primary warehouse
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get the default warehouse (primary warehouse)
     */
    public static function getDefault()
    {
        return static::where('is_default', true)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Get full address as a string
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->country,
        ]);
        return implode(', ', $parts);
    }
}
