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
 
    protected static function generateUniqueSlug($name, $excludeId = null)
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;
 
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
 
    public function getVariantAttributes()
    {
        return $this->getAllProductAttributes()->filter(function($attr) {
            return $attr->is_variation === true;
        })->values();
    }
 
    public function getStaticAttributes()
    {
        return $this->getAllProductAttributes()->filter(function($attr) {
            return $attr->is_variation === false;
        })->values();
    }
 
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
 
    public function getDescendantIds()
    {
        $descendantIds = [];
        $this->collectDescendantIds($this->id, $descendantIds);
        return $descendantIds;
    }
 
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
 
    public function getDescendants()
    {
        $descendantIds = $this->getDescendantIds();
        
        if (empty($descendantIds)) {
            return collect();
        }
        
        return static::whereIn('id', $descendantIds)->get();
    }
 
    public function productsWithDescendants()
    {
        $categoryIds = $this->getDescendantIds();
        $categoryIds[] = $this->id; // Include current category
        
        return Product::whereIn('category_id', $categoryIds);
    }
 
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
 
    public function canHaveChildren()
    {
        return $this->getDepth() < 3; 
    }
 
    public function getFullPathName()
    {
        $path = [];
        $current = $this;
        $maxDepth = 50; // Prevent infinite loops
        $depth = 0;
        
        // Build path from current category up to root
        while ($current && $depth < $maxDepth) {
            array_unshift($path, $current->name);
            $current = $current->parent;
            $depth++;
        }
        
        return implode(' > ', $path);
    }
 
    public function getRootCategory()
    {
        $current = $this;
        $maxDepth = 50; // Prevent infinite loops
        $depth = 0;
        
        while ($current && $current->parent_id && $depth < $maxDepth) {
            $current = $current->parent;
            $depth++;
        }
        
        return $current;
    }
}
