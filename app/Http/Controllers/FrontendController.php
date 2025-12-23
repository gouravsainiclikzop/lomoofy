<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\FieldManagement;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;

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
                $priceDisplay = '₹' . number_format($minSalePrice, 0);
                if ($maxSalePrice && $minSalePrice != $maxSalePrice) {
                    $priceDisplay .= ' - ₹' . number_format($maxSalePrice, 0);
                }
            } else {
                if ($minPrice != $maxPrice) {
                    $priceDisplay = '₹' . number_format($minPrice, 0) . ' - ₹' . number_format($maxPrice, 0);
                } else {
                    $priceDisplay = '₹' . number_format($minPrice, 0);
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
        
        // Get best seller products based on actual sales (order items)
        // Count total quantity sold per product from completed orders
        $bestSellerProductIds = \App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', ['delivered', 'shipped', 'processing']) // Only count successful orders
            ->where('orders.payment_status', 'paid') // Only count paid orders
            ->select('order_items.product_id', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('order_items.product_id')
            ->orderBy('total_sold', 'desc')
            ->limit(8)
            ->pluck('product_id')
            ->toArray();
        
        // Build base query with relationships
        $bestSellersQuery = Product::with([
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
        
        // If we have best sellers from orders, use them; otherwise fall back to featured products
        if (count($bestSellerProductIds) > 0) {
            // Use actual sales data - maintain order by sales quantity
            $bestSellersQuery->whereIn('id', $bestSellerProductIds)
                ->orderByRaw('FIELD(id, ' . implode(',', array_map('intval', $bestSellerProductIds)) . ')');
        } else {
            // Fallback to featured products if no sales data yet
            $bestSellersQuery->where('featured', true)
                ->orderBy('created_at', 'desc');
        }
        
        $bestSellers = $bestSellersQuery->limit(8)->get()
        ->map(function($product) use ($wishlistProductIds) {
            // Get price range from variants
            $activeVariants = $product->variants->where('is_active', true);
            $prices = $activeVariants->pluck('price')->filter();
            $salePrices = $activeVariants->pluck('sale_price')->filter();
            
            // Calculate display prices
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
            
            // Determine if product is on sale
            $hasSale = $minSalePrice && $minSalePrice < $minPrice;
            
            // Get product image
            $imageUrl = $product->primaryImage 
                ? asset('storage/' . $product->primaryImage->image_path)
                : ($product->images->first() 
                    ? asset('storage/' . $product->images->first()->image_path)
                    : asset('frontend/images/product/1.jpg'));
            
            // Format price display
            $priceDisplay = '';
            if ($hasSale && $minSalePrice) {
                $priceDisplay = '₹' . number_format($minSalePrice, 0);
                if ($maxSalePrice && $minSalePrice != $maxSalePrice) {
                    $priceDisplay .= ' - ₹' . number_format($maxSalePrice, 0);
                }
            } else {
                if ($minPrice != $maxPrice) {
                    $priceDisplay = '₹' . number_format($minPrice, 0) . ' - ₹' . number_format($maxPrice, 0);
                } else {
                    $priceDisplay = '₹' . number_format($minPrice, 0);
                }
            }
            
            // Determine badge type
            $badge = null;
            if ($hasSale) {
                $badge = 'sale';
            } elseif ($product->created_at->isAfter(now()->subDays(30))) {
                $badge = 'new';
            } elseif ($product->featured) {
                $badge = 'hot';
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
                'badge' => $badge,
                'in_wishlist' => in_array($product->id, $wishlistProductIds),
            ];
        });
        
        // Get active collections for homepage
        // Get recently viewed products from session
        $recentlyViewedProductIds = session('recently_viewed_products', []);
        
        // Get recently viewed products
        $recentlyViewed = collect([]);
        if (count($recentlyViewedProductIds) > 0) {
            $recentlyViewed = Product::with([
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
            ->whereIn('id', $recentlyViewedProductIds)
            ->orderByRaw('FIELD(id, ' . implode(',', array_map('intval', $recentlyViewedProductIds)) . ')')
            ->limit(8)
            ->get()
            ->map(function($product) use ($wishlistProductIds) {
                // Get price range from variants
                $activeVariants = $product->variants->where('is_active', true);
                $prices = $activeVariants->pluck('price')->filter();
                $salePrices = $activeVariants->pluck('sale_price')->filter();
                
                // Calculate display prices
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
                
                // Determine if product is on sale
                $hasSale = $minSalePrice && $minSalePrice < $minPrice;
                
                // Get product image
                $imageUrl = $product->primaryImage 
                    ? asset('storage/' . $product->primaryImage->image_path)
                    : ($product->images->first() 
                        ? asset('storage/' . $product->images->first()->image_path)
                        : asset('frontend/images/product/1.jpg'));
                
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
                
                // Determine badge type
                $badge = null;
                if ($hasSale) {
                    $badge = 'sale';
                } elseif ($product->created_at->isAfter(now()->subDays(30))) {
                    $badge = 'new';
                } elseif ($product->featured) {
                    $badge = 'hot';
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
                    'badge' => $badge,
                    'in_wishlist' => in_array($product->id, $wishlistProductIds),
                ];
            });
        }
        
        $collections = \App\Models\FeaturedCategoryStyle::where('is_active', true)
            ->with('category')
            ->orderBy('sort_order')
            ->get();
        
        // Get Our Collection data for the banner section
        $ourCollection = \App\Models\OurCollection::with('category')->first();
        
        // Get testimonials for the testimonials section
        $testimonials = \App\Models\Testimonial::orderBy('sort_order')
            ->get()
            ->map(function($testimonial) {
                return [
                    'id' => $testimonial->id,
                    'name' => $testimonial->name,
                    'title' => $testimonial->title,
                    'description' => $testimonial->description,
                    'image' => $testimonial->image ? asset('storage/' . $testimonial->image) : asset('frontend/images/team-1.jpg'),
                ];
            });
        
        // Get home sliders for the hero banner section
        $homeSliders = \App\Models\HomeSlider::with('category')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function($slider) {
                return [
                    'id' => $slider->id,
                    'image' => $slider->image ? asset('storage/' . $slider->image) : asset('frontend/images/banner-2.png'),
                    'tagline' => $slider->tagline,
                    'title' => $slider->title,
                    'category' => $slider->category ? [
                        'id' => $slider->category->id,
                        'slug' => $slider->category->slug,
                        'name' => $slider->category->name,
                    ] : null,
                ];
            });
        
        return view('frontend.index', compact('parentCategories', 'newArrivals', 'bestSellers', 'recentlyViewed', 'collections', 'ourCollection', 'testimonials', 'homeSliders'));
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
        
        // Get brands with product counts (using brand_id only, excluding "other" brand)
        $brands = \App\Models\Brand::where('is_active', true)
            ->where('slug', '!=', 'other')
            ->whereHas('products', function($query) {
                $query->where('status', 'published');
            })
            ->get()
            ->map(function($brand) {
                $count = Product::where('brand_id', $brand->id)
                    ->where('status', 'published')
                    ->count();
                
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                    'count' => $count
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
            'categories' => function($q) {
                $q->orderBy('product_categories.is_primary', 'desc');
            },
            'categories.parent',
            'brand' => function($q) {
                $q->active();
            },
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
        
        // Get primary category or first category from pivot table
        $primaryCategory = $product->categories->where('pivot.is_primary', true)->first();
        if (!$primaryCategory) {
            $primaryCategory = $product->categories->first();
        }
        // Fallback to category_id if no categories in pivot table
        if (!$primaryCategory && $product->category_id) {
            $primaryCategory = $product->category;
            if ($primaryCategory) {
                $primaryCategory->load('parent');
            }
        }
        
        // Get brand from brand_id
        $primaryBrand = $product->brand()->active()->first();
        
        // Track recently viewed products in session
        $recentlyViewed = session('recently_viewed_products', []);
        // Remove product if already exists (to move it to front)
        $recentlyViewed = array_values(array_filter($recentlyViewed, function($id) use ($product) {
            return $id != $product->id;
        }));
        // Add current product to the beginning
        array_unshift($recentlyViewed, $product->id);
        // Keep only last 8 products
        $recentlyViewed = array_slice($recentlyViewed, 0, 8);
        session(['recently_viewed_products' => $recentlyViewed]);
        
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
        
        // Get all variant attributes dynamically from category or product
        $allVariantAttributes = collect();
        if ($primaryCategory) {
            $allVariantAttributes = $primaryCategory->getAllProductAttributes()
                ->filter(function($attr) {
                    return $attr->is_visible !== false && $attr->is_visible !== 0 && $attr->is_visible !== '0';
                });
        }
        
        // If no attributes from category, get all visible attributes
        if ($allVariantAttributes->isEmpty()) {
            $allVariantAttributes = ProductAttribute::visible()->ordered()->get();
        }
        
        // Create attribute maps for quick lookup
        $attributeMap = $allVariantAttributes->keyBy('id');
        $attributeNameMap = $allVariantAttributes->keyBy('name');
        $attributeSlugMap = $allVariantAttributes->keyBy('slug');
        
        // Extract all attribute values from variants
        $attributeValues = []; // Structure: ['attribute_id' => ['value1', 'value2', ...]]
        $attributeVariantsMap = []; // Structure: ['attribute_id' => ['value' => variant_data]]
        $colorAttribute = null;
        $colorVariantsMap = [];
        $colors = [];
        $sizes = [];
        
        foreach ($activeVariants as $variant) {
            $attrs = is_string($variant->attributes) 
                ? json_decode($variant->attributes, true) 
                : ($variant->attributes ?? []);
            
            $colorValue = null;
            $sizeValue = null;
            
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
            
            // Extract all attribute values from this variant
            foreach ($attrs as $key => $value) {
                if (empty($value)) {
                    continue;
                }
                
                $attribute = null;
                $attributeId = null;
                
                // Find attribute by ID, name, or slug
                if (is_numeric($key)) {
                    $attribute = $attributeMap->get($key);
                    $attributeId = $key;
                } elseif ($attributeNameMap->has($key)) {
                    $attribute = $attributeNameMap->get($key);
                    $attributeId = $attribute->id;
                } elseif ($attributeSlugMap->has($key)) {
                    $attribute = $attributeSlugMap->get($key);
                    $attributeId = $attribute->id;
                }
                
                if ($attribute) {
                    // Initialize arrays if needed
                    if (!isset($attributeValues[$attributeId])) {
                        $attributeValues[$attributeId] = [];
                    }
                    if (!isset($attributeVariantsMap[$attributeId])) {
                        $attributeVariantsMap[$attributeId] = [];
                    }
                    
                    // Add unique value
                    if (!in_array($value, $attributeValues[$attributeId])) {
                        $attributeValues[$attributeId][] = $value;
                    }
                    
                    // Store variant data for this attribute value
                    if (!isset($attributeVariantsMap[$attributeId][$value])) {
                        $attributeVariantsMap[$attributeId][$value] = [
                            'value' => $value,
                            'images' => $variantImages,
                        ];
                        
                        // If it's a color attribute, also get color code
                        if ($attribute->type === 'color') {
                            $colorAttribute = $attribute;
                            $attributeValue = ProductAttributeValue::where('attribute_id', $attribute->id)
                                ->whereRaw('LOWER(value) = ?', [strtolower($value)])
                                ->first();
                            $colorCode = $attributeValue && $attributeValue->color_code 
                                ? $attributeValue->color_code 
                                : self::getColorCodeFromName($value);
                            
                            $attributeVariantsMap[$attributeId][$value]['color_code'] = $colorCode;
                            
                            // Also populate legacy color arrays for backward compatibility
                            if (!isset($colorVariantsMap[$value])) {
                                $colors[] = $value;
                                $colorVariantsMap[$value] = [
                                    'color' => $value,
                                    'color_code' => $colorCode,
                                    'image' => $variantImage,
                                    'images' => !empty($variantImages) ? $variantImages : [],
                                    'price' => $price,
                                    'sale_price' => $salePrice,
                                    'has_sale' => $hasVariantSale,
                                    'display_price' => $hasVariantSale ? $salePrice : $price,
                                ];
                            }
                        }
                        
                        // Legacy size support
                        if (strtolower($attribute->name) === 'size' || strtolower($attribute->slug) === 'size') {
                            if (!in_array($value, $sizes)) {
                                $sizes[] = $value;
                            }
                        }
                    }
                } else {
                    // Fallback for legacy color/size detection
                    if (strtolower($key) === 'color') {
                        $colorValue = $value;
                        if (!isset($colorVariantsMap[$value])) {
                            $colors[] = $value;
                            $colorCode = self::getColorCodeFromName($value);
                            $colorVariantsMap[$value] = [
                                'color' => $value,
                                'color_code' => $colorCode,
                                'image' => $variantImage,
                                'images' => !empty($variantImages) ? $variantImages : [],
                                'price' => $price,
                                'sale_price' => $salePrice,
                                'has_sale' => $hasVariantSale,
                                'display_price' => $hasVariantSale ? $salePrice : $price,
                            ];
                        }
                    }
                    if (strtolower($key) === 'size') {
                        $sizeValue = $value;
                        if (!in_array($value, $sizes)) {
                            $sizes[] = $value;
                        }
                    }
                }
            }
        }
        
        // Build attributes structure for frontend
        $attributesData = [];
        foreach ($allVariantAttributes as $attribute) {
            if (isset($attributeValues[$attribute->id]) && !empty($attributeValues[$attribute->id])) {
                $values = collect($attributeValues[$attribute->id])->map(function($value) use ($attribute, $attributeVariantsMap) {
                    $valueData = [
                        'value' => $value,
                    ];
                    
                    if (isset($attributeVariantsMap[$attribute->id][$value])) {
                        $variantData = $attributeVariantsMap[$attribute->id][$value];
                        if (isset($variantData['color_code'])) {
                            $valueData['color_code'] = $variantData['color_code'];
                        }
                        if (isset($variantData['images'])) {
                            $valueData['images'] = $variantData['images'];
                        }
                    }
                    
                    return $valueData;
                })->values()->toArray();
                
                $attributesData[] = [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'slug' => $attribute->slug,
                    'type' => $attribute->type,
                    'values' => $values,
                ];
            }
        }
        
        // Build variant data map with description and highlights_details for JavaScript
        $variantDataMap = [];
        foreach ($activeVariants as $variant) {
            $attrs = is_string($variant->attributes) 
                ? json_decode($variant->attributes, true) 
                : ($variant->attributes ?? []);
            
            $colorValue = null;
            $sizeValue = null;
            $allAttributeValues = []; // Store all attribute values for key generation
            
            // Get all attribute values
            foreach ($attrs as $key => $value) {
                if (empty($value)) {
                    continue;
                }
                
                $attribute = null;
                $attributeId = null;
                
                // Find attribute by ID, name, or slug
                if (is_numeric($key)) {
                    $attribute = $attributeMap->get($key);
                    $attributeId = $key;
                } elseif ($attributeNameMap->has($key)) {
                    $attribute = $attributeNameMap->get($key);
                    $attributeId = $attribute->id;
                } elseif ($attributeSlugMap->has($key)) {
                    $attribute = $attributeSlugMap->get($key);
                    $attributeId = $attribute->id;
                }
                
                if ($attribute) {
                    $allAttributeValues[$attributeId] = $value;
                    
                    // Legacy color/size support
                    if ($attribute->type === 'color' || strtolower($attribute->name) === 'color' || strtolower($attribute->slug) === 'color') {
                        $colorValue = $value;
                    }
                    if (strtolower($attribute->name) === 'size' || strtolower($attribute->slug) === 'size') {
                        $sizeValue = $value;
                    }
                } else {
                    // Fallback for legacy color/size detection
                    if (strtolower($key) === 'color') {
                        $colorValue = $value;
                    }
                    if (strtolower($key) === 'size') {
                        $sizeValue = $value;
                    }
                }
            }
            
            // Normalize highlights_details
            $highlightsDetails = [];
            if ($variant->highlights_details) {
                if (is_string($variant->highlights_details)) {
                    $highlightsDetails = json_decode($variant->highlights_details, true) ?? [];
                } else {
                    $highlightsDetails = is_array($variant->highlights_details) ? $variant->highlights_details : [];
                }
            }
            
            // Build key for variant - use all attributes sorted by ID for consistency
            $keyParts = [];
            ksort($allAttributeValues);
            foreach ($allAttributeValues as $attrId => $attrValue) {
                $keyParts[] = $attrId . ':' . $attrValue;
            }
            // Fallback to legacy color-size key if no attributes found
            if (empty($keyParts)) {
                $key = ($colorValue ?? '') . '|' . ($sizeValue ?? '');
            } else {
                $key = implode('|', $keyParts);
            }
            
            $price = $variant->price ?? 0;
            $salePrice = $variant->sale_price;
            $hasVariantSale = $salePrice && $salePrice < $price;
            
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
            
            $variantDataMap[$key] = [
                'id' => $variant->id,
                'sku' => $variant->sku ?? '',
                'price' => $price,
                'sale_price' => $salePrice,
                'has_sale' => $hasVariantSale,
                'display_price' => $hasVariantSale ? $salePrice : $price,
                'description' => $variant->description ?? '',
                'highlights_details' => $highlightsDetails,
                'attributes' => $allAttributeValues, // Include all attributes
                'images' => $variantImages, // Include variant images
                'is_in_stock' => $variant->manage_stock ? ($variant->stock_quantity > 0) : ($variant->stock_status === 'in_stock'),
            ];
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
            'primaryCategory',
            'primaryBrand',
            'productImages',
            'colors',
            'sizes',
            'colorVariantsMap',
            'attributesData', // New: All variant attributes dynamically
            'minPrice',
            'maxPrice',
            'minSalePrice',
            'maxSalePrice',
            'hasSale',
            'inStock',
            'inWishlist',
            'displaySku',
            'activeVariants',
            'similarProducts',
            'variantDataMap'
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
            'categories' => function($q) {
                $q->orderBy('product_categories.is_primary', 'desc');
            },
            'categories.parent',
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

        // Get primary category or first category from pivot table
        $primaryCategory = $product->categories->where('pivot.is_primary', true)->first();
        if (!$primaryCategory) {
            $primaryCategory = $product->categories->first();
        }
        // Fallback to category_id if no categories in pivot table
        if (!$primaryCategory && $product->category_id) {
            $primaryCategory = $product->category;
            if ($primaryCategory) {
                $primaryCategory->load('parent');
            }
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
        
        // Get all variant attributes dynamically from category or product
        $allVariantAttributes = collect();
        if ($primaryCategory) {
            $allVariantAttributes = $primaryCategory->getAllProductAttributes()
                ->filter(function($attr) {
                    return $attr->is_visible !== false && $attr->is_visible !== 0 && $attr->is_visible !== '0';
                });
        }
        
        // If no attributes from category, get all visible attributes
        if ($allVariantAttributes->isEmpty()) {
            $allVariantAttributes = ProductAttribute::visible()->ordered()->get();
        }
        
        // Create attribute maps for quick lookup
        $attributeMap = $allVariantAttributes->keyBy('id');
        $attributeNameMap = $allVariantAttributes->keyBy('name');
        $attributeSlugMap = $allVariantAttributes->keyBy('slug');
        
        // Extract all attribute values from variants
        $attributeValues = []; // Structure: ['attribute_id' => ['value1', 'value2', ...]]
        $attributeVariantsMap = []; // Structure: ['attribute_id' => ['value' => variant_data]]
        $colorAttribute = null;
        $colorVariantsMap = [];
        $colors = [];
        $sizes = [];
        
        foreach ($activeVariants as $variant) {
            $attrs = is_string($variant->attributes) 
                ? json_decode($variant->attributes, true) 
                : ($variant->attributes ?? []);
            
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
            
            // Extract all attribute values from this variant
            foreach ($attrs as $key => $value) {
                if (empty($value)) {
                    continue;
                }
                
                $attribute = null;
                $attributeId = null;
                
                // Find attribute by ID, name, or slug
                if (is_numeric($key)) {
                    $attribute = $attributeMap->get($key);
                    $attributeId = $key;
                } elseif ($attributeNameMap->has($key)) {
                    $attribute = $attributeNameMap->get($key);
                    $attributeId = $attribute->id;
                } elseif ($attributeSlugMap->has($key)) {
                    $attribute = $attributeSlugMap->get($key);
                    $attributeId = $attribute->id;
                }
                
                if ($attribute) {
                    // Initialize arrays if needed
                    if (!isset($attributeValues[$attributeId])) {
                        $attributeValues[$attributeId] = [];
                    }
                    if (!isset($attributeVariantsMap[$attributeId])) {
                        $attributeVariantsMap[$attributeId] = [];
                    }
                    
                    // Add unique value
                    if (!in_array($value, $attributeValues[$attributeId])) {
                        $attributeValues[$attributeId][] = $value;
                    }
                    
                    // Store variant data for this attribute value
                    if (!isset($attributeVariantsMap[$attributeId][$value])) {
                        $attributeVariantsMap[$attributeId][$value] = [
                            'value' => $value,
                            'images' => $variantImages,
                        ];
                        
                        // If it's a color attribute, also get color code
                        if ($attribute->type === 'color') {
                            $colorAttribute = $attribute;
                            $attributeValue = ProductAttributeValue::where('attribute_id', $attribute->id)
                                ->whereRaw('LOWER(value) = ?', [strtolower($value)])
                                ->first();
                            $colorCode = $attributeValue && $attributeValue->color_code 
                                ? $attributeValue->color_code 
                                : self::getColorCodeFromName($value);
                            
                            $attributeVariantsMap[$attributeId][$value]['color_code'] = $colorCode;
                            
                            // Also populate legacy color arrays for backward compatibility
                            if (!in_array($value, $colors)) {
                                $colors[] = $value;
                                $colorVariantsMap[$value] = [
                                    'color' => $value,
                                    'color_code' => $colorCode,
                                    'images' => $variantImages,
                                ];
                            }
                        }
                        
                        // Legacy size support
                        if (strtolower($attribute->name) === 'size' || strtolower($attribute->slug) === 'size') {
                            if (!in_array($value, $sizes)) {
                                $sizes[] = $value;
                            }
                        }
                    }
                }
            }
        }
        
        // Build attributes structure for frontend
        $attributesData = [];
        foreach ($allVariantAttributes as $attribute) {
            if (isset($attributeValues[$attribute->id]) && !empty($attributeValues[$attribute->id])) {
                $values = collect($attributeValues[$attribute->id])->map(function($value) use ($attribute, $attributeVariantsMap) {
                    $valueData = [
                        'value' => $value,
                    ];
                    
                    if (isset($attributeVariantsMap[$attribute->id][$value])) {
                        $variantData = $attributeVariantsMap[$attribute->id][$value];
                        if (isset($variantData['color_code'])) {
                            $valueData['color_code'] = $variantData['color_code'];
                        }
                        if (isset($variantData['images'])) {
                            $valueData['images'] = $variantData['images'];
                        }
                    }
                    
                    return $valueData;
                })->values()->toArray();
                
                $attributesData[] = [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'slug' => $attribute->slug,
                    'type' => $attribute->type,
                    'values' => $values,
                ];
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
                'category' => $primaryCategory ? $primaryCategory->name : '',
                'category_slug' => $primaryCategory ? $primaryCategory->slug : '',
                'parent_category' => $primaryCategory && $primaryCategory->parent ? $primaryCategory->parent->name : null,
                'parent_category_slug' => $primaryCategory && $primaryCategory->parent ? $primaryCategory->parent->slug : null,
                'brand' => $product->brand ? $product->brand->name : null,
                'brand_slug' => $product->brand ? $product->brand->slug : null,
                'images' => $productImages,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'min_sale_price' => $minSalePrice,
                'max_sale_price' => $maxSalePrice,
                'has_sale' => $hasSale,
                'price_display' => $minPrice != $maxPrice 
                    ? '₹' . number_format($minPrice, 0) . ' - ₹' . number_format($maxPrice, 0)
                    : '₹' . number_format($minPrice, 0),
                'colors' => array_values($colors), // Legacy support
                'sizes' => array_values($sizes), // Legacy support
                'color_variants' => $colorVariantsMap, // Legacy support
                'attributes' => $attributesData, // New: All variant attributes dynamically
                'in_stock' => $inStock,
                'variants' => $activeVariants->map(function($variant) use ($allVariantAttributes, $attributeMap, $attributeNameMap, $attributeSlugMap, $colorAttribute) {
                    $attrs = is_string($variant->attributes) 
                        ? json_decode($variant->attributes, true) 
                        : ($variant->attributes ?? []);
                    
                    $colorValue = null;
                    $sizeValue = null;
                    $allAttributes = []; // New: All attributes for this variant
                    
                    foreach ($attrs as $key => $value) {
                        if (empty($value)) {
                            continue;
                        }
                        
                        $attribute = null;
                        $attributeId = null;
                        
                        // Find attribute by ID, name, or slug
                        if (is_numeric($key)) {
                            $attribute = $attributeMap->get($key);
                            $attributeId = $key;
                        } elseif ($attributeNameMap->has($key)) {
                            $attribute = $attributeNameMap->get($key);
                            $attributeId = $attribute->id;
                        } elseif ($attributeSlugMap->has($key)) {
                            $attribute = $attributeSlugMap->get($key);
                            $attributeId = $attribute->id;
                        }
                        
                        if ($attribute) {
                            // Store all attributes
                            $allAttributes[] = [
                                'attribute_id' => $attribute->id,
                                'attribute_name' => $attribute->name,
                                'attribute_slug' => $attribute->slug,
                                'attribute_type' => $attribute->type,
                                'value' => $value,
                            ];
                            
                            // Legacy color/size support
                            if ($attribute->type === 'color' || strtolower($attribute->name) === 'color' || strtolower($attribute->slug) === 'color') {
                                $colorValue = $value;
                            }
                            if (strtolower($attribute->name) === 'size' || strtolower($attribute->slug) === 'size') {
                                $sizeValue = $value;
                            }
                        } else {
                            // Fallback for legacy color/size detection
                            if (strtolower($key) === 'color') {
                                $colorValue = $value;
                            }
                            if (strtolower($key) === 'size') {
                                $sizeValue = $value;
                            }
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
                    
                    // Get highlights_details
                    $highlightsDetails = [];
                    if ($variant->highlights_details) {
                        if (is_string($variant->highlights_details)) {
                            $highlightsDetails = json_decode($variant->highlights_details, true) ?? [];
                        } else {
                            $highlightsDetails = is_array($variant->highlights_details) ? $variant->highlights_details : [];
                        }
                    }
                    
                    return [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'price' => $variant->price,
                        'sale_price' => $variant->sale_price,
                        'color' => $colorValue, // Legacy support
                        'size' => $sizeValue, // Legacy support
                        'attributes' => $allAttributes, // New: All attributes for this variant
                        'images' => $variantImages,
                        'stock_quantity' => $variant->stock_quantity,
                        'stock_status' => $variant->stock_status,
                        'manage_stock' => $variant->manage_stock,
                        'is_in_stock' => $variant->manage_stock ? $variant->stock_quantity > 0 : $variant->stock_status === 'in_stock',
                        'highlights_details' => $highlightsDetails,
                    ];
                }),
                'default_sku' => $activeVariants->first() ? $activeVariants->first()->sku : '',
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

    public function myOrders(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        
        if (!$customer) {
            return redirect()->route('frontend.index')->with('show_login', true);
        }
        
        // Fetch orders with items, products, and variants
        $orders = Order::where('customer_id', $customer->id)
            ->with(['items' => function($query) {
                $query->with(['product' => function($q) {
                    $q->with(['primaryImage', 'category', 'images' => function($imgQ) {
                        $imgQ->orderBy('sort_order')->orderBy('id')->limit(1);
                    }]);
                }, 'variant' => function($q) {
                    $q->with(['images' => function($imgQ) {
                        $imgQ->orderBy('sort_order')->orderBy('id')->limit(1);
                    }]);
                }]);
            }])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('frontend.my-orders', compact('customer', 'orders'));
    }

    public function wishlist(Request $request)
    {
        // Get session ID or customer ID (similar to API controller)
        $customer = Auth::guard('customer')->user();
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
                    ? '₹' . number_format($minPrice, 0) . ' - ₹' . number_format($maxPrice, 0)
                    : '₹' . number_format($minPrice, 0),
                'is_new' => $product->created_at->isAfter(now()->subDays(30)),
                'is_featured' => $product->featured,
                'color_variants' => $formattedColorVariants,
            ];
        });
        
        return view('frontend.wishlist', compact('wishlistProducts', 'customer'));
    }

    public function profileInfo(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        
        \Log::info('=== PROFILE INFO PAGE LOAD ===');
        \Log::info('Customer ID: ' . ($customer ? $customer->id : 'NULL'));
        
        if ($customer) {
            \Log::info('Customer Data from Auth: ' . json_encode([
                'id' => $customer->id,
                'full_name' => $customer->full_name,
                'email' => $customer->email,
                'profile_image' => $customer->profile_image,
            ]));
            
            // Verify database directly
            $dbCustomer = \DB::table('customers')->where('id', $customer->id)->first();
            if ($dbCustomer) {
                \Log::info('Database Direct Query Result: ' . json_encode([
                    'id' => $dbCustomer->id,
                    'full_name' => $dbCustomer->full_name,
                    'email' => $dbCustomer->email,
                    'profile_image' => $dbCustomer->profile_image,
                ]));
                
                // Check if profile_image file exists and sync if needed
                if ($dbCustomer->profile_image) {
                    $fullPath = storage_path('app/public/' . $dbCustomer->profile_image);
                    $publicPath = public_path('storage/' . $dbCustomer->profile_image);
                    \Log::info('Profile Image Storage Path: ' . $fullPath);
                    \Log::info('Profile Image Exists (storage): ' . (file_exists($fullPath) ? 'Yes' : 'No'));
                    \Log::info('Profile Image Public Path: ' . $publicPath);
                    \Log::info('Profile Image Exists (public): ' . (file_exists($publicPath) ? 'Yes' : 'No'));
                    
                    // Sync file to public/storage if it exists in storage but not in public
                    if (file_exists($fullPath) && !file_exists($publicPath)) {
                        $destinationDir = dirname($publicPath);
                        if (!is_dir($destinationDir)) {
                            File::makeDirectory($destinationDir, 0755, true);
                            \Log::info('Created directory for sync: ' . $destinationDir);
                        }
                        
                        if (File::copy($fullPath, $publicPath)) {
                            \Log::info('Synced existing file to public/storage: ' . $publicPath);
                        } else {
                            \Log::error('Failed to sync file to public/storage: ' . $publicPath);
                        }
                    }
                }
            }
        }
        
        // Get fields for profile info (basic_info group)
        $fields = FieldManagement::where('field_group', 'basic_info')
            ->active()
            ->visible()
            ->ordered()
            ->get();
        
        // Get Quality-of-Life fields (qol group) - includes profile_image
        $qolFields = FieldManagement::where('field_group', 'qol')
            ->active()
            ->visible()
            ->ordered()
            ->get();
        
        \Log::info('=== PROFILE INFO PAGE LOAD END ===');
        
        return view('frontend.profile-info', compact('customer', 'fields', 'qolFields'));
    }

    public function updateProfileInfo(Request $request)
    {
        \Log::info('=== PROFILE UPDATE START ===');
        \Log::info('Request Method: ' . $request->method());
        \Log::info('Is AJAX: ' . ($request->ajax() ? 'Yes' : 'No'));
        \Log::info('Request Data: ' . json_encode($request->except(['password', 'password_confirmation'])));
        
        $customer = Auth::guard('customer')->user();
        
        if (!$customer) {
            \Log::error('No authenticated customer found');
            return redirect()->route('frontend.index')->with('error', 'Please login to update your profile.');
        }
        
        \Log::info('Customer ID: ' . $customer->id);
        \Log::info('Customer Current Data: ' . json_encode([
            'full_name' => $customer->full_name,
            'email' => $customer->email,
            'profile_image' => $customer->profile_image,
        ]));
        
        // Get fields for validation (basic_info and qol groups)
        $fields = FieldManagement::whereIn('field_group', ['basic_info', 'qol'])
            ->active()
            ->visible()
            ->ordered()
            ->get();
        
        \Log::info('Fields Count: ' . $fields->count());
        
        // Build validation rules
        $rules = [];
        foreach ($fields as $field) {
            $fieldRules = [];
            
            // File uploads are optional unless required
            if ($field->input_type === 'file') {
                if ($field->is_required) {
                    $fieldRules[] = 'required';
                } else {
                    $fieldRules[] = 'nullable';
                }
                $fieldRules[] = 'image';
                $fieldRules[] = 'mimes:jpeg,jpg,png,gif,webp';
                $fieldRules[] = 'max:2048'; // 2MB max
            } else {
                if ($field->is_required) {
                    $fieldRules[] = 'required';
                }
                
                if ($field->validation_rules) {
                    $fieldRules[] = $field->validation_rules;
                }
                
                if ($field->input_type === 'email') {
                    $fieldRules[] = 'email';
                }
                
                if ($field->input_type === 'tel') {
                    $fieldRules[] = 'max:20';
                }
                
                // For date fields, add date validation
                if ($field->input_type === 'date') {
                    $fieldRules[] = 'date';
                }
            }
            
            // Always add nullable for non-required fields if no rules set
            if (empty($fieldRules) && !$field->is_required) {
                $fieldRules[] = 'nullable';
            } elseif (!empty($fieldRules) && !$field->is_required && !in_array('required', $fieldRules)) {
                // Add nullable if not required and required is not in rules
                if (!in_array('nullable', $fieldRules)) {
                    array_unshift($fieldRules, 'nullable');
                }
            }
            
            if (!empty($fieldRules)) {
                $rules[$field->field_key] = implode('|', $fieldRules);
            }
        }
        
        \Log::info('Validation Rules: ' . json_encode($rules));
        
        // Validate request
        try {
            $validated = $request->validate($rules);
            \Log::info('Validation Passed. Validated Data: ' . json_encode(array_keys($validated)));
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation Failed: ' . json_encode($e->errors()));
            throw $e;
        }
        
        // Handle file uploads first
        foreach ($fields as $field) {
            if ($field->input_type === 'file' && $request->hasFile($field->field_key)) {
                $file = $request->file($field->field_key);
                
                \Log::info('File Upload Detected for: ' . $field->field_key);
                \Log::info('File Name: ' . $file->getClientOriginalName());
                \Log::info('File Size: ' . $file->getSize());
                \Log::info('File MIME: ' . $file->getMimeType());
                
                // Generate unique filename
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                // Store in storage/app/public/customers/profile_images
                $path = $file->storeAs('customers/profile_images', $filename, 'public');
                
                \Log::info('File Stored Path: ' . $path);
                \Log::info('Full Storage Path: ' . storage_path('app/public/' . $path));
                \Log::info('File Exists: ' . (file_exists(storage_path('app/public/' . $path)) ? 'Yes' : 'No'));
                
                // Check if storage link exists
                $publicStoragePath = public_path('storage');
                $storageLinkExists = is_link($publicStoragePath) || is_dir($publicStoragePath);
                \Log::info('Storage Link Exists: ' . ($storageLinkExists ? 'Yes' : 'No'));
                \Log::info('Public Storage Path: ' . $publicStoragePath);
                
                // Ensure file is copied to public/storage for immediate access
                $sourceFile = storage_path('app/public/' . $path);
                $destinationFile = public_path('storage/' . $path);
                $destinationDir = dirname($destinationFile);
                
                if (file_exists($sourceFile)) {
                    // Create destination directory if it doesn't exist
                    if (!is_dir($destinationDir)) {
                        File::makeDirectory($destinationDir, 0755, true);
                        \Log::info('Created directory: ' . $destinationDir);
                    }
                    
                    // Copy file to public/storage
                    if (File::copy($sourceFile, $destinationFile)) {
                        \Log::info('File copied to public/storage: ' . $destinationFile);
                        \Log::info('Public file exists: ' . (file_exists($destinationFile) ? 'Yes' : 'No'));
                    } else {
                        \Log::error('Failed to copy file to public/storage: ' . $destinationFile);
                    }
                } else {
                    \Log::error('Source file does not exist: ' . $sourceFile);
                }
                
                // Delete old image if exists
                if ($customer->{$field->field_key}) {
                    $oldPath = storage_path('app/public/' . $customer->{$field->field_key});
                    \Log::info('Old Image Path: ' . $oldPath);
                    \Log::info('Old Image Exists: ' . (file_exists($oldPath) ? 'Yes' : 'No'));
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                        \Log::info('Old Image Deleted');
                    }
                }
                
                // Save relative path (normalize to forward slashes for cross-platform compatibility)
                $normalizedPath = str_replace('\\', '/', $path);
                $customer->{$field->field_key} = $normalizedPath;
                \Log::info('Setting ' . $field->field_key . ' to: ' . $normalizedPath);
            }
        }
        
        // Update customer fields
        foreach ($fields as $field) {
            if ($field->input_type === 'file') {
                continue; // Already handled above
            }
            
            // Skip password fields if empty (don't update password unless provided)
            if (in_array($field->input_type, ['password']) && empty($request->input($field->field_key))) {
                continue;
            }
            
            // Check if field exists in validated data or request
            $fieldValue = null;
            if (isset($validated[$field->field_key])) {
                $fieldValue = $validated[$field->field_key];
            } elseif ($request->has($field->field_key)) {
                // For nullable fields that might not be in validated, get from request
                $fieldValue = $request->input($field->field_key);
                // Convert empty string to null for nullable fields
                if ($fieldValue === '' && !$field->is_required) {
                    $fieldValue = null;
                }
            } else {
                // Field not in request, skip
                continue;
            }
            
            // Handle date fields - ensure proper format
            if ($field->input_type === 'date' && $fieldValue) {
                try {
                    // Ensure date is in Y-m-d format
                    $dateValue = Carbon::parse($fieldValue)->format('Y-m-d');
                    $fieldValue = $dateValue;
                    \Log::info('Date field ' . $field->field_key . ' formatted to: ' . $fieldValue);
                } catch (\Exception $e) {
                    \Log::error('Invalid date format for ' . $field->field_key . ': ' . $fieldValue . ' - Error: ' . $e->getMessage());
                    continue;
                }
            }
            
            $oldValue = $customer->{$field->field_key};
            $customer->{$field->field_key} = $fieldValue;
            \Log::info('Updating ' . $field->field_key . ': ' . ($oldValue ?? 'NULL') . ' -> ' . ($fieldValue ?? 'NULL'));
        }
        
        \Log::info('Customer Data Before Save: ' . json_encode($customer->getAttributes()));
        
        $saved = $customer->save();
        
        \Log::info('Save Result: ' . ($saved ? 'Success' : 'Failed'));
        
        // Refresh customer data
        $customer->refresh();
        
        \Log::info('Customer Data After Refresh: ' . json_encode([
            'id' => $customer->id,
            'full_name' => $customer->full_name,
            'email' => $customer->email,
            'date_of_birth' => $customer->date_of_birth,
            'gender' => $customer->gender,
            'profile_image' => $customer->profile_image,
        ]));
        
        // Verify database directly
        $dbCustomer = \DB::table('customers')->where('id', $customer->id)->first();
        \Log::info('Database Direct Query Result: ' . json_encode([
            'id' => $dbCustomer->id ?? 'NULL',
            'full_name' => $dbCustomer->full_name ?? 'NULL',
            'email' => $dbCustomer->email ?? 'NULL',
            'date_of_birth' => $dbCustomer->date_of_birth ?? 'NULL',
            'gender' => $dbCustomer->gender ?? 'NULL',
            'profile_image' => $dbCustomer->profile_image ?? 'NULL',
        ]));
        
        // Return JSON response for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            $profileImageUrl = null;
            if ($customer->profile_image) {
                // Use Storage facade to get the URL, which handles path correctly
                $profileImageUrl = Storage::disk('public')->url($customer->profile_image);
                \Log::info('Profile Image Path: ' . $customer->profile_image);
                \Log::info('Profile Image URL: ' . $profileImageUrl);
                \Log::info('Storage File Exists: ' . (Storage::disk('public')->exists($customer->profile_image) ? 'Yes' : 'No'));
            }
            
            \Log::info('=== PROFILE UPDATE END (AJAX) ===');
            
            // Build response data with all updated fields
            $responseData = [
                'full_name' => $customer->full_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'alternate_phone' => $customer->alternate_phone,
                'profile_image' => $customer->profile_image,
                'profile_image_url' => $profileImageUrl,
            ];
            
            // Add date fields (format for frontend)
            if ($customer->date_of_birth) {
                $responseData['date_of_birth'] = $customer->date_of_birth instanceof \Carbon\Carbon 
                    ? $customer->date_of_birth->format('Y-m-d')
                    : $customer->date_of_birth;
            } else {
                $responseData['date_of_birth'] = null;
            }
            
            // Add other fields
            $responseData['gender'] = $customer->gender;
            $responseData['preferred_contact_method'] = $customer->preferred_contact_method;
            $responseData['preferred_payment_method'] = $customer->preferred_payment_method;
            $responseData['preferred_delivery_slot'] = $customer->preferred_delivery_slot;
            $responseData['newsletter_opt_in'] = $customer->newsletter_opt_in;
            
            // Add any custom fields from field management
            foreach ($fields as $field) {
                if ($field->input_type !== 'file' && isset($customer->{$field->field_key})) {
                    $value = $customer->{$field->field_key};
                    
                    // Format date fields
                    if ($field->input_type === 'date' && $value) {
                        $value = $value instanceof \Carbon\Carbon 
                            ? $value->format('Y-m-d')
                            : $value;
                    }
                    
                    $responseData[$field->field_key] = $value;
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'data' => $responseData
            ]);
        }
        
        \Log::info('=== PROFILE UPDATE END (REDIRECT) ===');
        
        // Return redirect for regular form submissions
        return redirect()->route('frontend.profile-info')->with('success', 'Profile updated successfully!');
    }

    public function changePassword(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        
        // Get password fields from field management
        $passwordFields = FieldManagement::whereIn('field_key', ['password', 'password_confirmation'])
            ->active()
            ->visible()
            ->ordered()
            ->get();
        
        // Create old_password field (not in field management, always needed)
        $oldPasswordField = (object)[
            'field_key' => 'old_password',
            'label' => 'Current Password',
            'input_type' => 'password',
            'placeholder' => 'Enter your current password',
            'is_required' => true,
            'help_text' => null,
        ];
        
        // If password fields exist in field management, use them; otherwise create defaults
        if ($passwordFields->isEmpty()) {
            // Create default password fields
            $passwordFields = collect([
                (object)[
                    'field_key' => 'password',
                    'label' => 'New Password',
                    'input_type' => 'password',
                    'placeholder' => 'Enter new password',
                    'is_required' => true,
                    'help_text' => 'Minimum 8 characters',
                ],
                (object)[
                    'field_key' => 'password_confirmation',
                    'label' => 'Confirm New Password',
                    'input_type' => 'password',
                    'placeholder' => 'Confirm new password',
                    'is_required' => true,
                    'help_text' => null,
                ],
            ]);
        } else {
            // Update labels for change password context
            foreach ($passwordFields as $field) {
                if ($field->field_key === 'password') {
                    $field->label = 'New Password';
                    $field->placeholder = $field->placeholder ?: 'Enter new password';
                } elseif ($field->field_key === 'password_confirmation') {
                    $field->label = 'Confirm New Password';
                    $field->placeholder = $field->placeholder ?: 'Confirm new password';
                }
            }
        }
        
        // Combine: old_password first, then password fields
        $passwordFields = collect([$oldPasswordField])->merge($passwordFields);
        
        return view('frontend.change-password', compact('customer', 'passwordFields'));
    }

    public function updatePassword(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        
        if (!$customer) {
            return redirect()->route('frontend.index')->with('error', 'Please login to change your password.');
        }
        
        // Get password fields for validation
        $passwordFields = FieldManagement::whereIn('field_key', ['password', 'password_confirmation'])
            ->active()
            ->visible()
            ->get();
        
        // Build validation rules
        $rules = [
            'old_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ];
        
        // Validate request
        $validated = $request->validate($rules);
        
        try {
            // Verify old password
            if (!Hash::check($request->old_password, $customer->password)) {
                return redirect()->route('frontend.change-password')
                    ->with('error', 'Current password is incorrect.')
                    ->withInput();
            }
            
            // Check if new password is same as old password
            if (Hash::check($request->password, $customer->password)) {
                return redirect()->route('frontend.change-password')
                    ->with('error', 'New password must be different from current password.')
                    ->withInput();
            }
            
            // Update password
            $customer->password = $request->password; // Will be hashed by model mutator
            $customer->save();
            
            \Log::info('Password changed for customer ID: ' . $customer->id);
            
            return redirect()->route('frontend.change-password')
                ->with('success', 'Password changed successfully!');
                
        } catch (\Exception $e) {
            \Log::error('Error changing password: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->route('frontend.change-password')
                ->with('error', 'Error changing password: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function addresses(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        
        // Get fields for addresses (address group) - use cache for performance
        $addressFields = Cache::remember('address_fields', 3600, function() {
            return FieldManagement::where('field_group', 'address')
                ->active()
                ->visible()
                ->ordered()
                ->get();
        });
        
        // Get customer addresses from database
        $addresses = [];
        if ($customer) {
            $addresses = CustomerAddress::where('customer_id', $customer->id)
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        return view('frontend.addresses', compact('customer', 'addressFields', 'addresses'));
    }

    /**
     * Get location data (country, state, city) by pincode
     */
    /**
     * Optimized function to search pincodes.json file (streaming approach)
     */
    private function searchPincodeInPincodesJson($filePath, $pincode)
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return null;
        }
        
        $pincodeInt = (int)$pincode;
        $pincodeStr = (string)$pincode;
        $chunkSize = 512 * 1024; // 512KB chunks
        $buffer = '';
        $filePosition = 0;
        $matchPosition = null;
        
        // Read file in chunks and search for pincode
        while (!feof($handle)) {
            $chunk = fread($handle, $chunkSize);
            if ($chunk === false) {
                break;
            }
            
            $buffer .= $chunk;
            
            // Search for pincode pattern: "pincode":123456 (numeric, not quoted)
            // Format: {"officeName":"...","pincode":504293,"taluk":"...","districtName":"...","stateName":"..."}
            // Try multiple patterns to handle different formats
            $patterns = [
                '/"pincode"\s*:\s*' . $pincodeInt . '(?=\s*[,}])/',  // Lookahead: "pincode":123456 followed by comma or }
                '/"pincode"\s*:\s*"' . preg_quote($pincodeStr, '/') . '"/',  // Quoted: "pincode":"123456"
                '/"pincode"\s*:\s*' . preg_quote($pincodeStr, '/') . '(?=\s*[,}])/',  // String without quotes with lookahead
                '/"pincode"\s*:\s*' . $pincodeInt . '[,\}]/',  // Simpler: "pincode":123456 followed by comma or }
            ];
            
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $buffer, $matches, PREG_OFFSET_CAPTURE)) {
                    $matchPosition = $filePosition + $matches[0][1];
                    \Log::info('Pincode match found at position: ' . $matchPosition . ' with pattern: ' . $pattern);
                    break 2;
                }
            }
            
            // Keep last 50KB of buffer to handle matches across chunks
            if (strlen($buffer) > 51200) {
                $removed = strlen($buffer) - 51200;
                $filePosition += $removed;
                $buffer = substr($buffer, -51200);
            } else {
                $filePosition += strlen($chunk);
            }
        }
        
        fclose($handle);
        
        if (!$matchPosition) {
            return null;
        }
        
        // Re-read file to extract the JSON object at the found position
        $handle = fopen($filePath, 'r');
        $contextSize = 5000; // 5KB context
        $startPos = max(0, $matchPosition - $contextSize);
        fseek($handle, $startPos);
        $context = fread($handle, $contextSize * 2);
        fclose($handle);
        
        // Find the JSON object containing the pincode
        $relativeMatchPos = $matchPosition - $startPos;
        
        // Find opening brace before the match
        $objectStart = $relativeMatchPos;
        while ($objectStart > 0 && $context[$objectStart] !== '{') {
            $objectStart--;
        }
        
        // If we didn't find an opening brace, the context might be too small
        if ($objectStart === 0 && $context[0] !== '{') {
            \Log::info('Could not find opening brace, increasing context size');
            // Try with larger context
            $contextSize = 20000; // 20KB context
            $startPos = max(0, $matchPosition - $contextSize);
            $handle = fopen($filePath, 'r');
            fseek($handle, $startPos);
            $context = fread($handle, $contextSize * 2);
            fclose($handle);
            $relativeMatchPos = $matchPosition - $startPos;
            $objectStart = $relativeMatchPos;
            while ($objectStart > 0 && $context[$objectStart] !== '{') {
                $objectStart--;
            }
        }
        
        // Find closing brace after the match
        // Start counting from the opening brace
        $objectEnd = $objectStart;
        $braceCount = 0; // Will be incremented when we see the opening brace
        $inString = false;
        $escapeNext = false;
        
        while ($objectEnd < strlen($context)) {
            $char = $context[$objectEnd];
            
            if ($escapeNext) {
                $escapeNext = false;
            } elseif ($char === '\\') {
                $escapeNext = true;
            } elseif ($char === '"' && !$escapeNext) {
                $inString = !$inString;
            } elseif (!$inString) {
                if ($char === '{') {
                    $braceCount++;
                } elseif ($char === '}') {
                    $braceCount--;
                    if ($braceCount === 0) {
                        // Found complete object
                        $objectJson = substr($context, $objectStart, $objectEnd - $objectStart + 1);
                        \Log::info('Extracted JSON object: ' . substr($objectJson, 0, 200));
                        $pincodeData = json_decode($objectJson, true);
                        if ($pincodeData && is_array($pincodeData)) {
                            \Log::info('Successfully decoded pincode data');
                            return $pincodeData;
                        } else {
                            \Log::info('Failed to decode JSON: ' . json_last_error_msg());
                            return null;
                        }
                    }
                }
            }
            $objectEnd++;
        }
        
        \Log::info('Could not find closing brace, object might be too large or context insufficient');
        return null;
    }

    /**
     * Optimized function to search JSON file line by line (streaming approach)
     */
    private function searchCityByPincodeStreaming($filePath, $pincode)
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return null;
        }
        
        $foundCity = null;
        $buffer = '';
        $inDataArray = false;
        $braceCount = 0;
        $currentObject = '';
        $maxReadSize = 50 * 1024 * 1024; // 50MB max read
        $bytesRead = 0;
        
        // Read file in chunks
        while (!feof($handle) && $bytesRead < $maxReadSize) {
            $chunk = fread($handle, 8192); // 8KB chunks
            if ($chunk === false) {
                break;
            }
            $bytesRead += strlen($chunk);
            $buffer .= $chunk;
            
            // Process complete JSON objects from buffer
            while (($pos = strpos($buffer, '{')) !== false) {
                $braceCount = 0;
                $objectStart = $pos;
                $i = $pos;
                
                // Find complete JSON object
                while ($i < strlen($buffer)) {
                    if ($buffer[$i] === '{') {
                        $braceCount++;
                    } elseif ($buffer[$i] === '}') {
                        $braceCount--;
                        if ($braceCount === 0) {
                            // Complete object found
                            $objectJson = substr($buffer, $objectStart, $i - $objectStart + 1);
                            $city = json_decode($objectJson, true);
                            
                            if ($city && is_array($city)) {
                                // Check pincode fields
                                $postalCode = null;
                                if (isset($city['postal_code'])) {
                                    $postalCode = trim((string)$city['postal_code']);
                                } elseif (isset($city['postalcode'])) {
                                    $postalCode = trim((string)$city['postalcode']);
                                } elseif (isset($city['pincode'])) {
                                    $postalCode = trim((string)$city['pincode']);
                                } elseif (isset($city['zip_code'])) {
                                    $postalCode = trim((string)$city['zip_code']);
                                } elseif (isset($city['zipcode'])) {
                                    $postalCode = trim((string)$city['zipcode']);
                                }
                                
                                if ($postalCode && (string)$postalCode === (string)$pincode) {
                                    fclose($handle);
                                    return $city;
                                }
                            }
                            
                            // Remove processed object from buffer
                            $buffer = substr($buffer, $i + 1);
                            break;
                        }
                    }
                    $i++;
                }
                
                // If no complete object found, break to read more
                if ($braceCount > 0) {
                    break;
                }
            }
        }
        
        fclose($handle);
        return null;
    }
    
    /**
     * Optimized function using streaming approach - doesn't load entire file
     */
    private function searchCityByPincodeOptimized($filePath, $pincode)
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return null;
        }
        
        $pincodePattern = preg_quote($pincode, '/');
        $patterns = [
            '/"postal_code"\s*:\s*"?' . $pincodePattern . '"?/',
            '/"postalcode"\s*:\s*"?' . $pincodePattern . '"?/',
            '/"pincode"\s*:\s*"?' . $pincodePattern . '"?/',
            '/"zip_code"\s*:\s*"?' . $pincodePattern . '"?/',
            '/"zipcode"\s*:\s*"?' . $pincodePattern . '"?/',
        ];
        
        $chunkSize = 512 * 1024; // 512KB chunks
        $buffer = '';
        $filePosition = 0;
        $matchPosition = null;
        $matchedPattern = null;
        
        // Read file in chunks and search for pincode
        while (!feof($handle)) {
            $chunk = fread($handle, $chunkSize);
            if ($chunk === false) {
                break;
            }
            
            $buffer .= $chunk;
            
            // Search for pincode in buffer
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $buffer, $matches, PREG_OFFSET_CAPTURE)) {
                    $matchPosition = $filePosition + $matches[0][1];
                    $matchedPattern = $pattern;
                    break 2;
                }
            }
            
            // Keep last 50KB of buffer to handle matches across chunks
            if (strlen($buffer) > 51200) {
                $removed = strlen($buffer) - 51200;
                $filePosition += $removed;
                $buffer = substr($buffer, -51200);
            } else {
                $filePosition += strlen($chunk);
            }
        }
        
        fclose($handle);
        
        if (!$matchPosition) {
            return null;
        }
        
        // Re-read file to extract the JSON object at the found position
        $handle = fopen($filePath, 'r');
        $contextSize = 10000; // 10KB context
        $startPos = max(0, $matchPosition - $contextSize);
        fseek($handle, $startPos);
        $context = fread($handle, $contextSize * 2);
        fclose($handle);
        
        // Find the JSON object containing the pincode
        $relativeMatchPos = $matchPosition - $startPos;
        
        // Find opening brace before the match
        $objectStart = $relativeMatchPos;
        while ($objectStart > 0 && $context[$objectStart] !== '{') {
            $objectStart--;
        }
        
        // Find closing brace after the match
        $objectEnd = $relativeMatchPos;
        $braceCount = 0;
        $inString = false;
        $escapeNext = false;
        
        while ($objectEnd < strlen($context)) {
            $char = $context[$objectEnd];
            
            if ($escapeNext) {
                $escapeNext = false;
            } elseif ($char === '\\') {
                $escapeNext = true;
            } elseif ($char === '"' && !$escapeNext) {
                $inString = !$inString;
            } elseif (!$inString) {
                if ($char === '{') {
                    $braceCount++;
                } elseif ($char === '}') {
                    $braceCount--;
                    if ($braceCount === 0) {
                        // Found complete object
                        $objectJson = substr($context, $objectStart, $objectEnd - $objectStart + 1);
                        $city = json_decode($objectJson, true);
                        return ($city && is_array($city)) ? $city : null;
                    }
                }
            }
            $objectEnd++;
        }
        
        return null;
    }

    public function getLocationByPincode(Request $request)
    {
        try {
            $pincode = $request->get('pincode');
            
            \Log::info('Pincode lookup request: ' . $pincode);
            
            if (!$pincode || strlen(trim($pincode)) < 4) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please enter a valid pincode'
                ], 400);
            }
            
            $pincode = trim($pincode);
            
            // Load pincodes JSON (new format)
            $pincodesPath = public_path('location-json/pincodes.json');
            \Log::info('Pincodes path: ' . $pincodesPath);
            \Log::info('Pincodes file exists: ' . (file_exists($pincodesPath) ? 'Yes' : 'No'));
            
            if (!file_exists($pincodesPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location data not available'
                ], 404);
            }
            
            // Use optimized search method for pincodes.json
            $foundPincode = $this->searchPincodeInPincodesJson($pincodesPath, $pincode);
            
            if (!$foundPincode) {
                \Log::info('Pincode not found: ' . $pincode);
                return response()->json([
                    'success' => false,
                    'message' => 'Location not found for this pincode'
                ], 404);
            }
            
            \Log::info('Found pincode data: ' . json_encode($foundPincode));
            
            // Extract data from pincodes.json format
            // Format: {"officeName":"Ada B.O","pincode":504293,"taluk":"Asifabad","districtName":"Adilabad","stateName":"ANDHRA PRADESH"}
            $districtName = $foundPincode['districtName'] ?? '';
            $taluk = $foundPincode['taluk'] ?? '';
            $stateNameRaw = $foundPincode['stateName'] ?? '';
            $countryName = 'India'; // Always India for pincodes
            
            // Normalize state name (convert to title case)
            $stateName = $stateNameRaw ? ucwords(strtolower($stateNameRaw)) : '';
            
            // Use districtName as city, fallback to taluk
            $cityName = $districtName ?: $taluk;
            
            \Log::info('Found pincode data - District: ' . $districtName . ', Taluk: ' . $taluk . ', State: ' . $stateName);
            
            // Look up state in states.json to get state ID and proper name
            $stateId = null;
            $stateNameFinal = $stateName;
            if ($stateNameRaw) {
                $statesPath = public_path('location-json/states.json');
                if (file_exists($statesPath)) {
                    $statesContent = file_get_contents($statesPath);
                    $statesData = json_decode($statesContent, true);
                    
                    if (json_last_error() === JSON_ERROR_NONE && is_array($statesData)) {
                        // States.json is a direct array
                        foreach ($statesData as $state) {
                            // Match by name (case insensitive)
                            $stateNameInFile = strtoupper(trim($state['name'] ?? ''));
                            $stateNameRawUpper = strtoupper(trim($stateNameRaw));
                            
                            if ($stateNameInFile === $stateNameRawUpper || 
                                stripos($stateNameInFile, $stateNameRawUpper) !== false ||
                                stripos($stateNameRawUpper, $stateNameInFile) !== false) {
                                $stateId = $state['id'] ?? null;
                                $stateNameFinal = $state['name'] ?? $stateName;
                                \Log::info('Found state in states.json: ID=' . $stateId . ', Name=' . $stateNameFinal);
                                break;
                            }
                        }
                    }
                }
            }
            
            // Look up city in cities.json to get city ID (match by districtName or taluk)
            $cityId = null;
            $cityNameFinal = $cityName;
            if ($cityName && $stateId) {
                $citiesPath = public_path('location-json/cities.json');
                if (file_exists($citiesPath)) {
                    // Read cities.json in chunks to find matching city
                    $handle = fopen($citiesPath, 'r');
                    if ($handle) {
                        $chunkSize = 512 * 1024; // 512KB chunks
                        $buffer = '';
                        $found = false;
                        
                        // Normalize city name for matching
                        $cityNameUpper = strtoupper(trim($cityName));
                        $districtNameUpper = strtoupper(trim($districtName));
                        $talukUpper = strtoupper(trim($taluk));
                        
                        while (!feof($handle) && !$found) {
                            $chunk = fread($handle, $chunkSize);
                            if ($chunk === false) break;
                            
                            $buffer .= $chunk;
                            
                            // Search for city with matching state_id and name
                            // Pattern: "state_id":"4022" and "name":"CityName"
                            $pattern = '/"state_id"\s*:\s*"' . preg_quote($stateId, '/') . '"[^}]*"name"\s*:\s*"([^"]+)"/';
                            if (preg_match_all($pattern, $buffer, $matches, PREG_SET_ORDER)) {
                                foreach ($matches as $match) {
                                    $matchedCityName = $match[1];
                                    $matchedCityNameUpper = strtoupper(trim($matchedCityName));
                                    
                                    // Match by districtName, taluk, or partial match
                                    if ($matchedCityNameUpper === $cityNameUpper ||
                                        $matchedCityNameUpper === $districtNameUpper ||
                                        $matchedCityNameUpper === $talukUpper ||
                                        stripos($matchedCityNameUpper, $cityNameUpper) !== false ||
                                        stripos($matchedCityNameUpper, $districtNameUpper) !== false ||
                                        stripos($matchedCityNameUpper, $talukUpper) !== false) {
                                        
                                        // Extract city ID from the match
                                        $cityPattern = '/"id"\s*:\s*"(\d+)"[^}]*"state_id"\s*:\s*"' . preg_quote($stateId, '/') . '"[^}]*"name"\s*:\s*"' . preg_quote($matchedCityName, '/') . '"/';
                                        if (preg_match($cityPattern, $buffer, $cityMatch)) {
                                            $cityId = $cityMatch[1];
                                            $cityNameFinal = $matchedCityName;
                                            $found = true;
                                            \Log::info('Found city in cities.json: ID=' . $cityId . ', Name=' . $cityNameFinal);
                                            break 2;
                                        }
                                    }
                                }
                            }
                            
                            // Keep last 50KB of buffer
                            if (strlen($buffer) > 51200) {
                                $buffer = substr($buffer, -51200);
                            }
                        }
                        
                        fclose($handle);
                    }
                }
            }
            
            // If city not found, use districtName or taluk as city name
            if (!$cityId) {
                $cityNameFinal = $cityName;
            }
            
            \Log::info('Location lookup result: City=' . $cityNameFinal . ', State=' . $stateNameFinal . ', Country=' . $countryName);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'pincode' => $pincode,
                    'city' => $cityNameFinal,
                    'state' => $stateNameFinal,
                    'country' => $countryName,
                    'city_id' => $cityId,
                    'state_id' => $stateId,
                    'country_id' => '101', // India's ID from countries.json
                    'districtName' => $districtName,
                    'taluk' => $taluk,
                    'officeName' => $foundPincode['officeName'] ?? '',
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching location by pincode: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching location data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveAddress(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        
        if (!$customer) {
            return redirect()->route('frontend.index')->with('error', 'Please login to save addresses.');
        }
        
        // Get fields for validation
        $addressFields = FieldManagement::where('field_group', 'address')
            ->active()
            ->visible()
            ->ordered()
            ->get();
        
        // Build validation rules
        $rules = [];
        foreach ($addressFields as $field) {
            $fieldRules = [];
            
            if ($field->is_required) {
                $fieldRules[] = 'required';
            }
            
            if ($field->validation_rules) {
                $fieldRules[] = $field->validation_rules;
            }
            
            if ($field->input_type === 'email') {
                $fieldRules[] = 'email';
            }
            
            if (!empty($fieldRules)) {
                $rules[$field->field_key] = implode('|', $fieldRules);
            }
        }
        
        // Validate request
        $validated = $request->validate($rules);
        
        try {
            // Prepare address data
            $addressData = [
                'customer_id' => $customer->id,
            ];
            
            // Map form fields to database columns
            foreach ($addressFields as $field) {
                $fieldKey = $field->field_key;
                $fieldValue = $request->input($fieldKey);
                
                // Handle different field mappings
                switch ($fieldKey) {
                    case 'address_type':
                    case 'address_line1':
                    case 'address_line2':
                    case 'landmark':
                    case 'country':
                    case 'state':
                    case 'city':
                    case 'pincode':
                    case 'delivery_instructions':
                        $addressData[$fieldKey] = $fieldValue ?: null;
                        break;
                    
                    case 'is_default':
                    case 'default':
                    case 'make_default_address':
                    case 'make_default':
                        // Handle default address checkbox - map to is_default column
                        $addressData['is_default'] = $request->has($fieldKey) && ($request->input($fieldKey) == '1' || $request->input($fieldKey) === true || $request->input($fieldKey) === 1) ? true : false;
                        break;
                    
                    case 'full_name':
                    case 'name':
                    case 'phone':
                    case 'email':
                    case 'alternate_phone':
                        // These fields might be stored separately or in a different table
                        // For now, we'll skip them or store in a JSON field if needed
                        break;
                    
                    default:
                        // For any other fields, try to map directly if column exists
                        if (in_array($fieldKey, ['address_type', 'address_line1', 'address_line2', 'landmark', 'country', 'state', 'city', 'pincode', 'delivery_instructions'])) {
                            $addressData[$fieldKey] = $fieldValue ?: null;
                        }
                        break;
                }
            }
            
            // Ensure required fields are set
            if (!isset($addressData['address_type'])) {
                $addressData['address_type'] = $request->input('address_type', 'home');
            }
            
            if (!isset($addressData['country'])) {
                $addressData['country'] = 'India'; // Default to India
            }
            
            // Check if this is an update (if address_id is provided)
            $addressId = $request->input('address_id');
            if ($addressId) {
                $address = CustomerAddress::where('customer_id', $customer->id)
                    ->where('id', $addressId)
                    ->first();
                
                if ($address) {
                    $address->update($addressData);
                    $message = 'Address updated successfully!';
                    
                    // If this address is set as default, unset all other default addresses
                    if (isset($addressData['is_default']) && $addressData['is_default']) {
                        CustomerAddress::where('customer_id', $customer->id)
                            ->where('id', '!=', $addressId)
                            ->update(['is_default' => false]);
                    }
                } else {
                    return redirect()->route('frontend.addresses')->with('error', 'Address not found.');
                }
            } else {
                // Create new address
                $newAddress = CustomerAddress::create($addressData);
                $message = 'Address saved successfully!';
                
                // If this address is set as default, unset all other default addresses
                if (isset($addressData['is_default']) && $addressData['is_default']) {
                    CustomerAddress::where('customer_id', $customer->id)
                        ->where('id', '!=', $newAddress->id)
                        ->update(['is_default' => false]);
                }
            }
            
            \Log::info('Address saved for customer ID: ' . $customer->id, $addressData);
            
            return redirect()->route('frontend.addresses')->with('success', $message);
            
        } catch (\Exception $e) {
            \Log::error('Error saving address: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->route('frontend.addresses')
                ->with('error', 'Error saving address: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Get address by ID for editing
     */
    public function getAddress($id)
    {
        try {
            $customer = Auth::guard('customer')->user();
            
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to view addresses.'
                ], 401);
            }
            
            $address = CustomerAddress::where('customer_id', $customer->id)
                ->where('id', $id)
                ->first();
            
            if (!$address) {
                return response()->json([
                    'success' => false,
                    'message' => 'Address not found.'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $address
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error getting address: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching address: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete address
     */
    public function deleteAddress($id)
    {
        try {
            $customer = Auth::guard('customer')->user();
            
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to delete addresses.'
                ], 401);
            }
            
            $address = CustomerAddress::where('customer_id', $customer->id)
                ->where('id', $id)
                ->first();
            
            if (!$address) {
                return response()->json([
                    'success' => false,
                    'message' => 'Address not found.'
                ], 404);
            }
            
            $address->delete();
            
            \Log::info('Address deleted: ' . $id . ' for customer: ' . $customer->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Address deleted successfully.'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error deleting address: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting address: ' . $e->getMessage()
            ], 500);
        }
    }

    public function paymentMethode(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        return view('frontend.payment-methode', compact('customer'));
    }

    public function shopingCart(Request $request)
    {
        // Get session ID or customer ID (similar to wishlist)
        $customer = $request->user();
        $customerId = ($customer && $customer instanceof \App\Models\Customer) ? $customer->id : null;
        
        // Get session_id from request (for guest users using localStorage)
        $sessionId = $request->input('session_id') 
                  ?? $request->query('session_id') 
                  ?? $request->header('X-Session-ID') 
                  ?? session()->getId();
        
        // Get or create cart
        $cart = \App\Models\Cart::where(function($query) use ($customerId, $sessionId) {
            if ($customerId) {
                $query->where('customer_id', $customerId);
            } else {
                $query->where('session_id', $sessionId);
            }
        })->active()->first();
        
        // If no cart exists, create an empty one for display
        if (!$cart) {
            $cart = new \App\Models\Cart([
                'session_id' => $customerId ? null : $sessionId,
                'customer_id' => $customerId,
                'subtotal' => 0,
                'tax_amount' => 0,
                'shipping_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
            ]);
            $cart->items = collect([]);
        } else {
            // Load cart items with relationships
            $cart->load([
                'items.product.primaryImage',
                'items.product.images' => function($q) {
                    $q->orderBy('sort_order')->orderBy('id')->limit(1);
                },
                'items.variant',
                'coupon'
            ]);
            
            // Recalculate cart totals manually
            $cart->load('items', 'coupon');
            $subtotal = $cart->items->sum('total_price');
            $cart->subtotal = $subtotal;
            
            // Calculate discount
            $discountAmount = 0;
            if ($cart->coupon_code && $cart->coupon) {
                $coupon = $cart->coupon;
                if ($coupon->discount_type === 'percentage') {
                    $discountAmount = ($subtotal * $coupon->discount_value) / 100;
                } else {
                    $discountAmount = min($coupon->discount_value, $subtotal);
                }
            }
            $cart->discount_amount = $discountAmount;
            
            // Calculate tax (0% for now - can be configured later)
            $taxRate = 0;
            $taxableAmount = $subtotal - $discountAmount;
            $taxAmount = $taxableAmount * $taxRate;
            $cart->tax_amount = $taxAmount;
            
            // Calculate shipping
            $allItemsFreeShipping = $cart->items->every(function($item) {
                return $item->product && $item->product->free_shipping;
            });
            
            $hasNonShippingItems = $cart->items->contains(function($item) {
                return $item->product && !$item->product->requires_shipping;
            });
            
            if ($allItemsFreeShipping || $hasNonShippingItems) {
                $shippingAmount = 0;
            } else {
                $freeShippingThreshold = 0;
                $defaultShippingCost = 0;
                $shippingAmount = $subtotal > $freeShippingThreshold ? 0 : $defaultShippingCost;
            }
            
            $cart->shipping_amount = $shippingAmount;
            
            // Calculate total
            $cart->total_amount = $subtotal - $discountAmount + $taxAmount + $shippingAmount;
            $cart->save();
        }
        
        return view('frontend.shoping-cart', [
            'cart' => $cart,
            'sessionId' => $sessionId,
        ]);
    }

    public function checkout(Request $request)
    {
        // Get customer (must be authenticated to checkout)
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return redirect()->route('frontend.shoping-cart')->with('error', 'Please login to proceed to checkout');
        }
        
        $customerId = $customer->id;
        
        // Get session_id
        $sessionId = $request->input('session_id') 
                  ?? $request->query('session_id') 
                  ?? $request->header('X-Session-ID') 
                  ?? session()->getId();
        
        // Get cart
        $cart = \App\Models\Cart::where(function($query) use ($customerId, $sessionId) {
            if ($customerId) {
                $query->where('customer_id', $customerId);
            } else {
                $query->where('session_id', $sessionId);
            }
        })->active()->with('items')->first();
        
        // If no cart or cart is empty, redirect to cart page
        if (!$cart || $cart->items->count() === 0) {
            return redirect()->route('frontend.shoping-cart')->with('error', 'Your cart is empty');
        }
        
        // Load cart items with relationships
        $cart->load([
            'items.product.primaryImage',
            'items.product.images' => function($q) {
                $q->orderBy('sort_order')->orderBy('id')->limit(1);
            },
            'items.product.category',
            'items.variant.images' => function($q) {
                $q->orderBy('sort_order')->orderBy('id')->limit(1);
            },
            'coupon'
        ]);
        
        // Recalculate cart totals
        $subtotal = $cart->items->sum('total_price');
        $cart->subtotal = $subtotal;
        
        // Calculate discount
        $discountAmount = 0;
        if ($cart->coupon_code && $cart->coupon) {
            $coupon = $cart->coupon;
            if ($coupon->discount_type === 'percentage') {
                $discountAmount = ($subtotal * $coupon->discount_value) / 100;
            } else {
                $discountAmount = min($coupon->discount_value, $subtotal);
            }
        }
        $cart->discount_amount = $discountAmount;
        
        // Calculate tax (0% for now - can be configured later)
        $taxRate = 0;
        $taxableAmount = $subtotal - $discountAmount;
        $taxAmount = $taxableAmount * $taxRate;
        $cart->tax_amount = $taxAmount;
        
        // Calculate shipping
        $allItemsFreeShipping = $cart->items->every(function($item) {
            return $item->product && $item->product->free_shipping;
        });
        
        $hasNonShippingItems = $cart->items->contains(function($item) {
            return $item->product && !$item->product->requires_shipping;
        });
        
        if ($allItemsFreeShipping || $hasNonShippingItems) {
            $shippingAmount = 0;
        } else {
            $freeShippingThreshold = 0;
            $defaultShippingCost = 0;
            $shippingAmount = $subtotal > $freeShippingThreshold ? 0 : $defaultShippingCost;
        }
        
        $cart->shipping_amount = $shippingAmount;
        
        // Calculate total
        $cart->total_amount = $subtotal - $discountAmount + $taxAmount + $shippingAmount;
        $cart->save();
        
        // Get customer's default address
        $defaultAddress = $customer->defaultAddress;
        
        return view('frontend.checkout', [
            'cart' => $cart,
            'customer' => $customer,
            'defaultAddress' => $defaultAddress,
            'sessionId' => $sessionId,
        ]);
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
