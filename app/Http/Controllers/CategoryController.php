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
        
        // Eager load parent relationships recursively to find root categories
        $query = Category::whereNull('deleted_at')->with(['parent.parent.parent.parent'])->withCount('products');
        
        // Search functionality - qualify columns with table name to avoid ambiguity
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('categories.name', 'like', '%' . $search . '%')
                  ->orWhere('categories.description', 'like', '%' . $search . '%')
                  ->orWhere('categories.slug', 'like', '%' . $search . '%');
            });
        }
        
        // Filter by status - qualify column with table name to avoid ambiguity
        if ($request->has('status') && $request->status !== '') {
            $query->where('categories.is_active', $request->status);
        }
        
        // Get all categories first to compute root parent sort_order
        // This allows us to order by the root/main parent category's sort_order
        $allCategories = $query->with('productAttributes')->get();
        
        // Add root parent sort_order to each category for sorting
        // Only use sort_order from root categories (categories with no parent_id)
        $categoriesWithRoot = $allCategories->map(function ($category) {
            $rootCategory = $category->getRootCategory();
            // Only use root category's sort_order (category with no parent_id)
            if ($rootCategory && !$rootCategory->parent_id) {
                $category->root_sort_order = $rootCategory->sort_order;
            } else {
                // If category itself is root (no parent_id), use its own sort_order
                $category->root_sort_order = $category->sort_order ?? 999999;
            }
            return $category;
        });
        
        // Sort by root category's sort_order only (categories with no parents), then by name
        $sortedCategories = $categoriesWithRoot->sortBy([
            ['root_sort_order', 'asc'],
            ['name', 'asc'],
        ])->values();
        
        // No pagination - return all categories
        $allCategoriesForHierarchy = $sortedCategories;
        
        // Add has_children flag to each category
        $allCategoriesForHierarchy = $allCategoriesForHierarchy->map(function ($category) use ($sortedCategories) {
            // Check if this category has children
            $children = $sortedCategories->filter(function($cat) use ($category) {
                return $cat->parent_id == $category->id;
            });
            
            $category->has_children = $children->count() > 0;
            $category->children_count = $children->count();
            
            return $category;
        });
        
        // Map ALL categories (not just paginated) for response
        // This ensures parent-child relationships are preserved in the frontend
        $allCategoriesData = $allCategoriesForHierarchy->map(function ($category) use ($allCategoriesForHierarchy) {
            // Find the category in the full list to get children info
            $fullCategory = $allCategoriesForHierarchy->firstWhere('id', $category->id);
            
            // Get children for this category
            $children = $allCategoriesForHierarchy->filter(function($cat) use ($category) {
                return $cat->parent_id == $category->id;
            })->map(function($child) {
                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'slug' => $child->slug,
                    'parent_name' => $child->parent ? $child->parent->name : null,
                    'parent_id' => $child->parent_id,
                    'is_active' => $child->is_active,
                    'sort_order' => $child->sort_order,
                    'description' => $child->description,
                    'image' => $child->image,
                    'products_count' => $child->products_count ?? 0,
                    'created_at' => $child->created_at,
                    'has_children' => $child->has_children ?? false,
                    'product_attributes' => $child->productAttributes->map(function($attr) {
                        return [
                            'id' => $attr->id,
                            'name' => $attr->name,
                            'is_variation' => $attr->is_variation ?? false,
                        ];
                    }),
                ];
            })->values();
            
            return [
                'id' => $category->id,
                'name' => $category->name,
                'full_path_name' => $category->getFullPathName(),
                'slug' => $category->slug,
                'parent_name' => $category->parent ? $category->parent->name : null,
                'parent_id' => $category->parent_id,
                'is_active' => $category->is_active,
                'sort_order' => $category->sort_order,
                'description' => $category->description,
                'image' => $category->image,
                'products_count' => $category->products_count ?? 0,
                'created_at' => $category->created_at,
                'has_children' => $fullCategory->has_children ?? false,
                'children' => $children->toArray(),
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
                'data' => $allCategoriesData,
                'total' => $allCategoriesData->count()
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $allCategoriesData,
            'total' => $allCategoriesData->count()
        ]);
    }

    /**
     * Get categories in hierarchical structure (tree view).
     */
    private function getHierarchicalData(Request $request)
    {
        // Load all categories with children recursively
        $query = Category::whereNull('deleted_at')->with(['parent', 'productAttributes'])->withCount('products');
        
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

    /**
     * Bulk update categories (POST AJAX JSON).
     * Updates multiple categories in a single request.
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:categories,id',
            'categories.*.name' => 'required|string|max:255',
            'categories.*.parent_id' => 'nullable|exists:categories,id',
            'categories.*.description' => 'nullable|string',
            'categories.*.is_active' => 'nullable|boolean',
            'categories.*.sort_order' => 'nullable|integer',
            'categories.*.product_attribute_ids' => 'nullable|array',
            'categories.*.image_base64' => 'nullable|string', // Base64 encoded image
        ]);

        $results = [
            'success' => [],
            'failed' => []
        ];

        foreach ($request->categories as $categoryData) {
            try {
                $category = Category::find($categoryData['id']);
                
                if (!$category) {
                    $results['failed'][] = [
                        'id' => $categoryData['id'],
                        'name' => $categoryData['name'] ?? 'Unknown',
                        'error' => 'Category not found'
                    ];
                    continue;
                }

                // Validate parent_id constraints
                if (isset($categoryData['parent_id']) && $categoryData['parent_id']) {
                    // Can't set parent to itself
                    if ($categoryData['parent_id'] == $category->id) {
                        $results['failed'][] = [
                            'id' => $category->id,
                            'name' => $categoryData['name'],
                            'error' => 'A category cannot be its own parent.'
                        ];
                        continue;
                    }
                    
                    // Prevent circular references
                    $parentCategory = Category::find($categoryData['parent_id']);
                    if ($parentCategory) {
                        $descendants = $category->descendants()->pluck('id')->toArray();
                        if (in_array($categoryData['parent_id'], $descendants)) {
                            $results['failed'][] = [
                                'id' => $category->id,
                                'name' => $categoryData['name'],
                                'error' => 'A category cannot be a parent of its own descendant.'
                            ];
                            continue;
                        }
                        
                        // Check maximum depth
                        $parentDepth = $parentCategory->getDepth();
                        if ($parentDepth >= 3) {
                            $results['failed'][] = [
                                'id' => $category->id,
                                'name' => $categoryData['name'],
                                'error' => 'Maximum hierarchy depth is 4 levels.'
                            ];
                            continue;
                        }
                    }
                }

                // Handle image upload if base64 provided
                $imagePath = $category->image; // Keep existing image by default
                if (isset($categoryData['image_base64']) && !empty($categoryData['image_base64'])) {
                    try {
                        // Decode base64 image
                        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $categoryData['image_base64']));
                        
                        if ($imageData) {
                            // Generate unique filename
                            $extension = 'jpg'; // Default, could be detected from base64 header
                            if (preg_match('#^data:image/(\w+);base64,#i', $categoryData['image_base64'], $matches)) {
                                $extension = $matches[1];
                            }
                            
                            $filename = uniqid() . '_' . time() . '.' . $extension;
                            $filePath = 'categories/' . $filename;
                            
                            // Delete old image if exists
                            if ($category->image && Storage::disk('public')->exists($category->image)) {
                                Storage::disk('public')->delete($category->image);
                            }
                            
                            // Store new image
                            Storage::disk('public')->put($filePath, $imageData);
                            $imagePath = $filePath;
                        }
                    } catch (\Exception $e) {
                        Log::error('Error processing base64 image for category ' . $category->id . ': ' . $e->getMessage());
                        // Continue with existing image if base64 decode fails
                    }
                }

                // Update category
                $updateData = [
                    'name' => $categoryData['name'],
                    'description' => $categoryData['description'] ?? null,
                    'parent_id' => $categoryData['parent_id'] ?? null,
                    'image' => $imagePath,
                    'is_active' => $categoryData['is_active'] ?? true,
                    'sort_order' => $categoryData['sort_order'] ?? 0,
                ];
                
                $category->update($updateData);

                // Sync product attributes if provided
                if (isset($categoryData['product_attribute_ids']) && is_array($categoryData['product_attribute_ids'])) {
                    $category->productAttributes()->sync($categoryData['product_attribute_ids']);
                }

                $results['success'][] = [
                    'id' => $category->id,
                    'name' => $category->name
                ];
            } catch (\Exception $e) {
                Log::error('Error updating category ' . ($categoryData['id'] ?? 'unknown') . ': ' . $e->getMessage());
                $results['failed'][] = [
                    'id' => $categoryData['id'] ?? null,
                    'name' => $categoryData['name'] ?? 'Unknown',
                    'error' => $e->getMessage()
                ];
            }
        }

        // Clear parent categories cache
        $this->clearParentCategoriesCache();

        return response()->json([
            'success' => count($results['failed']) === 0,
            'message' => count($results['success']) . ' category(ies) updated successfully' . 
                        (count($results['failed']) > 0 ? ', ' . count($results['failed']) . ' failed' : ''),
            'results' => $results
        ]);
    }

    /**
     * Bulk create categories (POST AJAX JSON).
     * Creates multiple categories in a single request.
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.name' => 'required|string|max:255',
            'categories.*.parent_id' => 'nullable|exists:categories,id',
            'categories.*.description' => 'nullable|string',
            'categories.*.is_active' => 'nullable|boolean',
            'categories.*.sort_order' => 'nullable|integer',
            'categories.*.product_attribute_ids' => 'nullable|array',
            'categories.*.image_base64' => 'nullable|string', // Base64 encoded image
        ]);

        $results = [
            'success' => [],
            'failed' => []
        ];

        foreach ($request->categories as $categoryData) {
            try {
                // Validate parent depth
                if (isset($categoryData['parent_id']) && $categoryData['parent_id']) {
                    $parentCategory = Category::find($categoryData['parent_id']);
                    if ($parentCategory) {
                        $parentDepth = $parentCategory->getDepth();
                        if ($parentDepth >= 3) {
                            $results['failed'][] = [
                                'name' => $categoryData['name'],
                                'error' => 'Maximum hierarchy depth is 4 levels.'
                            ];
                            continue;
                        }
                    }
                }

                // Handle image upload if base64 provided
                $imagePath = null;
                if (isset($categoryData['image_base64']) && !empty($categoryData['image_base64'])) {
                    try {
                        // Decode base64 image
                        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $categoryData['image_base64']));
                        
                        if ($imageData) {
                            // Generate unique filename
                            $extension = 'jpg'; // Default
                            if (preg_match('#^data:image/(\w+);base64,#i', $categoryData['image_base64'], $matches)) {
                                $extension = $matches[1];
                            }
                            
                            $filename = uniqid() . '_' . time() . '.' . $extension;
                            $filePath = 'categories/' . $filename;
                            
                            // Store new image
                            Storage::disk('public')->put($filePath, $imageData);
                            $imagePath = $filePath;
                        }
                    } catch (\Exception $e) {
                        Log::error('Error processing base64 image for new category: ' . $e->getMessage());
                    }
                }

                // Create category
                $category = Category::create([
                    'name' => $categoryData['name'],
                    'description' => $categoryData['description'] ?? null,
                    'parent_id' => $categoryData['parent_id'] ?? null,
                    'image' => $imagePath,
                    'is_active' => $categoryData['is_active'] ?? true,
                    'sort_order' => $categoryData['sort_order'] ?? 0,
                ]);

                // Sync product attributes if provided
                if (isset($categoryData['product_attribute_ids']) && is_array($categoryData['product_attribute_ids'])) {
                    $category->productAttributes()->sync($categoryData['product_attribute_ids']);
                }

                $results['success'][] = [
                    'id' => $category->id,
                    'name' => $category->name
                ];
            } catch (\Exception $e) {
                Log::error('Error creating category: ' . $e->getMessage());
                $results['failed'][] = [
                    'name' => $categoryData['name'] ?? 'Unknown',
                    'error' => $e->getMessage()
                ];
            }
        }

        // Clear parent categories cache
        $this->clearParentCategoriesCache();

        return response()->json([
            'success' => count($results['failed']) === 0,
            'message' => count($results['success']) . ' category(ies) created successfully' . 
                        (count($results['failed']) > 0 ? ', ' . count($results['failed']) . ' failed' : ''),
            'results' => $results
        ]);
    }

    /**
     * Bulk sync categories - handles both create and update in a single request.
     * Categories with 'id' are updated, categories without 'id' are created.
     */
    public function bulkSync(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'nullable|exists:categories,id', // Optional for new categories
            'categories.*.name' => 'required|string|max:255',
            'categories.*.parent_id' => 'nullable|exists:categories,id',
            'categories.*.description' => 'nullable|string',
            'categories.*.is_active' => 'nullable',
            'categories.*.sort_order' => 'nullable|integer',
            'categories.*.product_attribute_ids' => 'nullable|array',
            'categories.*.image_base64' => 'nullable|string',
        ]);

        // Normalize is_active values
        $categories = collect($request->categories)->map(function($category) {
            if (isset($category['is_active'])) {
                $category['is_active'] = filter_var($category['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($category['is_active'] === null) {
                    $category['is_active'] = true;
                }
            } else {
                $category['is_active'] = true;
            }
            return $category;
        })->toArray();
        $request->merge(['categories' => $categories]);

        $results = [
            'created' => ['success' => [], 'failed' => []],
            'updated' => ['success' => [], 'failed' => []]
        ];

        foreach ($request->categories as $categoryData) {
            $isUpdate = isset($categoryData['id']) && !empty($categoryData['id']);
            
            try {
                // Validate parent depth
                if (isset($categoryData['parent_id']) && $categoryData['parent_id']) {
                    $parentCategory = Category::whereNull('deleted_at')->find($categoryData['parent_id']);
                    if ($parentCategory) {
                        $parentDepth = $parentCategory->getDepth();
                        if ($parentDepth >= 3) {
                            if ($isUpdate) {
                                $results['updated']['failed'][] = [
                                    'id' => $categoryData['id'],
                                    'name' => $categoryData['name'],
                                    'error' => 'Maximum hierarchy depth is 4 levels.'
                                ];
                            } else {
                                $results['created']['failed'][] = [
                                    'name' => $categoryData['name'],
                                    'error' => 'Maximum hierarchy depth is 4 levels.'
                                ];
                            }
                            continue;
                        }
                    }
                }

                // Handle image upload if base64 provided
                $imagePath = null;
                if (isset($categoryData['image_base64']) && !empty($categoryData['image_base64'])) {
                    try {
                        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $categoryData['image_base64']));
                        
                        if ($imageData) {
                            $extension = 'jpg';
                            if (preg_match('#^data:image/(\w+);base64,#i', $categoryData['image_base64'], $matches)) {
                                $extension = $matches[1];
                            }
                            
                            $filename = uniqid() . '_' . time() . '.' . $extension;
                            $filePath = 'categories/' . $filename;
                            
                            if ($isUpdate) {
                                $category = Category::find($categoryData['id']);
                                if ($category && $category->image && Storage::disk('public')->exists($category->image)) {
                                    Storage::disk('public')->delete($category->image);
                                }
                            }
                            
                            Storage::disk('public')->put($filePath, $imageData);
                            $imagePath = $filePath;
                        }
                    } catch (\Exception $e) {
                        Log::error('Error processing base64 image: ' . $e->getMessage());
                    }
                }

                if ($isUpdate) {
                    // Update existing category
                    $category = Category::find($categoryData['id']);
                    if (!$category) {
                        $results['updated']['failed'][] = [
                            'id' => $categoryData['id'],
                            'name' => $categoryData['name'],
                            'error' => 'Category not found'
                        ];
                        continue;
                    }

                    $updateData = [
                        'name' => $categoryData['name'],
                        'description' => $categoryData['description'] ?? null,
                        'parent_id' => $categoryData['parent_id'] ?? null,
                        'is_active' => $categoryData['is_active'] ?? true,
                        'sort_order' => $categoryData['sort_order'] ?? 0,
                    ];
                    
                    if ($imagePath) {
                        $updateData['image'] = $imagePath;
                    }
                    
                    $category->update($updateData);

                    // Sync product attributes if provided
                    if (isset($categoryData['product_attribute_ids']) && is_array($categoryData['product_attribute_ids'])) {
                        $category->productAttributes()->sync($categoryData['product_attribute_ids']);
                    }

                    $results['updated']['success'][] = [
                        'id' => $category->id,
                        'name' => $category->name
                    ];
                } else {
                    // Create new category
                    $category = Category::create([
                        'name' => $categoryData['name'],
                        'description' => $categoryData['description'] ?? null,
                        'parent_id' => $categoryData['parent_id'] ?? null,
                        'image' => $imagePath,
                        'is_active' => $categoryData['is_active'] ?? true,
                        'sort_order' => $categoryData['sort_order'] ?? 0,
                    ]);

                    // Sync product attributes if provided
                    if (isset($categoryData['product_attribute_ids']) && is_array($categoryData['product_attribute_ids'])) {
                        $category->productAttributes()->sync($categoryData['product_attribute_ids']);
                    }

                    $results['created']['success'][] = [
                        'id' => $category->id,
                        'name' => $category->name
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Error ' . ($isUpdate ? 'updating' : 'creating') . ' category: ' . $e->getMessage());
                if ($isUpdate) {
                    $results['updated']['failed'][] = [
                        'id' => $categoryData['id'] ?? null,
                        'name' => $categoryData['name'] ?? 'Unknown',
                        'error' => $e->getMessage()
                    ];
                } else {
                    $results['created']['failed'][] = [
                        'name' => $categoryData['name'] ?? 'Unknown',
                        'error' => $e->getMessage()
                    ];
                }
            }
        }

        // Clear parent categories cache
        $this->clearParentCategoriesCache();

        $createdCount = count($results['created']['success']);
        $updatedCount = count($results['updated']['success']);
        $failedCount = count($results['created']['failed']) + count($results['updated']['failed']);

        $message = '';
        if ($createdCount > 0 && $updatedCount > 0) {
            $message = "Successfully created {$createdCount} and updated {$updatedCount} category(ies)";
        } elseif ($createdCount > 0) {
            $message = "Successfully created {$createdCount} category(ies)";
        } elseif ($updatedCount > 0) {
            $message = "Successfully updated {$updatedCount} category(ies)";
        }
        
        if ($failedCount > 0) {
            $message .= ", {$failedCount} failed";
        }

        return response()->json([
            'success' => $failedCount === 0,
            'message' => $message,
            'results' => $results
        ]);
    }

    /**
     * Get children of a category (AJAX JSON).
     */
    public function getChildren(Request $request)
    {
        $parentId = $request->get('parent_id');
        
        if (!$parentId) {
            return response()->json([
                'success' => false,
                'message' => 'Parent ID is required'
            ], 400);
        }

        $parent = Category::find($parentId);
        
        if (!$parent) {
            return response()->json([
                'success' => false,
                'message' => 'Parent category not found'
            ], 404);
        }

        $children = $parent->children()
            ->with('productAttributes')
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($child) {
                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'slug' => $child->slug,
                    'description' => $child->description,
                    'image' => $child->image,
                    'is_active' => $child->is_active,
                    'sort_order' => $child->sort_order,
                    'product_attribute_ids' => $child->productAttributes->pluck('id')->toArray(),
                    'products_count' => $child->products_count ?? 0,
                    'created_at' => $child->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $children,
            'parent' => [
                'id' => $parent->id,
                'name' => $parent->name,
            ]
        ]);
    }
}
