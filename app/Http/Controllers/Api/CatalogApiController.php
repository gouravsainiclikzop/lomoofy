<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogApiController extends Controller
{
    /**
     * Get all brands
     * GET /api/catalog/brands
     */
    public function getBrands()
    {
        $brands = Brand::where('is_active', true)->get();
        
        return response()->json([
            'success' => true,
            'data' => $brands,
        ]);
    }

    /**
     * Get single brand
     * GET /api/catalog/brands/{identifier}
     */
    public function getBrand($identifier)
    {
        $brand = Brand::where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->first();
        
        if (!$brand) {
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Brand not found'],
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $brand,
        ]);
    }

    /**
     * Get brand categories
     * GET /api/catalog/brands/{identifier}/categories
     */
    public function getBrandCategories($identifier)
    {
        $brand = Brand::where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->first();
        
        if (!$brand) {
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Brand not found'],
            ], 404);
        }
        
        $categories = Category::where('brand_id', $brand->id)
            ->where('is_active', true)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get all categories
     * GET /api/catalog/categories
     */
    public function getCategories()
    {
        $categories = Category::where('is_active', true)->get();
        
        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get single category
     * GET /api/catalog/categories/{identifier}
     */
    public function getCategory($identifier)
    {
        $category = Category::where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->first();
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Category not found'],
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    /**
     * Get category children
     * GET /api/catalog/categories/{identifier}/children
     */
    public function getCategoryChildren($identifier)
    {
        $category = Category::where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->first();
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Category not found'],
            ], 404);
        }
        
        $children = Category::where('parent_id', $category->id)
            ->where('is_active', true)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $children,
        ]);
    }

    /**
     * Get all products
     * GET /api/catalog/products
     */
    public function getProducts(Request $request)
    {
        $query = Product::where('status', 'published');
        
        // Add filters if needed
        if ($request->has('category')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }
        
        $products = $query->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get single product
     * GET /api/catalog/products/{identifier}
     */
    public function getProduct($identifier)
    {
        $product = Product::where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->first();
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Product not found'],
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }
}
