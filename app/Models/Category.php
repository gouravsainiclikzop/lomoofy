<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'image',
        'is_active',
        'sort_order',
        'featured',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'featured' => 'boolean',
    ];

    // Auto-generate slug from name
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($category) {
            if (empty($category->slug) && !empty($category->name)) {
                $category->slug = static::generateUniqueSlug($category->name);
            }
        });
        
        static::updating(function ($category) {
            // Only regenerate slug if name changed and slug is empty
            if ($category->isDirty('name') && (empty($category->slug) || $category->slug === Str::slug($category->getOriginal('name')))) {
                $category->slug = static::generateUniqueSlug($category->name, $category->id);
            }
        });
    }

    /**
     * Generate a unique slug from the given name.
     * With the composite unique index (slug, deleted_at), we only need to check
     * if an active record (deleted_at = NULL) exists with this slug.
     * Soft-deleted records don't conflict due to the composite index.
     * 
     * @param string $name
     * @param int|null $excludeId Category ID to exclude from uniqueness check (for updates)
     * @return string
     */
    protected static function generateUniqueSlug($name, $excludeId = null)
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        // Only check active records (deleted_at = NULL) since soft-deleted records
        // don't conflict due to the composite unique index (slug, deleted_at)
        while (static::where('slug', $slug)
            ->when($excludeId, function ($query) use ($excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    // Relationship: Parent category
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Relationship: Child categories (supports unlimited nesting)
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    // Relationship: All descendants recursively
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    // Scope: Only parent categories
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    // Scope: Only active categories
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope: Featured categories
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    // Scope: Ordered by sort_order
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    // Relationship: Products in this category
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_categories')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    // Relationship: Custom attributes for this category (CategoryAttribute - legacy)
    public function attributes()
    {
        return $this->hasMany(CategoryAttribute::class)->ordered();
    }

    // Relationship: Product attributes assigned to this category (ProductAttribute)
    public function productAttributes()
    {
        return $this->belongsToMany(ProductAttribute::class, 'category_product_attribute')
            ->withTimestamps()
            ->orderBy('sort_order');
    }

    /**
     * Get all attributes for this category including inherited from ancestors.
     * Supports unlimited nesting depth.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllAttributes()
    {
        $categoryIds = $this->getAncestorIds();
        $categoryIds[] = $this->id; // Include current category
        
        return CategoryAttribute::whereIn('category_id', $categoryIds)
            ->ordered()
            ->get()
            ->unique('slug') // Remove duplicates if same attribute exists in multiple levels
            ->values();
    }

    /**
     * Get all ProductAttributes assigned to this category including inherited from ancestors.
     * Supports unlimited nesting depth.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllProductAttributes()
    {
        $categoryIds = $this->getAncestorIds();
        $categoryIds[] = $this->id; // Include current category
        
        return ProductAttribute::whereHas('categories', function($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->with('values') // Eager load values to avoid N+1 queries
            ->ordered()
            ->get()
            ->unique('id') // Remove duplicates if same attribute exists in multiple levels
            ->values();
    }

    /**
     * Get variant attributes (can_be_used_for_variants = true) for this category.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVariantAttributes()
    {
        return $this->getAllProductAttributes()->filter(function($attr) {
            return $attr->is_variation === true;
        })->values();
    }

    /**
     * Get static attributes (can_be_used_for_variants = false) for this category.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStaticAttributes()
    {
        return $this->getAllProductAttributes()->filter(function($attr) {
            return $attr->is_variation === false;
        })->values();
    }

    /**
     * Get all ancestor category IDs (parent, grandparent, etc.)
     * Supports unlimited nesting depth.
     * 
     * @return array
     */
    public function getAncestorIds()
    {
        $ancestorIds = [];
        $current = $this->parent;
        $maxDepth = 50; // Prevent infinite loops
        $depth = 0;
        
        while ($current && $depth < $maxDepth) {
            $ancestorIds[] = $current->id;
            $current = $current->parent;
            $depth++;
        }
        
        return $ancestorIds;
    }

    /**
     * Get all ancestor categories (parent, grandparent, etc.)
     * Supports unlimited nesting depth.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAncestors()
    {
        $ancestors = collect();
        $current = $this->parent;
        $maxDepth = 50; // Prevent infinite loops
        $depth = 0;
        
        while ($current && $depth < $maxDepth) {
            $ancestors->push($current);
            $current = $current->parent;
            $depth++;
        }
        
        return $ancestors;
    }

    /**
     * Get all descendant category IDs (children, grandchildren, etc.)
     * Supports unlimited nesting depth.
     * 
     * @return array
     */
    public function getDescendantIds()
    {
        $descendantIds = [];
        $this->collectDescendantIds($this->id, $descendantIds);
        return $descendantIds;
    }

    /**
     * Recursively collect descendant category IDs.
     * 
     * @param int $categoryId
     * @param array &$ids
     * @param int $maxDepth
     * @param int $currentDepth
     */
    protected function collectDescendantIds($categoryId, array &$ids, $maxDepth = 50, $currentDepth = 0)
    {
        if ($currentDepth >= $maxDepth) {
            return; // Prevent infinite loops
        }
        
        $children = static::where('parent_id', $categoryId)->pluck('id')->toArray();
        
        foreach ($children as $childId) {
            $ids[] = $childId;
            $this->collectDescendantIds($childId, $ids, $maxDepth, $currentDepth + 1);
        }
    }

    /**
     * Get all descendant categories (children, grandchildren, etc.)
     * Supports unlimited nesting depth.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDescendants()
    {
        $descendantIds = $this->getDescendantIds();
        
        if (empty($descendantIds)) {
            return collect();
        }
        
        return static::whereIn('id', $descendantIds)->get();
    }

    /**
     * Get products in this category and all its descendants.
     * Supports unlimited nesting depth.
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function productsWithDescendants()
    {
        $categoryIds = $this->getDescendantIds();
        $categoryIds[] = $this->id; // Include current category
        
        return Product::whereIn('category_id', $categoryIds);
    }

    /**
     * Get the depth/level of this category in the hierarchy.
     * Root categories (no parent) are at level 0.
     * 
     * @return int
     */
    public function getDepth()
    {
        $depth = 0;
        $current = $this->parent;
        $maxDepth = 50; // Prevent infinite loops
        
        while ($current && $depth < $maxDepth) {
            $depth++;
            $current = $current->parent;
        }
        
        return $depth;
    }

    /**
     * Check if this category can have children (not at maximum depth).
     * Maximum depth is 4 levels (0, 1, 2, 3, 4).
     * 
     * @return bool
     */
    public function canHaveChildren()
    {
        return $this->getDepth() < 3; 
    }
}
