<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'unit_price',
        'total_price',
        'reserved_stock',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'reserved_stock' => 'integer',
    ];

    /**
     * Cart relationship
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Product relationship
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Product variant relationship
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Get the stock source (variant or product)
     */
    public function getStockSource()
    {
        if ($this->product_variant_id) {
            return $this->variant;
        }
        return $this->product;
    }

    /**
     * Get available stock for this item
     */
    public function getAvailableStock(): int
    {
        $source = $this->getStockSource();
        
        if (!$source || !$source->manage_stock) {
            return 999999; // Unlimited stock
        }
        
        return $source->stock_quantity ?? 0;
    }
}
