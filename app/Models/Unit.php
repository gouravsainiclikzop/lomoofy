<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'symbol',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope to get active units only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get units by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    /**
     * Get products using this unit
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get units of the same type
     */
    public function sameTypeUnits()
    {
        return $this->where('type', $this->type)->where('id', '!=', $this->id);
    }

    /**
     * Get display name with symbol
     */
    public function getDisplayNameAttribute()
    {
        return $this->name . ' (' . $this->symbol . ')';
    }
}