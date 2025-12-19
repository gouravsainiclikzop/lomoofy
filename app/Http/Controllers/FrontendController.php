<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;

class FrontendController extends Controller
{
    public function index(Request $request)
    {
        // Get parent categories (top-level categories) for homepage display
        // Limit to first 3 for the 3-column layout, or get all if fewer than 3
        $parentCategories = Category::whereNull('parent_id')
            ->where(function($q) {
                $q->where('is_active', true)->orWhereNull('is_active');
            })
            ->orderBy('sort_order')
            ->limit(3)
            ->get();
        
        // Get wishlist product IDs for current user/session
        $customer = $request->user();
        $customerId = ($customer && $customer instanceof \App\Models\Customer) ? $customer->id : null;
        $sessionId = $request->input('session_id') 
                  ?? $request->query('session_id') 
                  ?? $request->header('X-Session-ID') 
                  ?? session()->getId();
        
        $wishlistProductIds = \App\Models\Wishlist::where(function($query) use ($customerId, $sessionId) {
            if ($customerId) {
                $query->where('customer_id', $customerId);
            } else {
                $query->where('session_id', $sessionId);
            }
        })->pluck('product_id')->toArray();
        
        // Debug logging
        Log::info('Wishlist Debug', [
            'customer_id' => $customerId,
            'session_id' => $sessionId,
            'wishlist_product_ids' => $wishlistProductIds,
            'count' => count($wishlistProductIds)
        ]);
        
        // Get latest products for "New Arrivals" section
        $newArrivals = Product::with([
            'primaryImage',
            'images' => function($q) {
                $q->orderBy('sort_order')->orderBy('id')->limit(1);
            },
            'variants' => function($q) {
                $q->where('is_active', true)
                  ->orderBy('sort_order')
                  ->with(['images' => function($imgQ) {
                      $imgQ->orderBy('sort_order')->orderBy('id');
                  }]);
            }
        ])
        ->where('status', 'published')
        ->orderBy('created_at', 'desc')
        ->limit(8)
        ->get()
        ->map(function($product) use ($wishlistProductIds) {
            // Get price range from variants
            $activeVariants = $product->variants->where('is_active', true);
            $prices = $activeVariants->pluck('price')->filter();
            $salePrices = $activeVariants->pluck('sale_price')->filter();
            
            // Calculate display prices (sale price if available, otherwise regular price)
            $displayPrices = $activeVariants->map(function($variant) {
                $price = $variant->price ?? 0;
                $salePrice = $variant->sale_price;
                // Use sale price if it exists and is less than regular price, otherwise use regular price
                return ($salePrice && $salePrice < $price) ? $salePrice : $price;
            })->filter();
            
            $minPrice = $prices->min() ?? 0;
            $maxPrice = $prices->max() ?? 0;
            $minDisplayPrice = $displayPrices->min() ?? $minPrice;
            $maxDisplayPrice = $displayPrices->max() ?? $maxPrice;
            $minSalePrice = $salePrices->min();
            $maxSalePrice = $salePrices->max();
            
            // Determine if product is on sale
            $hasSale = $minSalePrice && $minSalePrice < $minPrice;
            
            // Get product image
            $imageUrl = $product->primaryImage 
                ? asset('storage/' . $product->primaryImage->image_path)
                : ($product->images->first() 
                    ? asset('storage/' . $product->images->first()->image_path)
                    : asset('frontend/images/product/1.jpg'));
            
            // Get all color attributes (from category or global)
            $colorAttributes = collect();
            if ($product->category) {
                $colorAttributes = $product->category->getAllProductAttributes()
                    ->where('type', 'color');
            }
            
            if ($colorAttributes->isEmpty()) {
                $colorAttributes = ProductAttribute::where('type', 'color')
                    ->orWhere(function($q) {
                        $q->where('slug', 'color')->orWhere('name', 'Color');
                    })
                    ->get();
            }
            
            // Get size attributes (from category or global)
            $sizeAttributes = collect();
            if ($product->category) {
                $sizeAttributes = $product->category->getAllProductAttributes()
                    ->where('type', 'size');
            }
            
            if ($sizeAttributes->isEmpty()) {
                $sizeAttributes = ProductAttribute::where('type', 'size')
                    ->orWhere(function($q) {
                        $q->where('slug', 'size')->orWhere('name', 'Size');
                    })
                    ->get();
            }
            
            // Create a map of attribute_id => attribute for quick lookup
            $colorAttributeMap = $colorAttributes->keyBy('id');
            $colorAttributeNameMap = $colorAttributes->keyBy('name');
            $sizeAttributeMap = $sizeAttributes->keyBy('id');
            $sizeAttributeNameMap = $sizeAttributes->keyBy('name');
            
            // Get color variants for color options
            $colorVariants = $activeVariants->filter(function($variant) use ($colorAttributeMap, $colorAttributeNameMap) {
                if (!$variant->attributes) {
                    return false;
                }
                $attrs = is_string($variant->attributes) 
                    ? json_decode($variant->attributes, true) 
                    : $variant->attributes;
                
                if (!is_array($attrs)) {
                    return false;
                }
                
                // Check each attribute in the variant
                foreach ($attrs as $key => $value) {
                    if (empty($value)) {
                        continue;
                    }
                    
                    // Check if key is numeric (attribute_id)
                    if (is_numeric($key) && isset($colorAttributeMap[$key])) {
                        return true; // Found color attribute by ID
                    }
                    
                    // Check if key is attribute name
                    if (isset($colorAttributeNameMap[$key]) || 
                        isset($colorAttributeNameMap[ucfirst($key)]) ||
                        strtolower($key) === 'color') {
                        return true; // Found color attribute by name
                    }
                }
                return false;
            });
            
            // Format price display
            $priceDisplay = '';
            if ($hasSale && $minSalePrice) {
                $priceDisplay = '$' . number_format($minSalePrice, 0);
                if ($maxSalePrice && $minSalePrice != $maxSalePrice) {
                    $priceDisplay .= ' - $' . number_format($maxSalePrice, 0);
                }
            } else {
                if ($minPrice != $maxPrice) {
                    $priceDisplay = '$' . number_format($minPrice, 0) . ' - $' . number_format($maxPrice, 0);
                } else {
                    $priceDisplay = '$' . number_format($minPrice, 0);
                }
            }
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'image_url' => $imageUrl,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'min_display_price' => $minDisplayPrice,
                'max_display_price' => $maxDisplayPrice,
                'min_sale_price' => $minSalePrice,
                'max_sale_price' => $maxSalePrice,
                'has_sale' => $hasSale,
                'price_display' => $priceDisplay,
                'is_new' => $product->created_at->isAfter(now()->subDays(30)), // New if created within 30 days
                'is_featured' => $product->featured,
                'in_wishlist' => in_array($product->id, $wishlistProductIds), // Check if product is in wishlist
                'color_variants' => $colorVariants->map(function($variant) use ($colorAttributeMap, $colorAttributeNameMap, $sizeAttributeMap, $sizeAttributeNameMap, $imageUrl, $activeVariants) {
                    $attrs = is_string($variant->attributes) 
                        ? json_decode($variant->attributes, true) 
                        : ($variant->attributes ?? []);
                    
                    // Get color value and attribute_id
                    $colorValue = null;
                    $colorAttributeId = null;
                    $sizeValue = null;
                    $attrs = is_array($attrs) ? $attrs : [];
                    
                    foreach ($attrs as $key => $value) {
                        if (empty($value)) {
                            continue;
                        }
                        
                        // Check for color attribute
                        if (is_numeric($key) && isset($colorAttributeMap[$key])) {
                            $colorValue = $value;
                            $colorAttributeId = (int)$key;
                        } elseif (isset($colorAttributeNameMap[$key]) || 
                                 isset($colorAttributeNameMap[ucfirst($key)]) ||
                                 strtolower($key) === 'color') {
                            $colorValue = $value;
                            $attribute = $colorAttributeNameMap[$key] ?? 
                                        $colorAttributeNameMap[ucfirst($key)] ?? 
                                        null;
                            $colorAttributeId = $attribute ? $attribute->id : null;
                        }
                        
                        // Check for size attribute
                        if (is_numeric($key) && isset($sizeAttributeMap[$key])) {
                            $sizeValue = $value;
                        } elseif (isset($sizeAttributeNameMap[$key]) || 
                                 isset($sizeAttributeNameMap[ucfirst($key)]) ||
                                 strtolower($key) === 'size') {
                            $sizeValue = $value;
                        }
                    }
                    
                    // Get color code from ProductAttributeValue
                    $colorCode = '#ccc'; // Default fallback
                    if ($colorAttributeId && $colorValue) {
                        // Look up ProductAttributeValue using attribute_id and value
                        $attributeValue = ProductAttributeValue::where('attribute_id', $colorAttributeId)
                            ->where('value', $colorValue)
                            ->first();
                        
                        // If not found, try case-insensitive match
                        if (!$attributeValue) {
                            $attributeValue = ProductAttributeValue::where('attribute_id', $colorAttributeId)
                                ->whereRaw('LOWER(value) = ?', [strtolower($colorValue)])
                                ->first();
                        }
                        
                        if ($attributeValue && $attributeValue->color_code) {
                            $colorCode = $attributeValue->color_code;
                        } else {
                            // Fallback: Generate a basic color code from common color names
                            $colorCode = self::getColorCodeFromName($colorValue);
                        }
                    } elseif ($colorValue) {
                        // If no attribute_id found but we have a color value, try to get code from name
                        $colorCode = self::getColorCodeFromName($colorValue);
                    }
                    
                    // Get variant image (primary image or first image)
                    $variantImage = $imageUrl; // Fallback to product image
                    if ($variant->images && $variant->images->count() > 0) {
                        $primaryVariantImage = $variant->images->where('is_primary', true)->first();
                        if ($primaryVariantImage) {
                            $variantImage = asset('storage/' . $primaryVariantImage->image_path);
                        } else {
                            $firstVariantImage = $variant->images->first();
                            if ($firstVariantImage) {
                                $variantImage = asset('storage/' . $firstVariantImage->image_path);
                            }
                        }
                    }
                    
                    // Get all variants with this color to find available sizes
                    $variantsWithSameColor = $activeVariants->filter(function($v) use ($colorValue, $colorAttributeMap, $colorAttributeNameMap) {
                        if (!$v->attributes) return false;
                        $vAttrs = is_string($v->attributes) 
                            ? json_decode($v->attributes, true) 
                            : ($v->attributes ?? []);
                        if (!is_array($vAttrs)) return false;
                        
                        foreach ($vAttrs as $k => $val) {
                            if (empty($val)) continue;
                            
                            // Check by attribute ID
                            if (is_numeric($k) && isset($colorAttributeMap[$k]) && $val === $colorValue) {
                                return true;
                            }
                            
                            // Check by attribute name
                            if ((isset($colorAttributeNameMap[$k]) || 
                                 isset($colorAttributeNameMap[ucfirst($k)]) ||
                                 strtolower($k) === 'color') && $val === $colorValue) {
                                return true;
                            }
                        }
                        return false;
                    });
                    
                    // Extract available sizes for this color
                    $availableSizes = [];
                    foreach ($variantsWithSameColor as $v) {
                        $vAttrs = is_string($v->attributes) 
                            ? json_decode($v->attributes, true) 
                            : ($v->attributes ?? []);
                        if (!is_array($vAttrs)) continue;
                        
                        foreach ($vAttrs as $k => $val) {
                            if (empty($val)) continue;
                            
                            // Check by size attribute ID
                            if (is_numeric($k) && isset($sizeAttributeMap[$k])) {
                                if (!in_array($val, $availableSizes)) {
                                    $availableSizes[] = $val;
                                }
                            } 
                            // Check by size attribute name
                            elseif ((isset($sizeAttributeNameMap[$k]) || 
                                    isset($sizeAttributeNameMap[ucfirst($k)]) ||
                                    strtolower($k) === 'size') && !in_array($val, $availableSizes)) {
                                $availableSizes[] = $val;
                            }
                        }
                    }
                    
                    // Get price for this variant
                    $price = $variant->price ?? 0;
                    $salePrice = $variant->sale_price;
                    $hasSale = $salePrice && $salePrice < $price;
                    
                    return [
                        'id' => $variant->id,
                        'color' => $colorValue,
                        'color_code' => $colorCode,
                        'image' => $variantImage, // Use variant image
                        'price' => $price,
                        'sale_price' => $salePrice,
                        'has_sale' => $hasSale,
                        'display_price' => $hasSale ? $salePrice : $price,
                        'size' => $sizeValue, // Size for this specific variant
                        'available_sizes' => $availableSizes, // All available sizes for this color
                        'images' => $variant->images->map(function($img) {
                            return [
                                'url' => asset('storage/' . $img->image_path),
                                'alt' => $img->alt_text ?? ''
                            ];
                        }),
                    ];
                })->unique('color')->values()->take(4),
            ];
        });
        
