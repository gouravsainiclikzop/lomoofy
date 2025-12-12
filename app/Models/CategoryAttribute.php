<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CategoryAttribute extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'type',
        'options',
        'is_required',
        'is_filterable',
        'is_searchable',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_filterable' => 'boolean',
        'is_searchable' => 'boolean',
        'sort_order' => 'integer',
    ];

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
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function values()
    {
        return $this->hasMany(ProductCategoryAttributeValue::class);
    }

    // Scopes
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }

    public function scopeSearchable($query)
    {
        return $query->where('is_searchable', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    // Helper methods
    public function isSelect()
    {
        return in_array($this->type, ['select', 'multiselect']);
    }

    public function isMultiselect()
    {
        return $this->type === 'multiselect';
    }

    public function isFile()
    {
        return $this->type === 'file';
    }

    public function isBoolean()
    {
        return $this->type === 'boolean';
    }

    public function isDate()
    {
        return $this->type === 'date';
    }

    public function isNumber()
    {
        return $this->type === 'number';
    }

    public function isText()
    {
        return $this->type === 'text';
    }

    public function getOptionsArray()
    {
        return $this->options ?? [];
    }

    public function getFormattedOptions()
    {
        if (!$this->isSelect()) {
            return [];
        }

        $options = $this->getOptionsArray();
        $formatted = [];

        foreach ($options as $option) {
            if (is_array($option)) {
                $formatted[$option['value']] = $option['label'];
            } else {
                $formatted[$option] = $option;
            }
        }

        return $formatted;
    }
}