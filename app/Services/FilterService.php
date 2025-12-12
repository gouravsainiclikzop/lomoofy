<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Collection;

class FilterService
{
    /**
     * Generate filters dynamically from product data.
     * No hardcoded assumptions about category depth.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $categoryId Optional category ID to filter by
     * @return array
     */
    public function generateFilters($query, $categoryId = null)
    {
        // Clone the query to avoid modifying the original
        $productsQuery = clone $query;
        
        // If category ID is provided, filter products by category
        if ($categoryId) {
            $productsQuery->where('category_id', $categoryId);
        }
        
        // Get all products matching the query
        $products = $productsQuery->with([
            'category.parent',
            'brands',
            'variants',
            'categoryAttributeValues.categoryAttribute'
        ])->get();
        
        $filters = [
            'brands' => $this->extractBrandFilters($products),
            'attributes' => $this->extractAttributeFilters($products),
            'variants' => $this->extractVariantFilters($products),
            'price_range' => $this->extractPriceRange($products),
        ];
        
        return $filters;
    }

    /**
     * Extract brand filters from products.
     * 
     * @param Collection $products
     * @return array
     */
    protected function extractBrandFilters(Collection $products)
    {
        $brands = collect();
        
        foreach ($products as $product) {
            foreach ($product->brands as $brand) {
                $brands->push([
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                ]);
            }
        }
        
        return $brands->unique('id')->values()->toArray();
    }

    /**
     * Extract attribute filters from products.
     * Supports unlimited category depth - gets attributes from product's category and ancestors.
     * 
     * @param Collection $products
     * @return array
     */
    protected function extractAttributeFilters(Collection $products)
    {
        $attributes = collect();
        
        foreach ($products as $product) {
            // Get all applicable attributes (from category + ancestors)
            $categoryAttributes = $product->getFilterableCategoryAttributes();
            
            foreach ($categoryAttributes as $attribute) {
                $key = $attribute->slug;
                
                if (!$attributes->has($key)) {
                    $attributes->put($key, [
                        'id' => $attribute->id,
                        'name' => $attribute->name,
                        'slug' => $attribute->slug,
                        'type' => $attribute->type,
                        'options' => $attribute->getFormattedOptions(),
                        'values' => collect(),
                    ]);
                }
                
                // Get attribute values for this product
                $productAttributeValue = $product->categoryAttributeValues
                    ->firstWhere('category_attribute_id', $attribute->id);
                
                if ($productAttributeValue) {
                    $value = $productAttributeValue->getFormattedValue();
                    
                    // Handle different attribute types
                    if ($attribute->isMultiselect() && is_array($value)) {
                        foreach ($value as $v) {
                            $attributes[$key]['values']->push($v);
                        }
                    } else {
                        $attributes[$key]['values']->push($value);
                    }
                }
            }
        }
        
        // Convert values to unique arrays
        return $attributes->map(function ($attribute) {
            $attribute['values'] = $attribute['values']->unique()->filter()->values()->toArray();
            return $attribute;
        })->values()->toArray();
    }

    /**
     * Extract variant filters (size, color, etc.) from products.
     * Variants remain product-level and unaffected by category depth.
     * 
     * @param Collection $products
     * @return array
     */
    protected function extractVariantFilters(Collection $products)
    {
        $variantAttributes = collect();
        
        foreach ($products as $product) {
            foreach ($product->variants as $variant) {
                if (is_array($variant->attributes)) {
                    foreach ($variant->attributes as $key => $value) {
                        if (!$variantAttributes->has($key)) {
                            $variantAttributes->put($key, collect());
                        }
                        $variantAttributes[$key]->push($value);
                    }
                }
            }
        }
        
        return $variantAttributes->map(function ($values, $key) {
            return [
                'name' => ucfirst($key),
                'slug' => \Illuminate\Support\Str::slug($key),
                'values' => $values->unique()->filter()->values()->toArray(),
            ];
        })->values()->toArray();
    }

    /**
     * Extract price range from products.
     * 
     * @param Collection $products
     * @return array
     */
    protected function extractPriceRange(Collection $products)
    {
        $prices = collect();
        
        foreach ($products as $product) {
            // Add product base price
            if ($product->price) {
                $prices->push($product->price);
            }
            
            // Add variant prices
            foreach ($product->variants as $variant) {
                if ($variant->price) {
                    $prices->push($variant->price);
                }
                if ($variant->sale_price) {
                    $prices->push($variant->sale_price);
                }
            }
        }
        
        if ($prices->isEmpty()) {
            return ['min' => 0, 'max' => 0];
        }
        
        return [
            'min' => $prices->min(),
            'max' => $prices->max(),
        ];
    }

    /**
     * Get filters for a specific category.
     * Supports unlimited nesting - includes products in category and all descendants.
     * 
     * @param Category $category
     * @return array
     */
    public function getFiltersForCategory(Category $category)
    {
        // Get all category IDs (category + all descendants)
        $categoryIds = $category->getDescendantIds();
        $categoryIds[] = $category->id; // Include the category itself
        
        $query = Product::whereIn('category_id', $categoryIds)
            ->where('status', 'published');
        
        return $this->generateFilters($query, null);
    }
}