        // Get active collections for homepage
        $collections = \App\Models\OurCollection::where('is_active', true)
            ->with('category')
            ->orderBy('sort_order')
            ->get(); 
        
        return view('frontend.index', compact('parentCategories', 'newArrivals', 'collections'));
    }

    public function shop(Request $request)
    {
        $selectedCategory = null;
        $childCategories = collect([]);
        $parentCategories = collect([]);
        $breadcrumb = [];
        
        // Get all parent categories (top-level categories) for the category list
        $parentCategories = Category::whereNull('parent_id')
            ->where(function($q) {
                $q->where('is_active', true)->orWhereNull('is_active');
            })
            ->with(['children' => function($query) {
                $query->where(function($q) {
                    $q->where('is_active', true)->orWhereNull('is_active');
                })->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();
        
        // Check if category parameter is provided
        if ($request->has('category') && $request->category) {
            $selectedCategory = Category::where('slug', $request->category)
                ->where(function($q) {
                    $q->where('is_active', true)->orWhereNull('is_active');
                })
                ->first();
            
            // If category found, get its direct children
            if ($selectedCategory) {
                $childCategories = Category::where('parent_id', $selectedCategory->id)
                    ->where(function($q) {
                        $q->where('is_active', true)->orWhereNull('is_active');
                    })
                    ->orderBy('sort_order')
                    ->get();
                
                // Build breadcrumb path using getAncestors method
                $ancestors = $selectedCategory->getAncestors();
                $breadcrumb = $ancestors->reverse()->map(function($ancestor) {
                    return [
                        'name' => $ancestor->name,
                        'slug' => $ancestor->slug,
                    ];
                })->values()->toArray();
                
                // Add current category to breadcrumb
                $breadcrumb[] = [
                    'name' => $selectedCategory->name,
                    'slug' => $selectedCategory->slug,
                ];
            }
        }
        
        // Get wishlist product IDs for current user/session
        $customer = $request->user();
        $customerId = ($customer && $customer instanceof \App\Models\Customer) ? $customer->id : null;
        $sessionId = $request->input('session_id') 
                  ?? $request->query('session_id') 
                  ?? $request->header('X-Session-ID') 
                  ?? session()->getId();
        
        $wishlistProductIds = \App\Models\Wishlist::where(function($query) use ($customerId, $sessionId) {
            if ($customerId) {
                $query->where('customer_id', $customerId);
            } else {
                $query->where('session_id', $sessionId);
            }
        })->pluck('product_id')->toArray();
        
        // Build products query
        $productsQuery = Product::with([
            'primaryImage',
            'images' => function($q) {
                $q->orderBy('sort_order')->orderBy('id')->limit(1);
            },
            'variants' => function($q) {
                $q->where('is_active', true)
                  ->orderBy('sort_order')
                  ->with(['images' => function($imgQ) {
                      $imgQ->orderBy('sort_order')->orderBy('id');
                  }]);
            }
        ])
        ->where('status', 'published');
        
        // Filter by category if selected
        if ($selectedCategory) {
            // Get all category IDs including children and grandchildren
            $categoryIds = [$selectedCategory->id];
            $categoryIds = array_merge($categoryIds, $selectedCategory->getDescendantIds());
            
            // Filter by primary category_id OR by product_categories pivot table
            $productsQuery->where(function($query) use ($categoryIds) {
                $query->whereIn('category_id', $categoryIds)
                      ->orWhereHas('categories', function($q) use ($categoryIds) {
                          $q->whereIn('categories.id', $categoryIds);
                      });
            });
        }
        
        // Apply search filter if provided
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $productsQuery->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('slug', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Get total count before limiting
        $totalProducts = $productsQuery->count();
        
        // Limit to 20 products initially
        $products = $productsQuery->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->map(function($product) use ($wishlistProductIds) {
                // Get price range from variants
                $activeVariants = $product->variants->where('is_active', true);
                $prices = $activeVariants->pluck('price')->filter();
                $salePrices = $activeVariants->pluck('sale_price')->filter();
                
                // Calculate display prices (sale price if available, otherwise regular price)
                $displayPrices = $activeVariants->map(function($variant) {
                    $price = $variant->price ?? 0;
                    $salePrice = $variant->sale_price;
                    // Use sale price if it exists and is less than regular price, otherwise use regular price
                    return ($salePrice && $salePrice < $price) ? $salePrice : $price;
                })->filter();
                
                $minPrice = $prices->min() ?? 0;
                $maxPrice = $prices->max() ?? 0;
                $minDisplayPrice = $displayPrices->min() ?? $minPrice;
                $maxDisplayPrice = $displayPrices->max() ?? $maxPrice;
                $minSalePrice = $salePrices->min();
                $maxSalePrice = $salePrices->max();
                
                // Determine if product is on sale
                $hasSale = $minSalePrice && $minSalePrice < $minPrice;
                
                // Get product image
                $imageUrl = $product->primaryImage 
                    ? asset('storage/' . $product->primaryImage->image_path)
                    : ($product->images->first() 
                        ? asset('storage/' . $product->images->first()->image_path)
                        : asset('frontend/images/product/1.jpg'));
                
                // Get all color attributes (from category or global)
                $colorAttributes = collect();
                if ($product->category) {
                    $colorAttributes = $product->category->getAllProductAttributes()
                        ->where('type', 'color');
                }
                
                if ($colorAttributes->isEmpty()) {
                    $colorAttributes = ProductAttribute::where('type', 'color')
                        ->orWhere(function($q) {
                            $q->where('slug', 'color')->orWhere('name', 'Color');
                        })
                        ->get();
                }
                
                // Get size attributes (from category or global)
                $sizeAttributes = collect();
                if ($product->category) {
                    $sizeAttributes = $product->category->getAllProductAttributes()
                        ->where('type', 'size');
                }
                
                if ($sizeAttributes->isEmpty()) {
                    $sizeAttributes = ProductAttribute::where('type', 'size')
                        ->orWhere(function($q) {
                            $q->where('slug', 'size')->orWhere('name', 'Size');
                        })
                        ->get();
                }
                
                // Create a map of attribute_id => attribute for quick lookup
                $colorAttributeMap = $colorAttributes->keyBy('id');
                $colorAttributeNameMap = $colorAttributes->keyBy('name');
                $sizeAttributeMap = $sizeAttributes->keyBy('id');
                $sizeAttributeNameMap = $sizeAttributes->keyBy('name');
                
                // Get color variants for color options
                $colorVariants = $activeVariants->filter(function($variant) use ($colorAttributeMap, $colorAttributeNameMap) {
                    if (!$variant->attributes) {
                        return false;
                    }
                    $attrs = is_string($variant->attributes) 
                        ? json_decode($variant->attributes, true) 
                        : $variant->attributes;
                    
                    if (!is_array($attrs)) {
                        return false;
                    }
                    
                    // Check each attribute in the variant
                    foreach ($attrs as $key => $value) {
                        if (empty($value)) {
                            continue;
                        }
                        
                        // Check if key is numeric (attribute_id)
                        if (is_numeric($key) && isset($colorAttributeMap[$key])) {
                            return true; // Found color attribute by ID
                        }
                        
                        // Check if key is attribute name
                        if (isset($colorAttributeNameMap[$key]) || 
                            isset($colorAttributeNameMap[ucfirst($key)]) ||
                            strtolower($key) === 'color') {
                            return true; // Found color attribute by name
                        }
                    }
                    return false;
                });
                
                // Format price display
                $priceDisplay = '';
                if ($hasSale && $minSalePrice) {
                    $priceDisplay = '$' . number_format($minSalePrice, 0);
                    if ($maxSalePrice && $minSalePrice != $maxSalePrice) {
                        $priceDisplay .= ' - $' . number_format($maxSalePrice, 0);
                    }
                } else {
                    if ($minPrice != $maxPrice) {
                        $priceDisplay = '$' . number_format($minPrice, 0) . ' - $' . number_format($maxPrice, 0);
                    } else {
                        $priceDisplay = '$' . number_format($minPrice, 0);
                    }
                }
                
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'image_url' => $imageUrl,
                    'min_price' => $minPrice,
                    'max_price' => $maxPrice,
                    'min_display_price' => $minDisplayPrice,
                    'max_display_price' => $maxDisplayPrice,
                    'min_sale_price' => $minSalePrice,
                    'max_sale_price' => $maxSalePrice,
                    'has_sale' => $hasSale,
                    'price_display' => $priceDisplay,
                    'is_new' => $product->created_at->isAfter(now()->subDays(30)), // New if created within 30 days
                    'is_featured' => $product->featured,
                    'in_wishlist' => in_array($product->id, $wishlistProductIds), // Check if product is in wishlist
                    'color_variants' => $colorVariants->map(function($variant) use ($colorAttributeMap, $colorAttributeNameMap, $sizeAttributeMap, $sizeAttributeNameMap, $imageUrl, $activeVariants) {
                        $attrs = is_string($variant->attributes) 
                            ? json_decode($variant->attributes, true) 
                            : ($variant->attributes ?? []);
                        
                        // Get color value and attribute_id
                        $colorValue = null;
                        $colorAttributeId = null;
                        $sizeValue = null;
                        $attrs = is_array($attrs) ? $attrs : [];
                        
                        foreach ($attrs as $key => $value) {
                            if (empty($value)) {
                                continue;
                            }
                            
                            // Check for color attribute
                            if (is_numeric($key) && isset($colorAttributeMap[$key])) {
                                $colorValue = $value;
                                $colorAttributeId = (int)$key;
                            } elseif (isset($colorAttributeNameMap[$key]) || 
                                     isset($colorAttributeNameMap[ucfirst($key)]) ||
                                     strtolower($key) === 'color') {
                                $colorValue = $value;
                                $attribute = $colorAttributeNameMap[$key] ?? 
                                            $colorAttributeNameMap[ucfirst($key)] ?? 
                                            null;
                                $colorAttributeId = $attribute ? $attribute->id : null;
                            }
                            
                            // Check for size attribute
                            if (is_numeric($key) && isset($sizeAttributeMap[$key])) {
                                $sizeValue = $value;
                            } elseif (isset($sizeAttributeNameMap[$key]) || 
                                     isset($sizeAttributeNameMap[ucfirst($key)]) ||
                                     strtolower($key) === 'size') {
                                $sizeValue = $value;
                            }
                        }
                        
                        // Get color code from ProductAttributeValue
                        $colorCode = '#ccc'; // Default fallback
                        if ($colorAttributeId && $colorValue) {
                            // Look up ProductAttributeValue using attribute_id and value
                            $attributeValue = ProductAttributeValue::where('attribute_id', $colorAttributeId)
                                ->where('value', $colorValue)
                                ->first();
                            
                            // If not found, try case-insensitive match
                            if (!$attributeValue) {
                                $attributeValue = ProductAttributeValue::where('attribute_id', $colorAttributeId)
                                    ->whereRaw('LOWER(value) = ?', [strtolower($colorValue)])
                                    ->first();
                            }
                            
                            if ($attributeValue && $attributeValue->color_code) {
                                $colorCode = $attributeValue->color_code;
                            } else {
                                // Fallback: Generate a basic color code from common color names
                                $colorCode = self::getColorCodeFromName($colorValue);
                            }
                        } elseif ($colorValue) {
                            // If no attribute_id found but we have a color value, try to get code from name
                            $colorCode = self::getColorCodeFromName($colorValue);
                        }
                        
                        // Get variant image (primary image or first image)
                        $variantImage = $imageUrl; // Fallback to product image
                        if ($variant->images && $variant->images->count() > 0) {
                            $primaryVariantImage = $variant->images->where('is_primary', true)->first();
                            if ($primaryVariantImage) {
                                $variantImage = asset('storage/' . $primaryVariantImage->image_path);
                            } else {
                                $firstVariantImage = $variant->images->first();
                                if ($firstVariantImage) {
                                    $variantImage = asset('storage/' . $firstVariantImage->image_path);
                                }
                            }
                        }
                        
                        // Get all variants with this color to find available sizes
                        $variantsWithSameColor = $activeVariants->filter(function($v) use ($colorValue, $colorAttributeMap, $colorAttributeNameMap) {
                            if (!$v->attributes) return false;
                            $vAttrs = is_string($v->attributes) 
                                ? json_decode($v->attributes, true) 
                                : ($v->attributes ?? []);
                            if (!is_array($vAttrs)) return false;
                            
                            foreach ($vAttrs as $k => $val) {
                                if (empty($val)) continue;
                                
                                // Check by attribute ID
                                if (is_numeric($k) && isset($colorAttributeMap[$k]) && $val === $colorValue) {
                                    return true;
                                }
                                
                                // Check by attribute name
                                if ((isset($colorAttributeNameMap[$k]) || 
                                     isset($colorAttributeNameMap[ucfirst($k)]) ||
                                     strtolower($k) === 'color') && $val === $colorValue) {
                                    return true;
                                }
                            }
                            return false;
                        });
                        
                        // Extract available sizes for this color
                        $availableSizes = [];
                        foreach ($variantsWithSameColor as $v) {
                            $vAttrs = is_string($v->attributes) 
                                ? json_decode($v->attributes, true) 
                                : ($v->attributes ?? []);
                            if (!is_array($vAttrs)) continue;
                            
                            foreach ($vAttrs as $k => $val) {
                                if (empty($val)) continue;
                                
                                // Check by size attribute ID
                                if (is_numeric($k) && isset($sizeAttributeMap[$k])) {
                                    if (!in_array($val, $availableSizes)) {
                                        $availableSizes[] = $val;
                                    }
                                } 
                                // Check by size attribute name
                                elseif ((isset($sizeAttributeNameMap[$k]) || 
                                        isset($sizeAttributeNameMap[ucfirst($k)]) ||
                                        strtolower($k) === 'size') && !in_array($val, $availableSizes)) {
                                    $availableSizes[] = $val;
                                }
                            }
                        }
                        
                        // Get price for this variant
                        $price = $variant->price ?? 0;
                        $salePrice = $variant->sale_price;
                        $hasSale = $salePrice && $salePrice < $price;
                        
                        return [
                            'id' => $variant->id,
                            'color' => $colorValue,
                            'color_code' => $colorCode,
                            'image' => $variantImage, // Use variant image
                            'price' => $price,
                            'sale_price' => $salePrice,
                            'has_sale' => $hasSale,
                            'display_price' => $hasSale ? $salePrice : $price,
                            'size' => $sizeValue, // Size for this specific variant
                            'available_sizes' => $availableSizes, // All available sizes for this color
                            'images' => $variant->images->map(function($img) {
                                return [
                                    'url' => asset('storage/' . $img->image_path),
                                    'alt' => $img->alt_text ?? ''
                                ];
                            }),
                        ];
                    })->unique('color')->values()->take(4),
                ];
            });
        
        $hasMoreProducts = $totalProducts > 20;
        $currentPage = 1;
        
        // Get min and max prices from product_variants (considering sale prices)
        $priceRange = DB::table('product_variants')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->where('product_variants.is_active', true)
            ->where('products.status', 'published')
            ->selectRaw('
                MIN(COALESCE(
                    CASE 
                        WHEN product_variants.sale_price IS NOT NULL AND product_variants.sale_price > 0 AND product_variants.sale_price < product_variants.price 
                        THEN product_variants.sale_price 
                        ELSE product_variants.price 
                    END,
                    product_variants.price,
                    0
                )) as min_price,
                MAX(COALESCE(product_variants.price, 0)) as max_price
            ')
            ->first();
        
        $minPrice = $priceRange && $priceRange->min_price ? (int)$priceRange->min_price : 0;
        $maxPrice = $priceRange && $priceRange->max_price ? (int)$priceRange->max_price : 1000;
        
        // Round up max price to nearest 100 for better UX
        $maxPrice = ceil($maxPrice / 100) * 100;
        // Ensure min is at least 0 and max is greater than min
        if ($maxPrice <= $minPrice) {
            $maxPrice = $minPrice + 100;
        }
        
        // Get available sizes from product variants
        $sizeAttribute = ProductAttribute::where('type', 'size')
            ->orWhere(function($q) {
                $q->where('slug', 'size')->orWhere('name', 'Size');
            })
            ->first();
        
        $availableSizes = collect();
        if ($sizeAttribute) {
            // Get all size values from variants attributes
            $variants = DB::table('product_variants')
                ->join('products', 'product_variants.product_id', '=', 'products.id')
                ->where('product_variants.is_active', true)
                ->where('products.status', 'published')
                ->select('product_variants.attributes')
                ->get();
            
            $sizeValues = collect();
            foreach ($variants as $variant) {
                $attributes = is_string($variant->attributes) 
                    ? json_decode($variant->attributes, true) 
                    : ($variant->attributes ?? []);
                
                if (is_array($attributes)) {
                    // Check by attribute ID
                    if (isset($attributes[$sizeAttribute->id])) {
                        $sizeValues->push($attributes[$sizeAttribute->id]);
                    }
                    // Check by attribute name/slug
                    if (isset($attributes['size']) || isset($attributes['Size']) || isset($attributes[$sizeAttribute->name]) || isset($attributes[$sizeAttribute->slug])) {
                        $sizeValue = $attributes['size'] ?? $attributes['Size'] ?? $attributes[$sizeAttribute->name] ?? $attributes[$sizeAttribute->slug];
                        if ($sizeValue) {
                            $sizeValues->push($sizeValue);
                        }
                    }
                }
            }
            
            // Get unique sizes, sorted naturally (numeric first, then alphabetic)
            $availableSizes = $sizeValues->unique()->map(function($size) {
                return trim($size);
            })->filter()->sort(function($a, $b) {
                // Natural sort: numbers first, then strings
                $aNum = is_numeric($a);
                $bNum = is_numeric($b);
                if ($aNum && $bNum) {
                    return (int)$a <=> (int)$b;
                } elseif ($aNum) {
                    return -1;
                } elseif ($bNum) {
                    return 1;
                }
                return strcasecmp($a, $b);
            })->values();
        }
        
        // Get brands with product counts
        // Get brands that have published products (from product_brands pivot table or brand_id)
        $brands = \App\Models\Brand::where('is_active', true)
            ->where(function($q) {
                // Brands with products via product_brands pivot
                $q->whereHas('productBrands', function($query) {
                    $query->where('status', 'published');
                })
                // OR brands with products via direct brand_id (using exists subquery instead of relationship)
                ->orWhereExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('products')
                        ->whereColumn('products.brand_id', 'brands.id')
                        ->where('products.status', 'published');
                });
            })
            ->get()
            ->map(function($brand) {
                // Count products from product_brands pivot
                $pivotCount = DB::table('product_brands')
                    ->join('products', 'product_brands.product_id', '=', 'products.id')
                    ->where('product_brands.brand_id', $brand->id)
                    ->where('products.status', 'published')
                    ->count();
                
                // Count products from brand_id
                $directCount = Product::where('brand_id', $brand->id)
                    ->where('status', 'published')
                    ->count();
                
                $totalCount = $pivotCount + $directCount;
                
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                    'count' => $totalCount
                ];
            })
            ->filter(function($brand) {
                return $brand['count'] > 0; // Only show brands with products
            })
            ->sortBy('name')
            ->values();
        
        return view('frontend.shop', compact('selectedCategory', 'childCategories', 'parentCategories', 'breadcrumb', 'products', 'totalProducts', 'hasMoreProducts', 'currentPage', 'minPrice', 'maxPrice', 'availableSizes', 'brands'));
    }

    /**
     * Load more products via AJAX
     */
    public function loadMoreProducts(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $selectedCategory = null;
        if ($request->has('category') && $request->category) {
            $selectedCategory = Category::where('slug', $request->category)
                ->where(function($q) {
                    $q->where('is_active', true)->orWhereNull('is_active');
                })
                ->first();
        }
        
        // Get wishlist product IDs
        $customer = $request->user();
        $customerId = ($customer && $customer instanceof \App\Models\Customer) ? $customer->id : null;
        $sessionId = $request->input('session_id') 
                  ?? $request->query('session_id') 
                  ?? $request->header('X-Session-ID') 
                  ?? session()->getId();
        
        $wishlistProductIds = \App\Models\Wishlist::where(function($query) use ($customerId, $sessionId) {
            if ($customerId) {
                $query->where('customer_id', $customerId);
            } else {
                $query->where('session_id', $sessionId);
            }
        })->pluck('product_id')->toArray();
        
        // Build products query (same as shop method)
        $productsQuery = Product::with([
            'primaryImage',
            'images' => function($q) {
                $q->orderBy('sort_order')->orderBy('id')->limit(1);
            },
            'variants' => function($q) {
                $q->where('is_active', true)
                  ->orderBy('sort_order')
                  ->with(['images' => function($imgQ) {
                      $imgQ->orderBy('sort_order')->orderBy('id');
                  }]);
            }
        ])
        ->where('status', 'published');
        
        // Filter by category if selected
        if ($selectedCategory) {
            $categoryIds = [$selectedCategory->id];
            $categoryIds = array_merge($categoryIds, $selectedCategory->getDescendantIds());
            
            $productsQuery->where(function($query) use ($categoryIds) {
                $query->whereIn('category_id', $categoryIds)
                      ->orWhereHas('categories', function($q) use ($categoryIds) {
                          $q->whereIn('categories.id', $categoryIds);
                      });
            });
        }
        
        // Apply search filter if provided
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $productsQuery->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('slug', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Get total count
        $totalProducts = $productsQuery->count();
        
        // Get products for this page
        $products = $productsQuery->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($perPage)
            ->get()
            ->map(function($product) use ($wishlistProductIds) {
                // Same mapping logic as shop method
                $activeVariants = $product->variants->where('is_active', true);
                $prices = $activeVariants->pluck('price')->filter();
                $salePrices = $activeVariants->pluck('sale_price')->filter();
                
                $displayPrices = $activeVariants->map(function($variant) {
                    $price = $variant->price ?? 0;
                    $salePrice = $variant->sale_price;
                    return ($salePrice && $salePrice < $price) ? $salePrice : $price;
                })->filter();
                
                $minPrice = $prices->min() ?? 0;
                $maxPrice = $prices->max() ?? 0;
                $minDisplayPrice = $displayPrices->min() ?? $minPrice;
                $maxDisplayPrice = $displayPrices->max() ?? $maxPrice;
                $minSalePrice = $salePrices->min();
                $maxSalePrice = $salePrices->max();
                $hasSale = $minSalePrice && $minSalePrice < $minPrice;
                
                $imageUrl = $product->primaryImage 
                    ? asset('storage/' . $product->primaryImage->image_path)
                    : ($product->images->first() 
                        ? asset('storage/' . $product->images->first()->image_path)
                        : asset('frontend/images/product/1.jpg'));
                
                // Get color attributes
                $colorAttributes = collect();
                if ($product->category) {
                    $colorAttributes = $product->category->getAllProductAttributes()->where('type', 'color');
                }
                if ($colorAttributes->isEmpty()) {
                    $colorAttributes = ProductAttribute::where('type', 'color')
                        ->orWhere(function($q) {
                            $q->where('slug', 'color')->orWhere('name', 'Color');
                        })
                        ->get();
                }
                
                $sizeAttributes = collect();
                if ($product->category) {
                    $sizeAttributes = $product->category->getAllProductAttributes()->where('type', 'size');
                }
                if ($sizeAttributes->isEmpty()) {
                    $sizeAttributes = ProductAttribute::where('type', 'size')
                        ->orWhere(function($q) {
                            $q->where('slug', 'size')->orWhere('name', 'Size');
                        })
                        ->get();
                }
                
                $colorAttributeMap = $colorAttributes->keyBy('id');
                $colorAttributeNameMap = $colorAttributes->keyBy('name');
                $sizeAttributeMap = $sizeAttributes->keyBy('id');
                $sizeAttributeNameMap = $sizeAttributes->keyBy('name');
                
                $colorVariants = $activeVariants->filter(function($variant) use ($colorAttributeMap, $colorAttributeNameMap) {
                    if (!$variant->attributes) return false;
                    $attrs = is_string($variant->attributes) ? json_decode($variant->attributes, true) : $variant->attributes;
                    if (!is_array($attrs)) return false;
                    
                    foreach ($attrs as $key => $value) {
                        if (empty($value)) continue;
                        if (is_numeric($key) && isset($colorAttributeMap[$key])) return true;
                        if (isset($colorAttributeNameMap[$key]) || isset($colorAttributeNameMap[ucfirst($key)]) || strtolower($key) === 'color') return true;
                    }
                    return false;
                });
                
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'image_url' => $imageUrl,
                    'min_price' => $minPrice,
                    'max_price' => $maxPrice,
                    'min_display_price' => $minDisplayPrice,
                    'max_display_price' => $maxDisplayPrice,
                    'min_sale_price' => $minSalePrice,
                    'max_sale_price' => $maxSalePrice,
                    'has_sale' => $hasSale,
                    'is_new' => $product->created_at->isAfter(now()->subDays(30)),
                    'is_featured' => $product->featured,
                    'in_wishlist' => in_array($product->id, $wishlistProductIds),
                    'color_variants' => $colorVariants->map(function($variant) use ($colorAttributeMap, $colorAttributeNameMap, $sizeAttributeMap, $sizeAttributeNameMap, $imageUrl, $activeVariants) {
                        $attrs = is_string($variant->attributes) ? json_decode($variant->attributes, true) : ($variant->attributes ?? []);
                        $colorValue = null;
                        $colorAttributeId = null;
                        $attrs = is_array($attrs) ? $attrs : [];
                        
                        foreach ($attrs as $key => $value) {
                            if (empty($value)) continue;
                            if (is_numeric($key) && isset($colorAttributeMap[$key])) {
                                $colorValue = $value;
                                $colorAttributeId = (int)$key;
                            } elseif (isset($colorAttributeNameMap[$key]) || isset($colorAttributeNameMap[ucfirst($key)]) || strtolower($key) === 'color') {
                                $colorValue = $value;
                                $attribute = $colorAttributeNameMap[$key] ?? $colorAttributeNameMap[ucfirst($key)] ?? null;
                                $colorAttributeId = $attribute ? $attribute->id : null;
                            }
                        }
                        
                        $colorCode = '#ccc';
                        if ($colorAttributeId && $colorValue) {
                            $attributeValue = ProductAttributeValue::where('attribute_id', $colorAttributeId)
                                ->where('value', $colorValue)
                                ->first();
                            if (!$attributeValue) {
                                $attributeValue = ProductAttributeValue::where('attribute_id', $colorAttributeId)
                                    ->whereRaw('LOWER(value) = ?', [strtolower($colorValue)])
                                    ->first();
                            }
                            if ($attributeValue && $attributeValue->color_code) {
                                $colorCode = $attributeValue->color_code;
                            } else {
                                $colorCode = self::getColorCodeFromName($colorValue);
                            }
                        } elseif ($colorValue) {
                            $colorCode = self::getColorCodeFromName($colorValue);
                        }
                        
                        $variantImage = $imageUrl;
                        if ($variant->images && $variant->images->count() > 0) {
                            $primaryVariantImage = $variant->images->where('is_primary', true)->first();
                            if ($primaryVariantImage) {
                                $variantImage = asset('storage/' . $primaryVariantImage->image_path);
                            } else {
                                $firstVariantImage = $variant->images->first();
                                if ($firstVariantImage) {
                                    $variantImage = asset('storage/' . $firstVariantImage->image_path);
                                }
                            }
                        }
                        
                        $price = $variant->price ?? 0;
                        $salePrice = $variant->sale_price;
                        $hasSale = $salePrice && $salePrice < $price;
                        
                        return [
                            'id' => $variant->id,
                            'color' => $colorValue,
                            'color_code' => $colorCode,
                            'image' => $variantImage,
                            'price' => $price,
                            'sale_price' => $salePrice,
                            'has_sale' => $hasSale,
                            'display_price' => $hasSale ? $salePrice : $price,
                        ];
                    })->unique('color')->values()->take(4),
                ];
            });
        
        $hasMore = ($offset + $perPage) < $totalProducts;
        
        return response()->json([
            'success' => true,
            'products' => $products,
            'hasMore' => $hasMore,
            'total' => $totalProducts,
            'currentPage' => $page
        ]);
    }

    public function product(Request $request)
    {
        $slug = $request->get('product');
        
        if (!$slug) {
            abort(404, 'Product not found');
        }

        $product = Product::with([
            'primaryImage',
            'images' => function($q) {
                $q->orderBy('sort_order')->orderBy('id');
            },
            'category.parent',
            'variants' => function($q) {
                $q->where('is_active', true)
                  ->orderBy('sort_order')
                  ->with(['images' => function($imgQ) {
                      $imgQ->orderBy('sort_order')->orderBy('id');
                  }]);
            }
        ])
        ->where('slug', $slug)
        ->where('status', 'published')
        ->first();

        if (!$product) {
            abort(404, 'Product not found');
        }

        // Get active variants
        $activeVariants = $product->variants->where('is_active', true);
        
        // Get price range
        $prices = $activeVariants->pluck('price')->filter();
        $salePrices = $activeVariants->pluck('sale_price')->filter();
        
        $minPrice = $prices->min() ?? 0;
        $maxPrice = $prices->max() ?? 0;
        $minSalePrice = $salePrices->min();
        $maxSalePrice = $salePrices->max();
        
        $hasSale = $minSalePrice && $minSalePrice < $minPrice;
        
        // Get product images
        $productImages = $product->images->map(function($image) use ($product) {
            return [
                'url' => asset('storage/' . $image->image_path),
                'alt' => $image->alt_text ?? $product->name,
            ];
        });
        
        // If no product images, use primary image or placeholder
        if ($productImages->isEmpty()) {
            if ($product->primaryImage) {
                $productImages->push([
                    'url' => asset('storage/' . $product->primaryImage->image_path),
                    'alt' => $product->name,
                ]);
            } else {
                $productImages->push([
                    'url' => asset('frontend/images/product/1.jpg'),
                    'alt' => $product->name,
                ]);
            }
        }
        
        // Get color attribute to find color codes
        $colorAttribute = null;
        if ($product->category) {
            $colorAttribute = $product->category->getAllProductAttributes()
                ->where('type', 'color')
                ->first();
        }
        
        if (!$colorAttribute) {
            $colorAttribute = ProductAttribute::where('type', 'color')
                ->orWhere(function($q) {
                    $q->where('slug', 'color')->orWhere('name', 'Color');
                })
                ->first();
        }
        
        // Get size attribute
        $sizeAttribute = null;
        if ($product->category) {
            $sizeAttribute = $product->category->getAllProductAttributes()
                ->where('type', 'size')
                ->first();
        }
        
        if (!$sizeAttribute) {
            $sizeAttribute = ProductAttribute::where('type', 'size')
                ->orWhere(function($q) {
                    $q->where('slug', 'size')->orWhere('name', 'Size');
                })
                ->first();
        }
        
        // Extract colors and sizes from variants with color codes
        $colors = [];
        $sizes = [];
        $colorVariantsMap = [];
        
        foreach ($activeVariants as $variant) {
            $attrs = is_string($variant->attributes) 
                ? json_decode($variant->attributes, true) 
                : ($variant->attributes ?? []);
            
            $colorValue = null;
            $sizeValue = null;
            
            // Get color and size values
            foreach ($attrs as $key => $value) {
                if (empty($value)) {
                    continue;
                }
                
                // Check for color by attribute ID
                if (is_numeric($key) && $colorAttribute && $key == $colorAttribute->id) {
                    $colorValue = $value;
                } 
                // Check for color by attribute name
                elseif (strtolower($key) === 'color' || (isset($colorAttribute) && $key === $colorAttribute->name)) {
                    $colorValue = $value;
                }
                
                // Check for size by attribute ID
                if (is_numeric($key) && $sizeAttribute && $key == $sizeAttribute->id) {
                    $sizeValue = $value;
                } 
                // Check for size by attribute name
                elseif (strtolower($key) === 'size' || (isset($sizeAttribute) && $key === $sizeAttribute->name)) {
                    $sizeValue = $value;
                }
            }
            
            // Get color code
            $colorCode = '#ccc';
            if ($colorAttribute && $colorValue) {
                $attributeValue = ProductAttributeValue::where('attribute_id', $colorAttribute->id)
                    ->whereRaw('LOWER(value) = ?', [strtolower($colorValue)])
                    ->first();
                if ($attributeValue && $attributeValue->color_code) {
                    $colorCode = $attributeValue->color_code;
                } else {
                    $colorCode = self::getColorCodeFromName($colorValue);
                }
            }
            
            // Get variant images
            $variantImages = [];
            if ($variant->images && $variant->images->count() > 0) {
                $variantImages = $variant->images->map(function($image) {
                    return [
                        'url' => asset('storage/' . $image->image_path),
                        'alt' => $image->alt_text ?? '',
                    ];
                })->toArray();
            }
            
            $imageUrl = $productImages->first()['url'] ?? asset('frontend/images/product/1.jpg');
            $variantImage = $imageUrl;
            if ($variant->images && $variant->images->count() > 0) {
                $primaryVariantImage = $variant->images->where('is_primary', true)->first();
                if ($primaryVariantImage) {
                    $variantImage = asset('storage/' . $primaryVariantImage->image_path);
                } else {
                    $firstVariantImage = $variant->images->first();
                    if ($firstVariantImage) {
                        $variantImage = asset('storage/' . $firstVariantImage->image_path);
                    }
                }
            }
            
            $price = $variant->price ?? 0;
            $salePrice = $variant->sale_price;
            $hasVariantSale = $salePrice && $salePrice < $price;
            
            if ($colorValue && !isset($colorVariantsMap[$colorValue])) {
                $colors[] = $colorValue;
                $colorVariantsMap[$colorValue] = [
                    'color' => $colorValue,
                    'color_code' => $colorCode,
                    'image' => $variantImage,
                    'price' => $price,
                    'sale_price' => $salePrice,
                    'has_sale' => $hasVariantSale,
                    'display_price' => $hasVariantSale ? $salePrice : $price,
                ];
            }
            
            if ($sizeValue && !in_array($sizeValue, $sizes)) {
                $sizes[] = $sizeValue;
            }
        }
        
        // Check stock status
        $inStock = $activeVariants->filter(function($variant) {
            if (!$variant->manage_stock) {
                return $variant->stock_status === 'in_stock';
            }
            return $variant->stock_quantity > 0;
        })->count() > 0;
        
        // Get wishlist status
        $customer = $request->user();
        $customerId = ($customer && $customer instanceof \App\Models\Customer) ? $customer->id : null;
        $sessionId = $request->input('session_id') 
                  ?? $request->query('session_id') 
                  ?? $request->header('X-Session-ID') 
                  ?? session()->getId();
        
        $inWishlist = \App\Models\Wishlist::where(function($query) use ($customerId, $sessionId) {
            if ($customerId) {
                $query->where('customer_id', $customerId);
            } else {
                $query->where('session_id', $sessionId);
            }
        })->where('product_id', $product->id)->exists();
        
        // Get first variant SKU for display
        $firstVariant = $activeVariants->first();
        $displaySku = $firstVariant ? $firstVariant->sku : '';
        
        // Get similar products (from same category, limit 6)
        $similarProducts = Product::with([
            'primaryImage',
            'images' => function($q) {
                $q->orderBy('sort_order')->orderBy('id')->limit(1);
            },
            'variants' => function($q) {
                $q->where('is_active', true)
                  ->orderBy('sort_order')
                  ->with(['images' => function($imgQ) {
                      $imgQ->orderBy('sort_order')->orderBy('id');
                  }]);
            }
        ])
        ->where('status', 'published')
        ->where('id', '!=', $product->id)
        ->where(function($q) use ($product) {
            if ($product->category_id) {
                $q->where('category_id', $product->category_id);
            }
        })
        ->limit(6)
        ->get()
        ->map(function($similarProduct) {
            $activeVariants = $similarProduct->variants->where('is_active', true);
            $prices = $activeVariants->pluck('price')->filter();
            $salePrices = $activeVariants->pluck('sale_price')->filter();
            
            $minPrice = $prices->min() ?? 0;
            $maxPrice = $prices->max() ?? 0;
            $minSalePrice = $salePrices->min();
            $hasSale = $minSalePrice && $minSalePrice < $minPrice;
            
            $imageUrl = asset('frontend/images/product/1.jpg');
            if ($similarProduct->primaryImage) {
                $imageUrl = asset('storage/' . $similarProduct->primaryImage->image_path);
            } elseif ($similarProduct->images->count() > 0) {
                $imageUrl = asset('storage/' . $similarProduct->images->first()->image_path);
            }
            
            return [
                'id' => $similarProduct->id,
                'name' => $similarProduct->name,
                'slug' => $similarProduct->slug,
                'image_url' => $imageUrl,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'min_sale_price' => $minSalePrice,
                'has_sale' => $hasSale,
                'display_price' => $minPrice != $maxPrice 
                    ? '$' . number_format($minPrice, 0) . ' - $' . number_format($maxPrice, 0)
                    : '$' . number_format($minPrice, 0),
            ];
        });
        
        return view('frontend.product', compact(
            'product',
            'productImages',
            'colors',
            'sizes',
            'colorVariantsMap',
            'minPrice',
            'maxPrice',
            'minSalePrice',
            'maxSalePrice',
            'hasSale',
            'inStock',
            'inWishlist',
            'displaySku',
            'activeVariants',
            'similarProducts'
        ));
    }

    /**
     * Get product details for quick view modal
     */
    public function getProductQuickView(Request $request)
    {
        $slug = $request->get('slug');
        
        if (!$slug) {
            return response()->json([
                'success' => false,
                'message' => 'Product slug is required'
            ], 400);
        }

        $product = Product::with([
            'primaryImage',
            'images' => function($q) {
                $q->orderBy('sort_order')->orderBy('id');
            },
            'category.parent',
            'variants' => function($q) {
                $q->where('is_active', true)
                  ->orderBy('sort_order')
                  ->with(['images' => function($imgQ) {
                      $imgQ->orderBy('sort_order')->orderBy('id');
                  }]);
            }
        ])
        ->where('slug', $slug)
        ->where('status', 'published')
        ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        // Get active variants
        $activeVariants = $product->variants->where('is_active', true);
        
        // Get price range
        $prices = $activeVariants->pluck('price')->filter();
        $salePrices = $activeVariants->pluck('sale_price')->filter();
        
        $minPrice = $prices->min() ?? 0;
        $maxPrice = $prices->max() ?? 0;
        $minSalePrice = $salePrices->min();
        $maxSalePrice = $salePrices->max();
        
        $hasSale = $minSalePrice && $minSalePrice < $minPrice;
        
        // Get product images
        $productImages = $product->images->map(function($image) use ($product) {
            return [
                'url' => asset('storage/' . $image->image_path),
                'alt' => $image->alt_text ?? $product->name,
            ];
        });
        
        // If no product images, use primary image or placeholder
        if ($productImages->isEmpty()) {
            if ($product->primaryImage) {
                $productImages->push([
                    'url' => asset('storage/' . $product->primaryImage->image_path),
                    'alt' => $product->name,
                ]);
            } else {
                $productImages->push([
                    'url' => asset('frontend/images/product/1.jpg'),
                    'alt' => $product->name,
                ]);
            }
        }
        
        // Get color attribute to find color codes
        $colorAttribute = null;
        if ($product->category) {
            $colorAttribute = $product->category->getAllProductAttributes()
                ->where('type', 'color')
                ->first();
        }
        
        if (!$colorAttribute) {
            $colorAttribute = ProductAttribute::where('type', 'color')
                ->orWhere(function($q) {
                    $q->where('slug', 'color')->orWhere('name', 'Color');
                })
                ->first();
        }
        
        // Get size attribute
        $sizeAttribute = null;
        if ($product->category) {
            $sizeAttribute = $product->category->getAllProductAttributes()
                ->where('type', 'size')
                ->first();
        }
        
        if (!$sizeAttribute) {
            $sizeAttribute = ProductAttribute::where('type', 'size')
                ->orWhere(function($q) {
                    $q->where('slug', 'size')->orWhere('name', 'Size');
                })
                ->first();
        }
        
        // Create maps for quick lookup
        $sizeAttributeMap = collect();
        if ($sizeAttribute) {
            $sizeAttributeMap = collect([$sizeAttribute->id => $sizeAttribute]);
        }
        $sizeAttributeNameMap = collect();
        if ($sizeAttribute) {
            $sizeAttributeNameMap = collect([$sizeAttribute->name => $sizeAttribute]);
        }
        
        // Extract colors and sizes from variants with color codes
        $colors = [];
        $sizes = [];
        $colorVariantsMap = [];
        
        foreach ($activeVariants as $variant) {
            $attrs = is_string($variant->attributes) 
                ? json_decode($variant->attributes, true) 
                : ($variant->attributes ?? []);
            
            $colorValue = null;
            $sizeValue = null;
            
            // Get color and size values
            foreach ($attrs as $key => $value) {
                if (empty($value)) {
                    continue;
                }
                
                // Check for color by attribute ID
                if (is_numeric($key) && $colorAttribute && $key == $colorAttribute->id) {
                    $colorValue = $value;
                } 
                // Check for color by attribute name
                elseif (strtolower($key) === 'color' || (isset($colorAttribute) && $key === $colorAttribute->name)) {
                    $colorValue = $value;
                }
                
                // Check for size by attribute ID
                if (is_numeric($key) && $sizeAttribute && $key == $sizeAttribute->id) {
                    $sizeValue = $value;
                } 
                // Check for size by attribute name
                elseif (strtolower($key) === 'size' || (isset($sizeAttribute) && $key === $sizeAttribute->name)) {
                    $sizeValue = $value;
                }
            }
            
            // Get color code
            $colorCode = '#ccc';
            if ($colorAttribute && $colorValue) {
                $attributeValue = ProductAttributeValue::where('attribute_id', $colorAttribute->id)
                    ->whereRaw('LOWER(value) = ?', [strtolower($colorValue)])
                    ->first();
                if ($attributeValue && $attributeValue->color_code) {
                    $colorCode = $attributeValue->color_code;
                } else {
                    $colorCode = self::getColorCodeFromName($colorValue);
                }
            }
            
            // Get variant images
            $variantImages = [];
            if ($variant->images && $variant->images->count() > 0) {
                $variantImages = $variant->images->map(function($image) {
                    return [
                        'url' => asset('storage/' . $image->image_path),
                        'alt' => $image->alt_text ?? '',
                    ];
                })->toArray();
            }
            
            if ($colorValue && !in_array($colorValue, $colors)) {
                $colors[] = $colorValue;
                $colorVariantsMap[$colorValue] = [
                    'color' => $colorValue,
                    'color_code' => $colorCode,
                    'images' => $variantImages,
                ];
            }
            
            if ($sizeValue && !in_array($sizeValue, $sizes)) {
                $sizes[] = $sizeValue;
            }
        }
        
        // Check stock status
        $inStock = $activeVariants->filter(function($variant) {
            if (!$variant->manage_stock) {
                return $variant->stock_status === 'in_stock';
            }
            return $variant->stock_quantity > 0;
        })->count() > 0;
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description ?? $product->short_description ?? '',
                'category' => $product->category ? $product->category->name : '',
                'parent_category' => $product->category && $product->category->parent ? $product->category->parent->name : null,
                'images' => $productImages,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'min_sale_price' => $minSalePrice,
                'max_sale_price' => $maxSalePrice,
                'has_sale' => $hasSale,
                'price_display' => $minPrice != $maxPrice 
                    ? '$' . number_format($minPrice, 0) . ' - $' . number_format($maxPrice, 0)
                    : '$' . number_format($minPrice, 0),
                'colors' => array_values($colors),
                'sizes' => array_values($sizes),
                'color_variants' => $colorVariantsMap,
                'in_stock' => $inStock,
                'variants' => $activeVariants->map(function($variant) use ($colorAttribute, $sizeAttribute) {
                    $attrs = is_string($variant->attributes) 
                        ? json_decode($variant->attributes, true) 
                        : ($variant->attributes ?? []);
                    
                    $colorValue = null;
                    $sizeValue = null;
                    
                    foreach ($attrs as $key => $value) {
                        if (empty($value)) {
                            continue;
                        }
                        
                        // Check for color by attribute ID
                        if (is_numeric($key) && $colorAttribute && $key == $colorAttribute->id) {
                            $colorValue = $value;
                        } 
                        // Check for color by attribute name
                        elseif (strtolower($key) === 'color' || (isset($colorAttribute) && $key === $colorAttribute->name)) {
                            $colorValue = $value;
                        }
                        
                        // Check for size by attribute ID
                        if (is_numeric($key) && $sizeAttribute && $key == $sizeAttribute->id) {
                            $sizeValue = $value;
                        } 
                        // Check for size by attribute name
                        elseif (strtolower($key) === 'size' || (isset($sizeAttribute) && $key === $sizeAttribute->name)) {
                            $sizeValue = $value;
                        }
                    }
                    
                    // Get variant images
                    $variantImages = [];
                    if ($variant->images && $variant->images->count() > 0) {
                        $variantImages = $variant->images->map(function($image) {
                            return [
                                'url' => asset('storage/' . $image->image_path),
                                'alt' => $image->alt_text ?? '',
                            ];
                        })->toArray();
                    }
                    
                    return [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'price' => $variant->price,
                        'sale_price' => $variant->sale_price,
                        'color' => $colorValue,
                        'size' => $sizeValue,
                        'images' => $variantImages,
                        'stock_quantity' => $variant->stock_quantity,
                        'stock_status' => $variant->stock_status,
                        'manage_stock' => $variant->manage_stock,
                        'is_in_stock' => $variant->manage_stock ? $variant->stock_quantity > 0 : $variant->stock_status === 'in_stock',
                    ];
                }),
            ]
        ]);
    }

    public function aboutUs()
    {
        return view('frontend.about-us');
    }

    public function contact()
    {
        return view('frontend.contact');
    }

    public function privacy()
    {
        return view('frontend.privacy');
    }

    public function faq()
    {
        return view('frontend.faq');
    }

    public function myOrders()
    {
        return view('frontend.my-orders');
    }

    public function wishlist(Request $request)
    {
        // Get session ID or customer ID (similar to API controller)
        $customer = $request->user();
        $customerId = ($customer && $customer instanceof \App\Models\Customer) ? $customer->id : null;
        
        // Get session_id from request (for guest users using localStorage)
        $sessionId = $request->input('session_id') 
                  ?? $request->query('session_id') 
                  ?? $request->header('X-Session-ID') 
                  ?? session()->getId();
        
        // Get wishlist items
        $wishlistQuery = \App\Models\Wishlist::with([
            'product.primaryImage',
            'product.images' => function($q) {
                $q->orderBy('sort_order')->orderBy('id')->limit(1);
            },
            'product.variants' => function($q) {
                $q->where('is_active', true)
                  ->orderBy('sort_order')
                  ->with(['images' => function($imgQ) {
                      $imgQ->orderBy('sort_order')->orderBy('id');
                  }]);
            }
        ]);
        
        if ($customerId) {
            $wishlistQuery->where('customer_id', $customerId);
        } else {
            $wishlistQuery->where('session_id', $sessionId);
        }
        
        $wishlistItems = $wishlistQuery->get();
        
        // Format wishlist items similar to new arrivals
        $wishlistProducts = $wishlistItems->map(function($wishlist) {
            $product = $wishlist->product;
            $activeVariants = $product->variants->where('is_active', true);
            
            // Get price range
            $prices = $activeVariants->pluck('price')->filter();
            $salePrices = $activeVariants->pluck('sale_price')->filter();
            
            $minPrice = $prices->min() ?? 0;
            $maxPrice = $prices->max() ?? 0;
            $minSalePrice = $salePrices->min();
            $maxSalePrice = $salePrices->max();
            
            $hasSale = $minSalePrice && $minSalePrice < $minPrice;
            
            // Get product image
            $imageUrl = $product->primaryImage 
                ? asset('storage/' . $product->primaryImage->image_path)
                : ($product->images->first() 
                    ? asset('storage/' . $product->images->first()->image_path)
                    : asset('frontend/images/product/1.jpg'));
            
            // Get color variants (similar to index method)
            $colorAttributes = collect();
            if ($product->category) {
                $colorAttributes = $product->category->getAllProductAttributes()
                    ->where('type', 'color');
            }
            
            if ($colorAttributes->isEmpty()) {
                $colorAttributes = ProductAttribute::where('type', 'color')
                    ->orWhere(function($q) {
                        $q->where('slug', 'color')->orWhere('name', 'Color');
                    })
                    ->get();
            }
            
            $colorAttributeMap = $colorAttributes->keyBy('id');
            $colorAttributeNameMap = $colorAttributes->keyBy('name');
            
            $colorVariants = $activeVariants->filter(function($variant) use ($colorAttributeMap, $colorAttributeNameMap) {
                if (!$variant->attributes) return false;
                $attrs = is_string($variant->attributes) 
                    ? json_decode($variant->attributes, true) 
                    : $variant->attributes;
                if (!is_array($attrs)) return false;
                
                foreach ($attrs as $key => $value) {
                    if (empty($value)) continue;
                    if (is_numeric($key) && isset($colorAttributeMap[$key])) return true;
                    if (isset($colorAttributeNameMap[$key]) || 
                        isset($colorAttributeNameMap[ucfirst($key)]) ||
                        strtolower($key) === 'color') return true;
                }
                return false;
            });
            
            // Format color variants (simplified version)
            $formattedColorVariants = $colorVariants->map(function($variant) use ($colorAttributeMap, $colorAttributeNameMap, $imageUrl) {
                $attrs = is_string($variant->attributes) 
                    ? json_decode($variant->attributes, true) 
                    : ($variant->attributes ?? []);
                
                $colorValue = null;
                $colorAttributeId = null;
                $attrs = is_array($attrs) ? $attrs : [];
                
                foreach ($attrs as $key => $value) {
                    if (empty($value)) continue;
                    if (is_numeric($key) && isset($colorAttributeMap[$key])) {
                        $colorValue = $value;
                        $colorAttributeId = (int)$key;
                        break;
                    }
                    $attribute = $colorAttributeNameMap[$key] ?? 
                                $colorAttributeNameMap[ucfirst($key)] ?? 
                                null;
                    if ($attribute) {
                        $colorValue = $value;
                        $colorAttributeId = $attribute->id;
                        break;
                    }
                    if (strtolower($key) === 'color') {
                        $colorValue = $value;
                        $attribute = $colorAttributeNameMap->first();
                        $colorAttributeId = $attribute ? $attribute->id : null;
                        break;
                    }
                }
                
                $colorCode = '#ccc';
                if ($colorAttributeId && $colorValue) {
                    $attributeValue = ProductAttributeValue::where('attribute_id', $colorAttributeId)
                        ->where('value', $colorValue)
                        ->first();
                    if (!$attributeValue) {
                        $attributeValue = ProductAttributeValue::where('attribute_id', $colorAttributeId)
                            ->whereRaw('LOWER(value) = ?', [strtolower($colorValue)])
                            ->first();
                    }
                    if ($attributeValue && $attributeValue->color_code) {
                        $colorCode = $attributeValue->color_code;
                    } else {
                        $colorCode = self::getColorCodeFromName($colorValue);
                    }
                } elseif ($colorValue) {
                    $colorCode = self::getColorCodeFromName($colorValue);
                }
                
                $variantImage = $imageUrl;
                if ($variant->images && $variant->images->count() > 0) {
                    $primaryVariantImage = $variant->images->where('is_primary', true)->first();
                    if ($primaryVariantImage) {
                        $variantImage = asset('storage/' . $primaryVariantImage->image_path);
                    } else {
                        $firstVariantImage = $variant->images->first();
                        if ($firstVariantImage) {
                            $variantImage = asset('storage/' . $firstVariantImage->image_path);
                        }
                    }
                }
                
                $price = $variant->price ?? 0;
                $salePrice = $variant->sale_price;
                $hasSale = $salePrice && $salePrice < $price;
                
                return [
                    'id' => $variant->id,
                    'color' => $colorValue,
                    'color_code' => $colorCode,
                    'image' => $variantImage,
                    'price' => $price,
                    'sale_price' => $salePrice,
                    'has_sale' => $hasSale,
                    'display_price' => $hasSale ? $salePrice : $price,
                ];
            })->unique('color')->values()->take(4);
            
            return [
                'wishlist_id' => $wishlist->id,
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'image_url' => $imageUrl,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'min_sale_price' => $minSalePrice,
                'max_sale_price' => $maxSalePrice,
                'has_sale' => $hasSale,
                'price_display' => $minPrice != $maxPrice 
                    ? '$' . number_format($minPrice, 0) . ' - $' . number_format($maxPrice, 0)
                    : '$' . number_format($minPrice, 0),
                'is_new' => $product->created_at->isAfter(now()->subDays(30)),
                'is_featured' => $product->featured,
                'color_variants' => $formattedColorVariants,
            ];
        });
        
        return view('frontend.wishlist', compact('wishlistProducts'));
    }

    public function profileInfo()
    {
        return view('frontend.profile-info');
    }

    public function addresses()
    {
        return view('frontend.addresses');
    }

    public function paymentMethode()
    {
        return view('frontend.payment-methode');
    }

    public function shopingCart()
    {
        return view('frontend.shoping-cart');
    }

    public function checkout()
    {
        return view('frontend.checkout');
    }

    public function completeOrder()
    {
        return view('frontend.complete-order');
    }

    /**
     * Get color code from common color names (fallback when not in database)
     */
    private static function getColorCodeFromName($colorName)
    {
        if (!$colorName) {
            return '#ccc';
        }

        $colorMap = [
            'white' => '#FFFFFF',
            'black' => '#000000',
            'grey' => '#808080',
            'gray' => '#808080',
            'navy' => '#000080',
            'blue' => '#0000FF',
            'red' => '#FF0000',
            'green' => '#008000',
            'yellow' => '#FFFF00',
            'orange' => '#FFA500',
            'purple' => '#800080',
            'pink' => '#FFC0CB',
            'brown' => '#A52A2A',
            'beige' => '#F5F5DC',
            'olive' => '#808000',
            'tan' => '#D2B48C',
            'maroon' => '#800000',
            'teal' => '#008080',
            'cyan' => '#00FFFF',
            'lime' => '#00FF00',
            'magenta' => '#FF00FF',
            'silver' => '#C0C0C0',
            'gold' => '#FFD700',
        ];

        $colorLower = strtolower(trim($colorName));
        return $colorMap[$colorLower] ?? '#ccc';
    }
}
