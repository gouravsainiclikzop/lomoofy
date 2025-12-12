<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategoryAttributeValue extends Model
{
    protected $fillable = [
        'product_id',
        'category_attribute_id',
        'value',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function categoryAttribute()
    {
        return $this->belongsTo(CategoryAttribute::class);
    }

    // Helper methods
    public function getFormattedValue()
    {
        $attribute = $this->categoryAttribute;
        
        if (!$attribute) {
            return $this->value;
        }

        switch ($attribute->type) {
            case 'boolean':
                return (bool) $this->value;
            case 'number':
                return is_numeric($this->value) ? (float) $this->value : $this->value;
            case 'multiselect':
                return json_decode($this->value, true) ?? [];
            case 'date':
                return $this->value ? \Carbon\Carbon::parse($this->value) : null;
            default:
                return $this->value;
        }
    }

    public function setFormattedValue($value)
    {
        $attribute = $this->categoryAttribute;
        
        if (!$attribute) {
            $this->value = $value;
            return;
        }

        switch ($attribute->type) {
            case 'boolean':
                $this->value = $value ? '1' : '0';
                break;
            case 'number':
                $this->value = (string) $value;
                break;
            case 'multiselect':
                $this->value = is_array($value) ? json_encode($value) : $value;
                break;
            case 'date':
                $this->value = $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null;
                break;
            default:
                $this->value = (string) $value;
        }
    }
}