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
        // Basic Information (product-level only)
        'name',
        'slug',
        'description', // Detailed description
        'short_description',
        'status',
        'featured',
        
        // Categories & Tags
        'brand_id', // Single brand (legacy) - primary brand
        'category_id', // Single category (primary)
        'default_warehouse_id', // Default warehouse for this product
        'tags',
        
        // SEO Settings
        'meta_title',
        'meta_description',
        'meta_keywords',
        'metadata', // Raw metadata / JSON-LD (optional)
        'json_ld', // JSON-LD structured data (optional)
        
        // Optional fields (kept for backward compatibility, but not in unified structure)
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
        'published_at',
        'download_limit',
        'download_expiry',
        'bundle_items',
        'subscription_period',
        'subscription_interval',
        'subscription_length',
        
        // Note: Removed fields (variant-level only):
        // sku, barcode, price, sale_price, sale_price_start, sale_price_end,
        // manage_stock, stock_quantity, stock_status, allow_backorder,
        // weight, length, width, height, diameter, type, sku_type
    ];

    protected $casts = [
        'requires_shipping' => 'boolean',
        'free_shipping' => 'boolean',
        'featured' => 'boolean',
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
            // SKU is now variant-level only, removed from product creation
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

    // Multi-brand support
    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'product_brands')
                    ->withPivot('is_primary', 'sort_order')
                    ->withTimestamps()
                    ->orderBy('product_brands.sort_order');
    }

    public function primaryBrand()
    {
        return $this->belongsToMany(Brand::class, 'product_brands')
                    ->wherePivot('is_primary', true);
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

    /**
     * Get all ProductAttributes applicable to this product based on its category.
     * Includes attributes from the product's category and all ancestor categories.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getApplicableProductAttributes()
    {
        if (!$this->category) {
            return collect();
        }

        return $this->category->getAllProductAttributes();
    }

    /**
     * Get variant attributes (is_variation = true) for this product.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVariantProductAttributes()
    {
        return $this->getApplicableProductAttributes()
            ->filter(function ($attribute) {
                return $attribute->is_variation === true;
            })
            ->values();
    }

    /**
     * Get static attributes (is_variation = false) for this product.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStaticProductAttributes()
    {
        return $this->getApplicableProductAttributes()
            ->filter(function ($attribute) {
                return $attribute->is_variation === false;
            })
            ->values();
    }

    /**
     * Get all applicable category attributes for this product.
     * Includes attributes from the product's category and all ancestor categories.
     * Supports unlimited nesting depth.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getApplicableCategoryAttributes()
    {
        if (!$this->category) {
            return collect();
        }

        return $this->category->getAllAttributes();
    }

    /**
     * Get filterable category attributes for this product.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
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

    // Helper methods
    // Note: Inventory and pricing methods removed - these are variant-level only
    
    // Product type checks removed - unified structure doesn't use product types
    // All products support variants (even if just a default one)
    
    /**
     * Check if product has any active variants in stock
     */
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
    
    /**
     * Get minimum price from all active variants
     */
    public function getMinPrice()
    {
        return $this->variants()->where('is_active', true)->min('price') ?? 0;
    }
    
    /**
     * Get maximum price from all active variants
     */
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

    // Get available attributes for this product
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