<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Migrates existing product_categories relationships to single category_id.
     * Priority: primary category > deepest subcategory > first category
     */
    public function up(): void
    {
        // Get all products with their category relationships
        $productCategories = DB::table('product_categories')
            ->select('product_id', 'category_id', 'is_primary')
            ->orderBy('is_primary', 'desc')
            ->orderBy('category_id', 'asc')
            ->get()
            ->groupBy('product_id');

        foreach ($productCategories as $productId => $categories) {
            $categoryId = null;
            
            // Priority 1: Primary category
            $primaryCategory = $categories->firstWhere('is_primary', true);
            if ($primaryCategory) {
                $categoryId = $primaryCategory->category_id;
            } else {
                // Priority 2: Find deepest category (category with no children or deepest in hierarchy)
                $categoryIds = $categories->pluck('category_id')->toArray();
                
                // Get all categories with their hierarchy depth
                $categoriesWithDepth = DB::table('categories')
                    ->whereIn('id', $categoryIds)
                    ->get()
                    ->map(function ($category) {
                        // Calculate depth
                        $depth = 0;
                        $currentId = $category->parent_id;
                        while ($currentId !== null) {
                            $parent = DB::table('categories')->where('id', $currentId)->first();
                            if ($parent) {
                                $depth++;
                                $currentId = $parent->parent_id;
                            } else {
                                break;
                            }
                        }
                        return [
                            'id' => $category->id,
                            'depth' => $depth,
                            'has_children' => DB::table('categories')->where('parent_id', $category->id)->exists()
                        ];
                    });
                
                // Find deepest category that has no children (leaf node)
                $leafCategories = $categoriesWithDepth->filter(fn($c) => !$c['has_children']);
                if ($leafCategories->isNotEmpty()) {
                    $categoryId = $leafCategories->sortByDesc('depth')->first()['id'];
                } else {
                    // If no leaf nodes, use deepest category
                    $categoryId = $categoriesWithDepth->sortByDesc('depth')->first()['id'];
                }
            }
            
            // Update product with category_id
            if ($categoryId) {
                DB::table('products')
                    ->where('id', $productId)
                    ->update(['category_id' => $categoryId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     * 
     * Note: This cannot fully restore the many-to-many relationships
     * as we don't have the full history. It will create a single relationship
     * for each product based on category_id.
     */
    public function down(): void
    {
        // Get all products with category_id
        $products = DB::table('products')
            ->whereNotNull('category_id')
            ->select('id', 'category_id')
            ->get();

        foreach ($products as $product) {
            // Check if relationship already exists
            $exists = DB::table('product_categories')
                ->where('product_id', $product->id)
                ->where('category_id', $product->category_id)
                ->exists();

            if (!$exists) {
                // Create relationship with is_primary = true
                DB::table('product_categories')->insert([
                    'product_id' => $product->id,
                    'category_id' => $product->category_id,
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
