<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_variant_id',
        'warehouse_id',
        'warehouse_location_id',
        'quantity',
        'reserved_quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
    ];

    /**
     * Boot the model and register event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Update ProductVariant stock_status when inventory stock is saved
        static::saved(function ($inventoryStock) {
            $variant = $inventoryStock->productVariant;
            if ($variant && $variant->manage_stock) {
                // Calculate total stock from all warehouses
                $totalStock = $variant->inventoryStocks()->sum('quantity');
                
                // Update variant stock_quantity and stock_status
                $variant->stock_quantity = $totalStock;
                $variant->stock_status = $totalStock > 0 ? 'in_stock' : 'out_of_stock';
                
                // Save without triggering events to avoid infinite loop
                $variant->saveQuietly();
            }
        });

        // Update ProductVariant stock_status when inventory stock is deleted
        static::deleted(function ($inventoryStock) {
            $variant = $inventoryStock->productVariant;
            if ($variant && $variant->manage_stock) {
                // Calculate total stock from remaining warehouses
                $totalStock = $variant->inventoryStocks()->sum('quantity');
                
                // Update variant stock_quantity and stock_status
                $variant->stock_quantity = $totalStock;
                $variant->stock_status = $totalStock > 0 ? 'in_stock' : 'out_of_stock';
                
                // Save without triggering events to avoid infinite loop
                $variant->saveQuietly();
            }
        });
    }

    /**
     * Get the product variant
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Get the warehouse
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the warehouse location
     */
    public function warehouseLocation()
    {
        return $this->belongsTo(WarehouseLocation::class);
    }

    /**
     * Get available quantity (quantity - reserved)
     */
    public function getAvailableQuantityAttribute()
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    /**
     * Scope to get stocks for a specific warehouse
     */
    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope to get stocks for a specific location
     */
    public function scopeForLocation($query, $locationId)
    {
        return $query->where('warehouse_location_id', $locationId);
    }
}
