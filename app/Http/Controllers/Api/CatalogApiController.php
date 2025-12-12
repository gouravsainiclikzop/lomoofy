<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\FilterService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CatalogApiController extends Controller
{
    /**
     * Get all active brands
     * 
     * @return JsonResponse
     */
    public function getBrands(): JsonResponse
    {
        $brands = Brand::active()
            ->ordered()
            ->get()
            ->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                    'description' => $brand->description,
                    'logo' => $brand->logo_url,
                    'website' => $brand->website,
                    'is_active' => $brand->is_active,
                    'sort_order' => $brand->sort_order,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $brands,
            'count' => $brands->count(),
        ]);
    }

    /**
     * Get a specific brand with its categories
     * 
     * @param int|string $identifier Brand ID or slug
     * @return JsonResponse
     */
    public function getBrand($identifier): JsonResponse
    {
        $brand = is_numeric($identifier)
            ? Brand::find($identifier)
            : Brand::where('slug', $identifier)->first();

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found',
            ], 404);
        }

        // Get all active categories (no brand filtering, supports unlimited nesting)
        // Note: is_active can be NULL, so we check for true OR NULL (default active)
        $categories = Category::where(function ($query) {
                $query->where('is_active', true)
                      ->orWhereNull('is_active');
            })
            ->ordered()
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'image' => $category->image,
                    'is_active' => $category->is_active ?? true,
                    'sort_order' => $category->sort_order ?? 0,
                    'featured' => $category->featured ?? false,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'description' => $brand->description,
                'logo' => $brand->logo_url,
                'website' => $brand->website,
                'is_active' => $brand->is_active,
                'sort_order' => $brand->sort_order,
                'categories' => $categories,
                'categories_count' => $categories->count(),
            ],
        ]);
    }

    /**
     * Get categories for a specific brand
     * 
     * @param int|string $identifier Brand ID or slug
     * @return JsonResponse
     */
    public function getBrandCategories($identifier): JsonResponse
    {
        $brand = is_numeric($identifier)
            ? Brand::find($identifier)
            : Brand::where('slug', $identifier)->first();

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found',
            ], 404);
        }

        // Get all active categories (no brand filtering, supports unlimited nesting)
        // Note: is_active can be NULL, so we check for true OR NULL (default active)
        $categories = Category::where(function ($query) {
                $query->where('is_active', true)
                      ->orWhereNull('is_active');
            })
            ->ordered()
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'image' => $category->image,
                    'is_active' => $category->is_active ?? true,
                    'sort_order' => $category->sort_order ?? 0,
                    'featured' => $category->featured ?? false,
                    'subcategories_count' => $category->children()
                        ->where(function ($q) {
                            $q->where('is_active', true)->orWhereNull('is_active');
                        })
                        ->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
            ],
            'data' => $categories,
            'count' => $categories->count(),
        ]);
    }

    
    public function getCategories(Request $request): JsonResponse
    {
        $query = Category::query();

        // Filter by parent_only parameter (default: show all categories)
        // Note: This filters to root categories only (categories with no parent)
        if ($request->has('parent_only') && $request->parent_only == '1') {
            $query->whereNull('parent_id');
        }

        // Filter active (default: show active or NULL)
        $query->where(function ($q) {
            $q->where('is_active', true)
              ->orWhereNull('is_active');
        });

        $categories = $query->with(['parent'])
            ->ordered()
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'image' => $category->image ? asset('storage/' . $category->image) : null,
                    'is_active' => $category->is_active ?? true,
                    'sort_order' => $category->sort_order ?? 0,
                    'featured' => $category->featured ?? false,
                    'parent_id' => $category->parent_id,
                    'parent' => $category->parent ? [
                        'id' => $category->parent->id,
                        'name' => $category->parent->name,
                        'slug' => $category->parent->slug,
                    ] : null,
                    'subcategories_count' => $category->children()
                        ->where(function ($q) {
                            $q->where('is_active', true)->orWhereNull('is_active');
                        })
                        ->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $categories,
            'count' => $categories->count(),
        ]);
    }

 
    public function getCategory($identifier): JsonResponse
    {
        $category = is_numeric($identifier)
            ? Category::with(['parent'])->find($identifier)
            : Category::with(['parent'])->where('slug', $identifier)->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $children = $category->children()
            ->active()
            ->ordered()
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
                    'featured' => $child->featured,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image' => $category->image,
                'is_active' => $category->is_active,
                'sort_order' => $category->sort_order,
                'featured' => $category->featured,
                'parent' => $category->parent ? [
                    'id' => $category->parent->id,
                    'name' => $category->parent->name,
                    'slug' => $category->parent->slug,
                ] : null,
                'children' => $children,
                'children_count' => $children->count(),
            ],
        ]);
    }

  
    public function getCategoryChildren($identifier): JsonResponse
    {
        $category = is_numeric($identifier)
            ? Category::find($identifier)
            : Category::where('slug', $identifier)->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $children = $category->children()
            ->active()
            ->ordered()
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
                    'featured' => $child->featured,
                ];
            });

        return response()->json([
            'success' => true,
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ],
            'data' => $children,
            'count' => $children->count(),
        ]);
    }

  
    public function getProducts(Request $request): JsonResponse
    {
        $query = Product::with(['brands', 'category.parent', 'primaryImage', 'variants'])
            ->where('status', 'published');

        // Filter by brand
        if ($request->has('brand_id')) {
            $query->whereHas('brands', function ($q) use ($request) {
                $q->where('brands.id', $request->brand_id);
            });
        } elseif ($request->has('brand_slug')) {
            $query->whereHas('brands', function ($q) use ($request) {
                $q->where('brands.slug', $request->brand_slug);
            });
        }

        // Filter by category (includes all descendants for unlimited nesting support)
        if ($request->has('category_id')) {
            $category = Category::find($request->category_id);
            if ($category) {
                $categoryIds = $category->getDescendantIds();
                $categoryIds[] = $category->id; // Include the category itself
                $query->whereIn('category_id', $categoryIds);
            } else {
                $query->where('category_id', $request->category_id);
            }
        } elseif ($request->has('category_slug')) {
            $category = Category::where('slug', $request->category_slug)->first();
            if ($category) {
                $categoryIds = $category->getDescendantIds();
                $categoryIds[] = $category->id; // Include the category itself
                $query->whereIn('category_id', $categoryIds);
            }
        }

        // Filter by subcategory (legacy support - maps to category_id with descendants)
        // Note: This supports filtering by any category level, not just "subcategories"
        if ($request->has('subcategory_id')) {
            $category = Category::find($request->subcategory_id);
            if ($category) {
                $categoryIds = $category->getDescendantIds();
                $categoryIds[] = $category->id; // Include the category itself
                $query->whereIn('category_id', $categoryIds);
            } else {
                $query->where('category_id', $request->subcategory_id);
            }
        } elseif ($request->has('subcategory_slug')) {
            $category = Category::where('slug', $request->subcategory_slug)->first();
            if ($category) {
                $categoryIds = $category->getDescendantIds();
                $categoryIds[] = $category->id; // Include the category itself
                $query->whereIn('category_id', $categoryIds);
            }
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter featured products
        if ($request->has('featured')) {
            $query->where('featured', $request->featured == '1' || $request->featured === true);
        }

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = min((int) ($request->per_page ?? 20), 100); // Max 100 per page
        $products = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $products->getCollection()->transform(function ($product) {
            $primaryBrand = $product->brands->firstWhere('pivot.is_primary') ?? $product->brands->first();
            
            $minPrice = $product->variants->min('price') ?? $product->price;
            $maxPrice = $product->variants->max('price') ?? $product->price;
            
            return [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'short_description' => $product->short_description,
                'type' => $product->type,
                'status' => $product->status,
                'featured' => $product->featured,
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'price_range' => $minPrice != $maxPrice ? [
                    'min' => $minPrice,
                    'max' => $maxPrice,
                ] : null,
                'brand' => $primaryBrand ? [
                    'id' => $primaryBrand->id,
                    'name' => $primaryBrand->name,
                    'slug' => $primaryBrand->slug,
                ] : null,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                    'path' => $product->category_path,
                ] : null,
                'primary_image' => $product->primaryImage ? [
                    'id' => $product->primaryImage->id,
                    'path' => $product->primaryImage->image_path,
                    'url' => asset('storage/' . $product->primaryImage->image_path),
                    'alt' => $product->primaryImage->alt_text,
                ] : null,
                'variants_count' => $product->variants->count(),
                'tags' => $product->tags ? explode(', ', $product->tags) : [],
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ];
        });

        // Generate dynamic filters from product data
        $filterService = new FilterService();
        $categoryId = $request->category_id ?? ($request->category_slug ? Category::where('slug', $request->category_slug)->value('id') : null);
        $dynamicFilters = $filterService->generateFilters($query, $categoryId);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
            'filters' => $dynamicFilters,
            'applied_filters' => [
                'brand_id' => $request->brand_id,
                'brand_slug' => $request->brand_slug,
                'category_id' => $request->category_id,
                'category_slug' => $request->category_slug,
                'subcategory_id' => $request->subcategory_id, // Legacy support
                'subcategory_slug' => $request->subcategory_slug, // Legacy support
                'status' => $request->status ?? 'published',
                'featured' => $request->featured,
                'search' => $request->search,
            ],
        ]);
    }

    /**
     * Get a specific product with full details
     * 
     * @param int|string $identifier Product ID, SKU, or slug
     * @return JsonResponse
     */
    public function getProduct($identifier): JsonResponse
    {
        $product = is_numeric($identifier)
            ? Product::where('id', $identifier)->orWhere('sku', $identifier)->first()
            : Product::where('slug', $identifier)->orWhere('sku', $identifier)->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $product->load(['brands', 'category.parent', 'images', 'variants']);

        $primaryBrand = $product->brands->firstWhere('pivot.is_primary') ?? $product->brands->first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'short_description' => $product->short_description,
                'type' => $product->type,
                'status' => $product->status,
                'featured' => $product->featured,
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'stock_quantity' => $product->stock_quantity,
                'stock_status' => $product->stock_status,
                'brand' => $primaryBrand ? [
                    'id' => $primaryBrand->id,
                    'name' => $primaryBrand->name,
                    'slug' => $primaryBrand->slug,
                    'logo' => $primaryBrand->logo_url,
                ] : null,
                'brands' => $product->brands->map(function ($brand) {
                    return [
                        'id' => $brand->id,
                        'name' => $brand->name,
                        'slug' => $brand->slug,
                        'is_primary' => $brand->pivot->is_primary ?? false,
                    ];
                }),
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                    'path' => $product->category_path,
                    'parent' => $product->category->parent ? [
                        'id' => $product->category->parent->id,
                        'name' => $product->category->parent->name,
                        'slug' => $product->category->parent->slug,
                    ] : null,
                ] : null,
                'images' => $product->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'path' => $image->image_path,
                        'url' => asset('storage/' . $image->image_path),
                        'alt_text' => $image->alt_text,
                        'is_primary' => $image->is_primary,
                        'sort_order' => $image->sort_order,
                    ];
                }),
                'variants' => $product->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'name' => $variant->name,
                        'attributes' => $variant->attributes,
                        'price' => $variant->price,
                        'sale_price' => $variant->sale_price,
                        'cost_price' => $variant->cost_price,
                        'stock_quantity' => $variant->stock_quantity,
                        'stock_status' => $variant->stock_status,
                        'is_active' => $variant->is_active,
                        'sort_order' => $variant->sort_order,
                    ];
                }),
                'tags' => $product->tags ? explode(', ', $product->tags) : [],
                'meta' => [
                    'title' => $product->meta_title,
                    'description' => $product->meta_description,
                    'keywords' => $product->meta_keywords,
                ],
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ],
        ]);
    }
}

