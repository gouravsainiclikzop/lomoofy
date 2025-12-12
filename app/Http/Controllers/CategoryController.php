<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ProductAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    /**
     * Display categories page.
     */
    public function index(Request $request)
    {
        // Get statistics for the dashboard
        $stats = [
            'total' => Category::count(),
            'active' => Category::where('is_active', true)->count(),
            'inactive' => Category::where('is_active', false)->orWhereNull('is_active')->count(),
            'root_categories' => Category::whereNull('parent_id')->count(),
            'total_products' => \App\Models\Product::whereNotNull('category_id')->count(),
        ];
        
        return view('admin.categories.index', compact('stats'));
    }

    /**
     * Get all categories data (AJAX JSON).
     */
    public function getData(Request $request)
    {
        // Check if hierarchical view is requested
        if ($request->has('hierarchy') && $request->hierarchy == '1') {
            return $this->getHierarchicalData($request);
        }
        
        $query = Category::with(['parent'])->withCount('products');
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }
        
        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status);
        }
        
        // Order by sort_order, then by name (supports unlimited nesting)
        $categories = $query->with('productAttributes')->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        $data = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'parent_name' => $category->parent ? $category->parent->name : null,
                'parent_id' => $category->parent_id,
                'is_active' => $category->is_active,
                'sort_order' => $category->sort_order,
                'description' => $category->description,
                'image' => $category->image,
                'products_count' => $category->products_count ?? 0,
                'created_at' => $category->created_at,
                'product_attributes' => $category->productAttributes->map(function($attr) {
                    return [
                        'id' => $attr->id,
                        'name' => $attr->name,
                        'is_variation' => $attr->is_variation ?? false,
                    ];
                }),
            ];
        });

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                    'per_page' => $categories->perPage(),
                    'total' => $categories->total(),
                    'has_more_pages' => $categories->hasMorePages(),
                    'links' => $categories->links()->render()
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get categories in hierarchical structure (tree view).
     */
    private function getHierarchicalData(Request $request)
    {
        // Load all categories with children recursively
        $query = Category::with(['parent', 'productAttributes'])->withCount('products');
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }
        
        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status);
        }
        
        // Get all categories (no pagination for tree view)
        $allCategories = $query->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Build hierarchical structure
        $hierarchicalData = $this->buildHierarchy($allCategories);

        return response()->json([
            'success' => true,
            'data' => $hierarchicalData,
            'pagination' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => $allCategories->count(),
                'total' => $allCategories->count(),
                'has_more_pages' => false,
                'links' => ''
            ]
        ]);
    }

    /**
     * Build hierarchical tree structure from flat category list.
     */
    private function buildHierarchy($categories, $parentId = null, $level = 0)
    {
        $result = [];
        
        foreach ($categories as $category) {
            if ($category->parent_id == $parentId) {
                // Check if this category has children
                $children = $categories->filter(function($cat) use ($category) {
                    return $cat->parent_id == $category->id;
                });
                
                $categoryData = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'parent_name' => $category->parent ? $category->parent->name : null,
                    'parent_id' => $category->parent_id,
                    'is_active' => $category->is_active,
                    'sort_order' => $category->sort_order,
                    'description' => $category->description,
                    'image' => $category->image,
                    'products_count' => $category->products_count ?? 0,
                    'created_at' => $category->created_at,
                    'level' => $level,
                    'has_children' => $children->count() > 0,
                    'product_attributes' => $category->productAttributes->map(function($attr) {
                        return [
                            'id' => $attr->id,
                            'name' => $attr->name,
                            'is_variation' => $attr->is_variation ?? false,
                        ];
                    }),
                    'children' => $this->buildHierarchy($categories, $category->id, $level + 1)
                ];
                $result[] = $categoryData;
            }
        }
        
        return $result;
    }

    /**
     * Get parent categories for dropdown (AJAX JSON).
     * Returns categories that can be used as parents (maximum 4 levels deep).
     * 
     * - Excludes the current category being edited (if editing) to prevent circular references
     * - Returns only categories at level 0, 1, 2, or 3 (so new category would be at level 1, 2, 3, or 4)
     * - Maximum hierarchy depth is 4 levels
     * - Uses caching to improve performance
     */
    public function getParents(Request $request)
    {
        $excludeId = $request->get('exclude_id');
        $currentParentId = $request->get('current_parent_id');
        
        // Use a single cache key for all categories (without exclude)
        // Then filter in memory - this reduces cache complexity
        $cacheKey = 'category_parents_all';
        
        // Try to get from cache first (cache for 5 minutes)
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            // Filter out excluded category if needed
            if ($excludeId) {
                $cached = $cached->filter(function($item) use ($excludeId) {
                    return $item['id'] != $excludeId;
                })->values();
            }
            
            return response()->json([
                'success' => true,
                'data' => $cached
            ]);
        }
        
        // Get categories that can be parents (only level 0, 1, or 2 - max depth is 3)
        // Exclude the current category being edited to prevent circular references
        // Only get active (non-soft-deleted) categories for parent selection
        $query = Category::with('parent');
        
        // Exclude current category if editing (to prevent circular references)
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        // Get all active categories first
        $allCategories = $query->orderBy('name')->get();
        
        // Filter to only include categories at level 0, 1, 2, or 3 (max depth is 4)
        $parents = $allCategories->filter(function($category) {
            return $category->getDepth() < 4; // Only level 0, 1, 2, or 3 can be parents
        })->values();
        
        // Log for debugging (remove in production if needed)
        Log::info('Parent categories query result', [
            'count' => $parents->count(),
            'exclude_id' => $excludeId
        ]);
        
        // Get all children counts in one query to avoid N+1
        $parentIds = $parents->pluck('id')->toArray();
        $childrenCounts = Category::whereIn('parent_id', $parentIds)
            ->selectRaw('parent_id, COUNT(*) as count')
            ->groupBy('parent_id')
            ->pluck('count', 'parent_id')
            ->toArray();

        // Build hierarchical display names and add flag for children
        // Pre-load all categories in a map for efficient parent lookup
        $categoriesById = $parents->keyBy('id');
        
        $parents = $parents->map(function($parent) use ($categoriesById, $childrenCounts) {
            $hasChildren = isset($childrenCounts[$parent->id]) && $childrenCounts[$parent->id] > 0;
            $categoryDepth = $parent->getDepth();
            $canHaveChildren = $categoryDepth < 3; // Only level 0, 1, 2, or 3 can have children
            
            // Build breadcrumb path for display using the map
            $path = [];
            $current = $parent;
            $maxDepth = 10; // Prevent infinite loops
            $depth = 0;
            
            while ($current && $depth < $maxDepth) {
                array_unshift($path, $current->name);
                if ($current->parent_id && isset($categoriesById[$current->parent_id])) {
                    $current = $categoriesById[$current->parent_id];
                } else {
                    $current = $current->parent; // Fallback to eager loaded relation
                }
                $depth++;
            }
            
            $displayName = implode(' > ', $path);
            
            // Add warning if category is at maximum depth
            $suffix = '';
            if ($hasChildren) {
                $suffix .= ' (has children)';
            }
            if (!$canHaveChildren) {
                $suffix .= ' [Max depth reached]';
            }
            
            return [
                'id' => $parent->id,
                'name' => $displayName . $suffix,
                'has_children' => $hasChildren,
                'depth' => $categoryDepth,
                'can_have_children' => $canHaveChildren,
            ];
        });

        // Cache the result for 5 minutes
        Cache::put($cacheKey, $parents, now()->addMinutes(5));

        return response()->json([
            'success' => true,
            'data' => $parents
        ]);
    }

    /**
     * Store new category (POST AJAX JSON).
     * Checks if a soft-deleted category with the same slug exists and restores it instead of creating a new one.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $parentCategory = Category::find($value);
                        if ($parentCategory) {
                            $parentDepth = $parentCategory->getDepth();
                            // Maximum depth is 4 levels (0, 1, 2, 3, 4)
                            // Parent must be at level 0, 1, 2, or 3 (so new category would be at level 1, 2, 3, or 4)
                            if ($parentDepth >= 3) {
                                $fail('Cannot create category: Maximum hierarchy depth is 4 levels. The selected parent category is already at the maximum depth.');
                            }
                        }
                    }
                },
            ],
            'description' => 'nullable|string',
            'is_active' => 'nullable|in:0,1,true,false',
            'sort_order' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Generate slug from name
        $slug = $request->slug ?: Str::slug($request->name);
        
        // Check if a soft-deleted category with this slug exists
        $softDeletedCategory = Category::onlyTrashed()
            ->where('slug', $slug)
            ->first();

        $isRestored = false;
        if ($softDeletedCategory) {
            // Restore the soft-deleted category and update it
            $softDeletedCategory->restore();
            
            // Handle image upload
            $imagePath = $softDeletedCategory->image; // Keep existing image by default
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($softDeletedCategory->image && Storage::disk('public')->exists($softDeletedCategory->image)) {
                    Storage::disk('public')->delete($softDeletedCategory->image);
                }
                // Store new image
                $imagePath = $request->file('image')->store('categories', 'public');
            }

            // Update the restored category with new data
            $softDeletedCategory->update([
                'name' => $request->name,
                'description' => $request->description,
                'parent_id' => $request->parent_id,
                'image' => $imagePath,
                'is_active' => $request->is_active ?? true,
                'sort_order' => $request->sort_order ?? 0,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'meta_keywords' => $request->meta_keywords,
            ]);

            $category = $softDeletedCategory;
            $isRestored = true;
        } else {
            // Create new category
            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('categories', 'public');
            }

            // Let the model handle slug generation automatically (handles soft-deleted records)
            $category = Category::create([
                'name' => $request->name,
                'slug' => $request->slug ?: null, // Let model generate if empty
                'description' => $request->description,
                'parent_id' => $request->parent_id,
                'image' => $imagePath,
                'is_active' => $request->is_active ?? true,
                'sort_order' => $request->sort_order ?? 0,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'meta_keywords' => $request->meta_keywords,
            ]);
        }

        // Sync product attributes if provided
        if ($request->has('product_attribute_ids')) {
            $attributeIds = [];
            if (is_array($request->product_attribute_ids)) {
                $attributeIds = $request->product_attribute_ids;
            } elseif (is_string($request->product_attribute_ids)) {
                $decoded = json_decode($request->product_attribute_ids, true);
                $attributeIds = is_array($decoded) ? $decoded : [];
            }
            $category->productAttributes()->sync($attributeIds);
        }

        // Clear parent categories cache since a new category was added or restored
        $this->clearParentCategoriesCache();

        $message = $isRestored 
            ? 'Category restored successfully' 
            : 'Category created successfully';

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $category->load('productAttributes'),
            'restored' => $isRestored
        ]);
    }

    /**
     * Get single category for edit (AJAX JSON).
     */
