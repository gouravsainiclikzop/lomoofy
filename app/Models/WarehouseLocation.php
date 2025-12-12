<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'rack',
        'shelf',
        'bin',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get the warehouse that owns this location
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Scope to get active locations only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get location code (rack-shelf-bin)
     */
    public function getLocationCodeAttribute()
    {
        $parts = array_filter([$this->rack, $this->shelf, $this->bin]);
        return implode('-', $parts) ?: 'N/A';
    }

    /**
     * Get full location display
     */
    public function getFullLocationAttribute()
    {
        $parts = [];
        if ($this->rack) $parts[] = "Rack: {$this->rack}";
        if ($this->shelf) $parts[] = "Shelf: {$this->shelf}";
        if ($this->bin) $parts[] = "Bin: {$this->bin}";
        return implode(', ', $parts) ?: 'No location specified';
    }
}
