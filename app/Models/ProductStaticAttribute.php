<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStaticAttribute extends Model
{
    protected $fillable = [
        'product_id',
        'attribute_id',
        'value_id',
        'custom_value',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'attribute_id' => 'integer',
        'value_id' => 'integer',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute()
    {
        return $this->belongsTo(ProductAttribute::class, 'attribute_id');
    }

    public function value()
    {
        return $this->belongsTo(ProductAttributeValue::class, 'value_id');
    }

    // Helper methods
    public function getDisplayValue()
    {
        if ($this->value_id) {
            return $this->value->value ?? '';
        }
        
        return $this->custom_value ?? '';
    }
}