public function edit(Request $request)
    {
        Log::info('=== CATEGORY EDIT REQUEST ===');
        Log::info('Request ID: ' . $request->id);
        
        $category = Category::find($request->id);

        if (!$category) {
            Log::error('Category not found for ID: ' . $request->id);
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $category->load('productAttributes');
        $categoryData = $category->toArray();
        
        // Get inherited attributes from parent categories
        $inheritedAttributes = collect();
        if ($category->parent) {
            $inheritedAttributes = $category->parent->getAllProductAttributes();
        }

        $categoryData['product_attribute_ids'] = $category->productAttributes->pluck('id')->toArray();
        $categoryData['inherited_product_attributes'] = $inheritedAttributes->toArray();

        Log::info('Category found: ' . json_encode($categoryData));

        return response()->json([
            'success' => true,
            'data' => $categoryData
        ]);
    }

    /**
     * Update category (POST AJAX JSON).
     */
    public function update(Request $request)
    {
        $category = Category::find($request->id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                function ($attribute, $value, $fail) use ($category) {
                    if ($value) {
                        // Can't set parent to itself
                        if ($value == $category->id) {
                            $fail('A category cannot be its own parent.');
                        }
                        
                        // Prevent circular references - check if the selected parent is a descendant
                        $parentCategory = Category::find($value);
                        if ($parentCategory) {
                            $descendants = $category->descendants()->pluck('id')->toArray();
                            if (in_array($value, $descendants)) {
                                $fail('A category cannot be a parent of its own descendant.');
                            }
                            
                            // Check maximum depth (4 levels)
                            $parentDepth = $parentCategory->getDepth();
                            // Maximum depth is 4 levels (0, 1, 2, 3, 4)
                            // Parent must be at level 0, 1, 2, or 3 (so updated category would be at level 1, 2, 3, or 4)
                            if ($parentDepth >= 3) {
                                $fail('Cannot update category: Maximum hierarchy depth is 4 levels. The selected parent category is already at the maximum depth.');
                            }
                        }
                    }
                },
            ],
            'description' => 'nullable|string',
            'is_active' => 'nullable|in:0,1,true,false',
            'sort_order' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Handle image upload
        $imagePath = $category->image; // Keep existing image by default
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            // Store new image
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        // Let the model handle slug generation automatically if name changed (handles soft-deleted records)
        $updateData = [
            'name' => $request->name,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
            'image' => $imagePath,
            'is_active' => $request->is_active ?? true,
            'sort_order' => $request->sort_order ?? 0,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
        ];
        
        // Only set slug if explicitly provided, otherwise let model handle it based on name change
        if ($request->has('slug') && !empty($request->slug)) {
            $updateData['slug'] = $request->slug;
        }
        // If slug not provided and name changed, model will auto-generate unique slug
        
        $category->update($updateData);

        // Sync product attributes if provided
        if ($request->has('product_attribute_ids')) {
            $attributeIds = [];
            if (is_array($request->product_attribute_ids)) {
                $attributeIds = $request->product_attribute_ids;
            } elseif (is_string($request->product_attribute_ids)) {
                $decoded = json_decode($request->product_attribute_ids, true);
                $attributeIds = is_array($decoded) ? $decoded : [];
            }
            $category->productAttributes()->sync($attributeIds);
        }

        // Clear parent categories cache since category was updated
        $this->clearParentCategoriesCache();

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category->load('productAttributes')
        ]);
    }
    
    /**
     * Clear parent categories cache
     * Called when categories are created, updated, deleted, or restored
     */
    private function clearParentCategoriesCache()
    {
        // Clear the base cache (without exclude_id)
        // Since we use a single cache key and filter in memory, 
        // we only need to clear this one key
        Cache::forget('category_parents_all');
    }

    /**
     * Delete category (POST AJAX JSON).
     */
    public function delete(Request $request)
    {
        $category = Category::find($request->id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        // Check if category has children
        if ($category->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with subcategories'
            ], 422);
        }

        $category->delete();

        // Clear parent categories cache since a category was deleted
        $this->clearParentCategoriesCache();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }

    /**
     * Restore soft-deleted category (POST AJAX JSON).
     */
    public function restore(Request $request)
    {
        $category = Category::onlyTrashed()->find($request->id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found in trash'
            ], 404);
        }

        // Check if an active category with the same slug exists
        $activeCategory = Category::where('slug', $category->slug)->first();
        if ($activeCategory) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot restore: An active category with the same slug already exists'
            ], 422);
        }

        $category->restore();

        // Clear parent categories cache since a category was restored
        $this->clearParentCategoriesCache();

        return response()->json([
            'success' => true,
            'message' => 'Category restored successfully',
            'data' => $category->load('productAttributes')
        ]);
    }

    /**
     * Bulk delete categories (POST AJAX JSON).
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:categories,id'
        ]);

        $ids = $request->ids;
        $deleted = 0;
        $failed = 0;
        $errors = [];

        foreach ($ids as $id) {
            $category = Category::find($id);
            if (!$category) {
                $failed++;
                continue;
            }

            // Check if category has children
            if ($category->children()->count() > 0) {
                $failed++;
                $errors[] = "Category '{$category->name}' cannot be deleted because it has subcategories";
                continue;
            }

            $category->delete();
            $deleted++;
        }

        $message = "Deleted {$deleted} category(ies)";
        if ($failed > 0) {
            $message .= ". {$failed} category(ies) could not be deleted.";
        }

        return response()->json([
            'success' => $deleted > 0,
            'message' => $message,
            'deleted' => $deleted,
            'failed' => $failed,
            'errors' => $errors
        ]);
    }

    /**
     * Update status for a category (POST AJAX JSON).
     */
    public function updateStatus(Request $request)
    {
        $category = Category::find($request->id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $request->validate([
            'is_active' => 'required|in:0,1,true,false',
        ]);

        $category->update([
            'is_active' => $request->is_active == 1 || $request->is_active === 'true' || $request->is_active === true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => [
                'is_active' => $category->is_active
            ]
        ]);
    }

    /**
     * Update parent category (POST AJAX JSON).
     */
    public function updateParent(Request $request)
    {
        $category = Category::find($request->id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $request->validate([
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                function ($attribute, $value, $fail) use ($category) {
                    if ($value) {
                        // Can't set parent to itself
                        if ($value == $category->id) {
                            $fail('A category cannot be its own parent.');
                        }
                        
                        // Prevent circular references - check if the selected parent is a descendant
                        $parentCategory = Category::find($value);
                        if ($parentCategory) {
                            $descendants = $category->descendants()->pluck('id')->toArray();
                            if (in_array($value, $descendants)) {
                                $fail('A category cannot be a parent of its own descendant.');
                            }
                            
                            // Check maximum depth (4 levels)
                            $parentDepth = $parentCategory->getDepth();
                            // Maximum depth is 4 levels (0, 1, 2, 3, 4)
                            // Parent must be at level 0, 1, 2, or 3 (so updated category would be at level 1, 2, 3, or 4)
                            if ($parentDepth >= 3) {
                                $fail('Cannot update parent: Maximum hierarchy depth is 4 levels. The selected parent category is already at the maximum depth.');
                            }
                        }
                    }
                },
            ],
        ]);

        $category->update([
            'parent_id' => $request->parent_id ?: null,
        ]);

        // Reload category with parent
        $category->load('parent');

        return response()->json([
            'success' => true,
            'message' => 'Parent category updated successfully',
            'data' => [
                'parent_id' => $category->parent_id,
                'parent_name' => $category->parent ? $category->parent->name : null
            ]
        ]);
    }

    /**
     * Get all available ProductAttributes for assignment (AJAX JSON).
     */
    public function getAvailableAttributes(Request $request)
    {
        $attributes = ProductAttribute::ordered()->get();
        
        return response()->json([
            'success' => true,
            'data' => $attributes
        ]);
    }
}
