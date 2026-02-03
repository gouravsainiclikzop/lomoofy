<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [ 
        'name',
        'slug',
        'short_description',
        'status',
        'featured', 
        'brand_id', 
        'category_id',  
        'default_warehouse_id',  
        'tags', 
        // SEO Settings
        'meta_title',
        'meta_description',
        'meta_keywords',
        'metadata', 
        'json_ld',  
        'unit_id',
        'unit_quantity',
        'unit_display',
        'color',
        'size',
        'material',
        'origin_country',
        'manufacturing_date',
        'expiry_date',
        'is_perishable',
        'requires_prescription',
        'is_hazardous',
        'ingredients',
        'nutritional_info',
        'barcode_type',
        'custom_attributes',
        'requires_shipping',
        'free_shipping',
        'gst_type',
        'gst_percentage',
        'published_at',
        'download_limit',
        'download_expiry',
        'bundle_items',
        'subscription_period',
        'subscription_interval',
        'subscription_length', 
    ];

    protected $casts = [
        'requires_shipping' => 'boolean',
        'free_shipping' => 'boolean',
        'featured' => 'boolean',
        'gst_type' => 'boolean',
        'gst_percentage' => 'decimal:2',
        'bundle_items' => 'array',
        'published_at' => 'datetime',
        'json_ld' => 'array',
        'custom_attributes' => 'array',
    ];

    // Auto-generate slug from name
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($product) {
            if (empty($product->slug) && !empty($product->name)) {
                $product->slug = Str::slug($product->name);
            } 
        });
        
        static::updating(function ($product) {
            if (empty($product->slug) && !empty($product->name)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    // Relationships
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'product_brands')
                    ->withTimestamps();
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)
            ->whereNull('product_variant_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)
            ->where('is_primary', true)
            ->whereNull('product_variant_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    // Relationship with reviews
    public function reviews()
    {
        return $this->hasMany(Review::class, 'product_id');
    }

    // * Get only active reviews for this product
    public function activeReviews()
    {
        return $this->hasMany(Review::class, 'product_id')->where('status', 'active');
    }

    // Single category relationship (primary)
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Default warehouse relationship
    public function defaultWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'default_warehouse_id');
    }

    // Legacy many-to-many relationship (kept for backward compatibility during migration)
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }

    public function categoryAttributeValues()
    {
        return $this->hasMany(ProductCategoryAttributeValue::class);
    }

    // Static attributes (ProductAttribute values stored per product)
    public function staticAttributes()
    {
        return $this->hasMany(ProductStaticAttribute::class);
    }

    
    public function getApplicableProductAttributes()
    {
        if (!$this->category) {
            return collect();
        }

        return $this->category->getAllProductAttributes();
    }
 
    public function getVariantProductAttributes()
    {
        return $this->getApplicableProductAttributes()
            ->filter(function ($attribute) {
                return $attribute->is_variation === true;
            })
            ->values();
    }
 
    public function getStaticProductAttributes()
    {
        return $this->getApplicableProductAttributes()
            ->filter(function ($attribute) {
                return $attribute->is_variation === false;
            })
            ->values();
    }
 
    public function getApplicableCategoryAttributes()
    {
        if (!$this->category) {
            return collect();
        }

        return $this->category->getAllAttributes();
    }
 
    public function getFilterableCategoryAttributes()
    {
        return $this->getApplicableCategoryAttributes()
            ->filter(function ($attribute) {
                return $attribute->is_filterable;
            });
    }

    // Helper method to get category with full path
    public function getCategoryPathAttribute()
    {
        if (!$this->category) {
            return null;
        }

        $path = [];
        $current = $this->category;
        
        while ($current) {
            array_unshift($path, $current->name);
            $current = $current->parent;
        }
        
        return implode(' > ', $path);
    }

    // Legacy method for backward compatibility
    public function primaryCategory()
    {
        return $this->category();
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Accessors & Mutators
    // Note: Price accessors removed - pricing is variant-level only
    // Use $product->variants->min('price') or similar for product-level price display

    public function getImageUrlAttribute()
    {
        $primaryImage = $this->primaryImage;
        if ($primaryImage) {
            return asset('storage/' . $primaryImage->image_path);
        }
        
        $firstImage = $this->images()->first();
        if ($firstImage) {
            return asset('storage/' . $firstImage->image_path);
        }
        
        return asset('assets/images/placeholder.jpg');
    }

    
    public function hasStock()
    {
        return $this->variants()->where('is_active', true)
            ->where(function($query) {
                $query->where('stock_status', 'in_stock')
                    ->orWhere(function($q) {
                        $q->where('manage_stock', true)
                          ->where('stock_quantity', '>', 0);
                    });
            })
            ->exists();
    }
     
    public function getMinPrice()
    {
        return $this->variants()->where('is_active', true)->min('price') ?? 0;
    }
    
    
    public function getMaxPrice()
    {
        return $this->variants()->where('is_active', true)->max('price') ?? 0;
    }

    public function requiresShipping()
    {
        return $this->requires_shipping && !$this->isDigital() && !$this->isService();
    }

    // Get all possible attribute combinations for variants
    public function getAttributeCombinations()
    {
        $variants = $this->variants()->active()->get();
        $combinations = collect();

        foreach ($variants as $variant) {
            $combinations->push($variant->attributes ?? []);
        }

        return $combinations;
    }

 
    public function getAvailableAttributes()
    {
        $variants = $this->variants()->active()->get();
        $attributes = collect();

        foreach ($variants as $variant) {
            $variantAttributes = $variant->attributes ?? [];
            foreach ($variantAttributes as $key => $value) {
                if (!$attributes->has($key)) {
                    $attributes->put($key, collect());
                }
                $attributes->get($key)->push($value);
            }
        }

        return $attributes->map(function ($values) {
            return $values->unique()->values();
        });
    }
}