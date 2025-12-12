<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku', // Required at variant level
        'barcode', // Optional barcode at variant level
        'name',
        'attributes',
        
        // Pricing (variant-level only)
        'price', // Regular Price
        'sale_price', // Sale Price
        'cost_price',
        
        // Discount fields
        'discount_type',
        'discount_value',
        'discount_active',
        'sale_price_start',
        'sale_price_end',
        
        // Inventory (variant-level only)
        'manage_stock',
        'stock_quantity',
        'stock_status',
        'low_stock_threshold', // Variant-specific low stock threshold
        
        // Dimensions/Measurements (variant-level only)
        'weight',
        'length',
        'width',
        'height',
        'diameter',
        'measurements',
        'highlights_details', // Variant highlights & details
        
        // Variant Images
        'image', // Legacy single image field
        
        // Status
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'sale_price_start' => 'datetime',
        'sale_price_end' => 'datetime',
        'manage_stock' => 'boolean',
        'discount_active' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'low_stock_threshold' => 'integer',
        'measurements' => 'array',
        'highlights_details' => 'array',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_variant_id')->orderBy('sort_order');
    }

    /**
     * Get inventory stocks for this variant across all warehouses
     */
    public function inventoryStocks()
    {
        return $this->hasMany(InventoryStock::class);
    }

    /**
     * Get total stock quantity across all warehouses
     */
    public function getTotalStockQuantityAttribute()
    {
        if (!$this->manage_stock) {
            return null;
        }
        
        // If using warehouse-based inventory, sum from inventory_stocks
        $warehouseStock = $this->inventoryStocks()->sum('quantity');
        
        // Fallback to variant stock_quantity if no warehouse stocks exist
        if ($warehouseStock == 0 && $this->stock_quantity > 0) {
            return $this->stock_quantity;
        }
        
        return $warehouseStock;
    }

    /**
     * Get available stock (total - reserved) across all warehouses
     */
    public function getAvailableStockAttribute()
    {
        if (!$this->manage_stock) {
            return null;
        }
        
        $totalQuantity = $this->inventoryStocks()->sum('quantity');
        $reservedQuantity = $this->inventoryStocks()->sum('reserved_quantity');
        
        // Fallback to variant stock_quantity if no warehouse stocks exist
        if ($totalQuantity == 0 && $this->stock_quantity > 0) {
            return $this->stock_quantity;
        }
        
        return max(0, $totalQuantity - $reservedQuantity);
    }

    /**
     * Get stock for a specific warehouse
     */
    public function getStockForWarehouse($warehouseId, $locationId = null)
    {
        $query = $this->inventoryStocks()->where('warehouse_id', $warehouseId);
        
        if ($locationId) {
            $query->where('warehouse_location_id', $locationId);
        }
        
        return $query->first();
    }

    // Accessors
    public function getCurrentPriceAttribute()
    {
        if ($this->isOnSale()) {
            return $this->sale_price;
        }
        return $this->price ?? 0; // Price is always variant-level
    }

    public function getDiscountPercentageAttribute()
    {
        $basePrice = $this->price ?? 0;
        if ($this->isOnSale() && $basePrice > 0) {
            return round((($basePrice - $this->sale_price) / $basePrice) * 100);
        }
        return 0;
    }
    
    /**
     * Check if variant is low on stock
     */
    public function isLowStock()
    {
        if (!$this->manage_stock) {
            return false;
        }
        
        $threshold = $this->low_stock_threshold ?? 0;
        return $this->stock_quantity <= $threshold;
    }

    // Helper methods
    public function isOnSale()
    {
        if (!$this->sale_price) {
            return false;
        }

        $now = now();
        
        if ($this->sale_price_start && $this->sale_price_start > $now) {
            return false;
        }
        
        if ($this->sale_price_end && $this->sale_price_end < $now) {
            return false;
        }
        
        return true;
    }

    public function isInStock()
    {
        if (!$this->manage_stock) {
            return $this->stock_status === 'in_stock';
        }
        
        return $this->stock_quantity > 0;
    }

    public function getAttributeString()
    {
        $attributes = [];
        foreach ($this->attributes as $key => $value) {
            $attributes[] = ucfirst($key) . ': ' . $value;
        }
        return implode(', ', $attributes);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where(function($q) {
            $q->where('stock_status', 'in_stock')
              ->orWhere(function($q2) {
                  $q2->where('manage_stock', true)
                     ->where('stock_quantity', '>', 0);
              });
        });
    }
}