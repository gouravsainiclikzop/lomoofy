<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductAttribute extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'is_required',
        'is_variation',
        'is_visible',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_variation' => 'boolean',
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Auto-generate slug from name
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($attribute) {
            if (empty($attribute->slug) && !empty($attribute->name)) {
                $attribute->slug = Str::slug($attribute->name);
            }
        });
        
        static::updating(function ($attribute) {
            if (empty($attribute->slug) && !empty($attribute->name)) {
                $attribute->slug = Str::slug($attribute->name);
            }
        });
    }

    // Relationships
    public function values()
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_id')->orderBy('sort_order');
    }

    // Relationship: Categories that have this attribute assigned
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product_attribute')
            ->withTimestamps();
    }

    // Scopes
    public function scopeVariation($query)
    {
        return $query->where('is_variation', true);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Helper methods
    public function isSelect()
    {
        return $this->type === 'select';
    }

    public function isColor()
    {
        return $this->type === 'color';
    }

    public function isImage()
    {
        return $this->type === 'image';
    }

    public function isText()
    {
        return $this->type === 'text';
    }
}