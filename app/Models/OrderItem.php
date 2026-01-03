<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'warehouse_id',
        'warehouse_location_id',
        'product_name',
        'product_sku',
        'variant_name',
        'variant_sku',
        'quantity',
        'unit_price',
        'total_price',
        'original_variant_price',
        'variant_sale_price',
        'discount_type',
        'discount_value',
        'discount_active',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'original_variant_price' => 'decimal:2',
        'variant_sale_price' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_active' => 'boolean',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function warehouseLocation()
    {
        return $this->belongsTo(WarehouseLocation::class);
    }
}
