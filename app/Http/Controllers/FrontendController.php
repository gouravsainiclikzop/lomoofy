<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use App\Services\CheckoutService;
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
            
            // Get first variant image - use placeholder if no variant images
            $imageUrl = asset('assets/images/placeholder.jpg'); // Default placeholder
            $firstVariant = $activeVariants->first();
            if ($firstVariant && $firstVariant->images && $firstVariant->images->count() > 0) {
                $primaryVariantImage = $firstVariant->images->where('is_primary', true)->first();
                if ($primaryVariantImage) {
                    $imageUrl = asset('storage/' . $primaryVariantImage->image_path);
                } else {
                    $firstVariantImage = $firstVariant->images->first();
                    if ($firstVariantImage) {
                        $imageUrl = asset('storage/' . $firstVariantImage->image_path);
                    }
                }
            }
            
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
            $colorVariants = $activeVariants->filter(function($variant) {
                if (!$variant->attributes) {
                    return false;
                }
                $parsed = self::parseVariantAttributes($variant->attributes);
                // Has color if new format has color or old format has color key
                return $parsed['color'] !== null || isset($parsed['all']['color']);
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
                'color_variants' => $colorVariants->map(function($variant) use ($imageUrl, $activeVariants) {
                    // Parse attributes using new helper function
                    $parsed = self::parseVariantAttributes($variant->attributes);
                    
                    // Get color from new structured format
                    $colorValue = $parsed['color'] ? $parsed['color']['label'] : null;
                    $colorCode = $parsed['color'] ? $parsed['color']['code'] : '#ccc';
                    
                    // Get all variable attributes (size, material, pattern, etc.)
                    $variableAttributes = $parsed['variable'] ?? [];
                    
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
                    
                    // Get all variants with this color to find available variable attribute values
                    $variantsWithSameColor = $activeVariants->filter(function($v) use ($colorValue) {
                        if (!$v->attributes) return false;
                        $vParsed = self::parseVariantAttributes($v->attributes);
                        $vColorLabel = $vParsed['color'] ? $vParsed['color']['label'] : null;
                        return $vColorLabel === $colorValue;
                    });
                    
                    // Extract available values for each variable attribute
                    $availableVariableValues = [];
                    foreach ($variantsWithSameColor as $v) {
                        $vParsed = self::parseVariantAttributes($v->attributes);
                        foreach ($vParsed['variable'] ?? [] as $varKey => $varValue) {
                            if (!isset($availableVariableValues[$varKey])) {
                                $availableVariableValues[$varKey] = [];
                            }
                            if (!in_array($varValue, $availableVariableValues[$varKey])) {
                                $availableVariableValues[$varKey][] = $varValue;
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
                        'variable_attributes' => $variableAttributes, // All variable attributes for this variant
                        'available_variable_values' => $availableVariableValues, // All available values for each variable attribute
                        'images' => $variant->images->map(function($img) {
                            return [
                                'url' => asset('storage/' . $img->image_path),
                                'alt' => $img->alt_text ?? ''
                            ];
                        }),
                    ];
                })->unique('color')->values()->take(4),
                'variable_attributes' => $colorVariants->isEmpty() ? collect($activeVariants)->flatMap(function($variant) {
                    $parsed = self::parseVariantAttributes($variant->attributes);
                    return $parsed['variable'] ?? [];
                })->unique()->keys()->mapWithKeys(function($key) use ($activeVariants) {
                    $values = collect($activeVariants)->flatMap(function($variant) use ($key) {
                        $parsed = self::parseVariantAttributes($variant->attributes);
                        return [$parsed['variable'][$key] ?? null];
                    })->filter()->unique()->values()->toArray();
                    return [$key => $values];
                })->toArray() : [],
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
            // Check if there are any featured products first
            $hasFeaturedProducts = Product::where('status', 'published')
                ->where('featured', true)
                ->exists();
            
            if ($hasFeaturedProducts) {
                // Use featured products
                $bestSellersQuery->where('featured', true)
                    ->orderBy('created_at', 'desc');
            } else {
                // Fall back to recently added published products if no featured products
                $bestSellersQuery->orderBy('created_at', 'desc');
            }
        }
        
        $bestSellers = $bestSellersQuery->limit(8)->get()
        ->map(function($product) use ($wishlistProductIds) {
            // Get price range from variants
            $activeVariants = $product->variants->where('is_active', true);
            
            // Handle products with no variants
            if ($activeVariants->isEmpty()) {
                $minPrice = 0;
                $maxPrice = 0;
                $minDisplayPrice = 0;
                $maxDisplayPrice = 0;
                $minSalePrice = null;
                $maxSalePrice = null;
                $hasSale = false;
            } else {
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
                $hasSale = $minSalePrice && $minPrice > 0 && $minSalePrice < $minPrice;
            }
            
            // Get variant image - use placeholder if no variant images
            $imageUrl = asset('assets/images/placeholder.jpg'); // Default placeholder
            $firstVariant = $activeVariants->first();
            if ($firstVariant && $firstVariant->images && $firstVariant->images->count() > 0) {
                $primaryVariantImage = $firstVariant->images->where('is_primary', true)->first();
                if ($primaryVariantImage) {
                    $imageUrl = asset('storage/' . $primaryVariantImage->image_path);
                } else {
                    $firstVariantImage = $firstVariant->images->first();
                    if ($firstVariantImage) {
                        $imageUrl = asset('storage/' . $firstVariantImage->image_path);
                    }
                }
            }
            
            // Format price display
            $priceDisplay = '';
            if ($minPrice > 0 || $minDisplayPrice > 0) {
                if ($hasSale && $minSalePrice) {
                    $priceDisplay = '₹' . number_format($minSalePrice, 0);
                    if ($maxSalePrice && $minSalePrice != $maxSalePrice) {
                        $priceDisplay .= ' - ₹' . number_format($maxSalePrice, 0);
                    }
                } else {
                    if ($minPrice != $maxPrice && $maxPrice > 0) {
                        $priceDisplay = '₹' . number_format($minPrice, 0) . ' - ₹' . number_format($maxPrice, 0);
                    } else {
                        $priceDisplay = '₹' . number_format($minPrice > 0 ? $minPrice : $minDisplayPrice, 0);
                    }
                }
            } else {
                $priceDisplay = 'Price on request';
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
                
                // Get variant image - use placeholder if no variant images
                $imageUrl = asset('assets/images/placeholder.jpg'); // Default placeholder
                $firstVariant = $activeVariants->first();
                if ($firstVariant && $firstVariant->images && $firstVariant->images->count() > 0) {
                    $primaryVariantImage = $firstVariant->images->where('is_primary', true)->first();
                    if ($primaryVariantImage) {
                        $imageUrl = asset('storage/' . $primaryVariantImage->image_path);
                    } else {
                        $firstVariantImage = $firstVariant->images->first();
                        if ($firstVariantImage) {
                            $imageUrl = asset('storage/' . $firstVariantImage->image_path);
                        }
                    }
                }
                
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
        
        // Get or create session_id for wishlist
        $sessionId = $request->input('session_id') 
                  ?? $request->query('session_id') 
                  ?? $request->header('X-Session-ID')
                  ?? $request->cookie('wishlist_session_id');
        
        // If no session_id exists, check if user has any wishlist items and use that session_id
        $sessionIdGenerated = false;
        if (!$sessionId && !$customerId) {
            // Check if there are any wishlist items for this user (guest) with any session_id
            // This handles the case where wishlist items exist but cookie doesn't
            $existingWishlist = \App\Models\Wishlist::whereNull('customer_id')
                ->whereNotNull('session_id')
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($existingWishlist && $existingWishlist->session_id) {
                // Use the existing session_id from wishlist
                $sessionId = $existingWishlist->session_id;
            } else {
                // Generate new session_id only if no existing wishlist found
                $sessionId = 'session_' . time() . '_' . substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 9);
                $sessionIdGenerated = true; // Flag to set cookie in response
            }
        } elseif (!$sessionId) {
            // For logged-in users, we don't need session_id (uses customer_id)
            $sessionId = null;
        }
        
        // Fallback to Laravel session ID only if we still don't have one and user is not logged in (shouldn't happen)
        if (!$sessionId && !$customerId) {
            $sessionId = session()->getId();
        }
        
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
                  ->orWhere('short_description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('slug', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Filter by price range (check display price: sale_price if available and less than regular, otherwise regular price)
        if ($request->has('min_price') || $request->has('max_price')) {
            $minPrice = $request->has('min_price') ? (float)$request->min_price : null;
            $maxPrice = $request->has('max_price') ? (float)$request->max_price : null;
            
            // Apply filter if at least one price parameter is provided
            if ($minPrice !== null || $maxPrice !== null) { 
                
                // Use raw SQL to calculate display price and filter
                // Display price = CASE WHEN sale_price IS NOT NULL AND sale_price > 0 AND sale_price < price THEN sale_price ELSE price END
                $productsQuery->whereHas('variants', function($q) use ($minPrice, $maxPrice) {
                    $q->where('is_active', true);
                    
                    // Build the WHERE clause with raw SQL for display price calculation
                    $conditions = [];
                    $bindings = [];
                    
                    if ($minPrice !== null) {
                        $conditions[] = '(CASE 
                            WHEN sale_price IS NOT NULL AND sale_price > 0 AND sale_price < price THEN sale_price 
                            ELSE price 
                        END) >= ?';
                        $bindings[] = $minPrice;
                    }
                    
                    if ($maxPrice !== null) {
                        $conditions[] = '(CASE 
                            WHEN sale_price IS NOT NULL AND sale_price > 0 AND sale_price < price THEN sale_price 
                            ELSE price 
                        END) <= ?';
                        $bindings[] = $maxPrice;
                    }
                    
                    if (!empty($conditions)) {
                        $q->whereRaw(implode(' AND ', $conditions), $bindings);
                    }
                });
            }
        }
        
        // Filter by sizes
        if ($request->has('sizes') && is_array($request->sizes) && count($request->sizes) > 0) {
            $sizes = array_filter($request->sizes);
            if (count($sizes) > 0) {
                $productsQuery->whereHas('variants', function($q) use ($sizes) {
                    $q->where('is_active', true)
                      ->where(function($sizeQ) use ($sizes) {
                          foreach ($sizes as $size) {
                              $sizeQ->orWhereJsonContains('attributes->size', $size)
                                    ->orWhereJsonContains('attributes->Size', $size)
                                    ->orWhere('attributes', 'like', '%"' . $size . '"%');
                          }
                      });
                });
            }
        }
        
        // Filter by brands
        if ($request->has('brands') && is_array($request->brands) && count($request->brands) > 0) {
            $brandIds = array_filter($request->brands);
            if (count($brandIds) > 0) {
                // Convert to integers to ensure proper matching
                $brandIds = array_map('intval', $brandIds);
                $brandIds = array_filter($brandIds); // Remove any invalid values
                if (count($brandIds) > 0) {
                    $productsQuery->whereIn('brand_id', $brandIds);
                }
            }
        }
        
        // Filter by colors (if implemented in variants)
        if ($request->has('colors') && is_array($request->colors) && count($request->colors) > 0) {
            $colors = array_filter($request->colors);
            if (count($colors) > 0) {
                // Get color attribute to check by ID if it exists
                $colorAttribute = \App\Models\ProductAttribute::where(function($q) {
                    $q->where('type', 'color')
                      ->orWhere('slug', 'color')
                      ->orWhere('name', 'Color');
                })->first();
                
                $productsQuery->whereHas('variants', function($q) use ($colors, $colorAttribute) {
                    $q->where('is_active', true)
                      ->where(function($colorQ) use ($colors, $colorAttribute) {
                          foreach ($colors as $color) {
                              $colorLower = strtolower(trim($color));
                              $colorUpper = ucfirst($colorLower);
                              
                              $colorQ->orWhere(function($cq) use ($colorLower, $colorUpper, $color, $colorAttribute) {
                                  // Method 1: Check new structured format: attributes->color->label
                                  // Structure: {"color":{"label":"red","code":"#FF0000"}}
                                  $cq->whereJsonContains('attributes->color->label', $colorLower)
                                     ->orWhereJsonContains('attributes->color->label', $colorUpper)
                                     ->orWhereJsonContains('attributes->color->label', $color)
                                     ->orWhereJsonContains('attributes->Color->label', $colorLower)
                                     ->orWhereJsonContains('attributes->Color->label', $colorUpper)
                                     ->orWhereJsonContains('attributes->Color->label', $color);
                                  
                                  // Method 2: Case-insensitive JSON search for color->label using JSON_UNQUOTE
                                  $cq->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(attributes, "$.color.label"))) = ?', [$colorLower])
                                     ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(attributes, "$.Color.label"))) = ?', [$colorLower]);
                                  
                                  // Method 3: Like search for new format: "color":{"label":"red"
                                  $cq->orWhere('attributes', 'like', '%"color":{"label":"' . $colorLower . '"%')
                                     ->orWhere('attributes', 'like', '%"Color":{"label":"' . $colorLower . '"%')
                                     ->orWhere('attributes', 'like', '%"color":{"label":"' . $colorUpper . '"%')
                                     ->orWhere('attributes', 'like', '%"Color":{"label":"' . $colorUpper . '"%')
                                     ->orWhere('attributes', 'like', '%"color":{%"label":"' . $colorLower . '"%')
                                     ->orWhere('attributes', 'like', '%"Color":{%"label":"' . $colorLower . '"%')
                                     ->orWhere('attributes', 'like', '%"color":{%"label":"' . $colorUpper . '"%')
                                     ->orWhere('attributes', 'like', '%"Color":{%"label":"' . $colorUpper . '"%');
                                  
                                  // Method 4: Check by attribute ID (if color attribute exists) - old format
                                  // Colors might be stored as: {"1": "red"} where 1 is the color attribute ID
                                  if ($colorAttribute) {
                                      $cq->orWhereJsonContains('attributes->' . $colorAttribute->id, $colorLower)
                                         ->orWhereJsonContains('attributes->' . $colorAttribute->id, $colorUpper)
                                         ->orWhereJsonContains('attributes->' . $colorAttribute->id, $color);
                                  }
                                  
                                  // Method 5: Check old format - direct color string (backward compatibility)
                                  // Colors might be stored as: {"color": "red"} or {"Color": "red"}
                                  $cq->orWhereJsonContains('attributes->color', $colorLower)
                                     ->orWhereJsonContains('attributes->Color', $colorLower)
                                     ->orWhereJsonContains('attributes->COLOR', $colorLower)
                                     ->orWhereJsonContains('attributes->color', $colorUpper)
                                     ->orWhereJsonContains('attributes->Color', $colorUpper)
                                     ->orWhereJsonContains('attributes->COLOR', $colorUpper)
                                     ->orWhereJsonContains('attributes->color', $color)
                                     ->orWhereJsonContains('attributes->Color', $color)
                                     ->orWhereJsonContains('attributes->COLOR', $color);
                                  
                                  // Method 6: Case-insensitive JSON search for direct color (old format)
                                  $cq->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(attributes, "$.color"))) = ?', [$colorLower])
                                     ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(attributes, "$.Color"))) = ?', [$colorLower]);
                                  
                                  // Method 7: Like search for old format: "color":"red"
                                  $cq->orWhere('attributes', 'like', '%"color":"' . $colorLower . '"%')
                                     ->orWhere('attributes', 'like', '%"Color":"' . $colorLower . '"%')
                                     ->orWhere('attributes', 'like', '%"color":"' . $colorUpper . '"%')
                                     ->orWhere('attributes', 'like', '%"Color":"' . $colorUpper . '"%');
                                  
                                  // Method 8: If color attribute exists, also check by ID in JSON (old format)
                                  if ($colorAttribute) {
                                      $cq->orWhere('attributes', 'like', '%"' . $colorAttribute->id . '":"' . $colorLower . '"%')
                                         ->orWhere('attributes', 'like', '%"' . $colorAttribute->id . '":"' . $colorUpper . '"%');
                                  }
                              });
                          }
                      });
                });
            }
        }
        
        // Get total count before limiting
        $totalProducts = $productsQuery->count();
        
        
        
        // Get sort parameter
        $sort = $request->input('sort', '1');
        
        // Apply initial ordering based on sort type
        // Note: Price sorting will be done after mapping since we need display prices
        switch ($sort) {
            case '2': // Price: Low to High - will sort after mapping
            case '3': // Price: High to Low - will sort after mapping
                // Get all products first (we'll limit after sorting)
                $productsQuery = $productsQuery->orderBy('created_at', 'desc');
                break;
            case '4': // Rating - featured first, then by created_at
            case '5': // Trending - featured first, then by created_at
                $productsQuery = $productsQuery->orderBy('featured', 'desc')
                    ->orderBy('created_at', 'desc');
                break;
            default: // Default: created_at desc
                $productsQuery = $productsQuery->orderBy('created_at', 'desc');
                break;
        }
        
        // Get products (get more than 20 if we need to sort by price, then limit after)
        $productsToMap = ($sort == '2' || $sort == '3') 
            ? $productsQuery->get() 
            : $productsQuery->take(20)->get();
        
        // If we don't have wishlist items but have products, check if any products are in wishlist with different session_id
        // This handles the case where cookie doesn't exist or doesn't match the session_id used to store wishlist items
        if (empty($wishlistProductIds) && !$customerId && $productsToMap->count() > 0 && $sessionId) {
            $productIds = $productsToMap->pluck('id')->toArray();
            // Check if any of these products are in wishlist with any session_id (not just current one)
            $wishlistItems = \App\Models\Wishlist::whereIn('product_id', $productIds)
                ->whereNull('customer_id')
                ->whereNotNull('session_id')
                ->where('session_id', '!=', $sessionId) // Different from current session_id
                ->get();
            
            if ($wishlistItems->count() > 0) {
                // Use the most common session_id from these wishlist items
                $sessionIdCounts = $wishlistItems->groupBy('session_id')->map->count();
                $mostCommonSessionId = $sessionIdCounts->sortDesc()->keys()->first();
                
                if ($mostCommonSessionId) {
                    $sessionId = $mostCommonSessionId;
                    $sessionIdGenerated = true; // Set cookie so it persists
                    // Re-fetch wishlist product IDs with the correct session_id
                    $wishlistProductIds = \App\Models\Wishlist::where('session_id', $sessionId)
                        ->pluck('product_id')->toArray();
                }
            }
        } elseif (empty($wishlistProductIds) && !$customerId && $productsToMap->count() > 0 && !$sessionId) {
            // No session_id at all - check if any products are in wishlist
            $productIds = $productsToMap->pluck('id')->toArray();
            $wishlistItems = \App\Models\Wishlist::whereIn('product_id', $productIds)
                ->whereNull('customer_id')
                ->whereNotNull('session_id')
                ->get();
            
            if ($wishlistItems->count() > 0) {
                // Use the most common session_id from these wishlist items
                $sessionIdCounts = $wishlistItems->groupBy('session_id')->map->count();
                $mostCommonSessionId = $sessionIdCounts->sortDesc()->keys()->first();
                
                if ($mostCommonSessionId) {
                    $sessionId = $mostCommonSessionId;
                    $sessionIdGenerated = true; // Set cookie so it persists
                    // Re-fetch wishlist product IDs with the correct session_id
                    $wishlistProductIds = \App\Models\Wishlist::where('session_id', $sessionId)
                        ->pluck('product_id')->toArray();
                }
            }
        }
        
        // Map products to array format
        $products = $productsToMap->map(function($product) use ($wishlistProductIds) {
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
                
                // Get first variant image - use placeholder if no variant images (same as new arrivals)
                $imageUrl = asset('assets/images/placeholder.jpg'); // Default placeholder
                $firstVariant = $activeVariants->first();
                if ($firstVariant && $firstVariant->images && $firstVariant->images->count() > 0) {
                    $primaryVariantImage = $firstVariant->images->where('is_primary', true)->first();
                    if ($primaryVariantImage) {
                        $imageUrl = asset('storage/' . $primaryVariantImage->image_path);
                    } else {
                        $firstVariantImage = $firstVariant->images->first();
                        if ($firstVariantImage) {
                            $imageUrl = asset('storage/' . $firstVariantImage->image_path);
                        }
                    }
                }
                
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
                    'color_variants' => $colorVariants->map(function($variant) use ($imageUrl, $activeVariants) {
                        // Parse attributes using new helper function
                        $parsed = self::parseVariantAttributes($variant->attributes);
                        
                        // Get color from new structured format
                        $colorValue = $parsed['color'] ? $parsed['color']['label'] : null;
                        $colorCode = $parsed['color'] ? $parsed['color']['code'] : '#ccc';
                        
                        // Get all variable attributes (size, material, pattern, etc.)
                        $variableAttributes = $parsed['variable'] ?? [];
                        
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
                        
                        // Get all variants with this color to find available variable attribute values
                        $variantsWithSameColor = $activeVariants->filter(function($v) use ($colorValue) {
                            if (!$v->attributes) return false;
                            $vParsed = self::parseVariantAttributes($v->attributes);
                            $vColorLabel = $vParsed['color'] ? $vParsed['color']['label'] : null;
                            return $vColorLabel === $colorValue;
                        });
                        
                        // Extract available values for each variable attribute
                        $availableVariableValues = [];
                        foreach ($variantsWithSameColor as $v) {
                            $vParsed = self::parseVariantAttributes($v->attributes);
                            foreach ($vParsed['variable'] ?? [] as $varKey => $varValue) {
                                if (!isset($availableVariableValues[$varKey])) {
                                    $availableVariableValues[$varKey] = [];
                                }
                                if (!in_array($varValue, $availableVariableValues[$varKey])) {
                                    $availableVariableValues[$varKey][] = $varValue;
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
                            'variable_attributes' => $variableAttributes, // All variable attributes for this variant
                            'available_variable_values' => $availableVariableValues, // All available values for each variable attribute
                            'images' => $variant->images->map(function($img) {
                                return [
                                    'url' => asset('storage/' . $img->image_path),
                                    'alt' => $img->alt_text ?? ''
                                ];
                            }),
                        ];
                    })->unique('color')->values()->take(4),
                    'variable_attributes' => $colorVariants->isEmpty() ? collect($activeVariants)->flatMap(function($variant) {
                        $parsed = self::parseVariantAttributes($variant->attributes);
                        return $parsed['variable'] ?? [];
                    })->unique()->keys()->mapWithKeys(function($key) use ($activeVariants) {
                        $values = collect($activeVariants)->flatMap(function($variant) use ($key) {
                            $parsed = self::parseVariantAttributes($variant->attributes);
                            return [$parsed['variable'][$key] ?? null];
                        })->filter()->unique()->values()->toArray();
                        return [$key => $values];
                    })->toArray() : [],
                ];
            });
        
        // Apply sorting based on sort parameter
        switch ($sort) {
            case '2': // Price: Low to High
                $products = $products->sortBy(function($product) {
                    return $product['min_display_price'] ?? $product['min_price'] ?? 0;
                })->values();
                break;
            case '3': // Price: High to Low
                $products = $products->sortByDesc(function($product) {
                    return $product['max_display_price'] ?? $product['max_price'] ?? 0;
                })->values();
                break;
            case '4': // Rating - already sorted by featured/created_at in query
            case '5': // Trending - already sorted by featured/created_at in query
                // Already sorted in query, no additional sorting needed
                break;
            default: // Default - already sorted by created_at in query
                break;
        }
        
        // Limit to 20 products after sorting (if we got more than 20)
        if ($sort == '2' || $sort == '3') {
            $products = $products->take(20);
        }
        
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
                $parsed = self::parseVariantAttributes($variant->attributes);
                // Get size from variable attributes
                if (isset($parsed['variable']['size'])) {
                    $sizeValues->push($parsed['variable']['size']);
                }
                // Fallback: check old format
                elseif (isset($parsed['all']['size'])) {
                    $sizeValues->push($parsed['all']['size']);
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
        
        // Get available colors from product variants with color codes
        $colorAttribute = ProductAttribute::where(function($q) {
            $q->where('type', 'color')
              ->orWhere('slug', 'color')
              ->orWhere('name', 'Color');
        })->first();
        
        $availableColors = collect();
        if ($colorAttribute) {
            // Get all color values from variants attributes
            $variants = DB::table('product_variants')
                ->join('products', 'product_variants.product_id', '=', 'products.id')
                ->where('product_variants.is_active', true)
                ->where('products.status', 'published')
                ->select('product_variants.attributes')
                ->get();
            
            $colorValuesMap = [];
            foreach ($variants as $variant) {
                $parsed = self::parseVariantAttributes($variant->attributes);
                
                // Get color from new structured format
                if ($parsed['color'] && $parsed['color']['label']) {
                    $colorValue = trim($parsed['color']['label']);
                    $colorCode = $parsed['color']['code'] ?? '#ccc';
                    
                    if ($colorValue && !isset($colorValuesMap[$colorValue])) {
                        $colorValuesMap[$colorValue] = [
                            'name' => $colorValue,
                            'code' => $colorCode,
                            'id' => strtolower(str_replace(' ', '', $colorValue)) . 'a8'
                        ];
                    }
                }
                // Fallback: check old format
                elseif (isset($parsed['all']['color'])) {
                    $colorValue = trim($parsed['all']['color']);
                    if ($colorValue && !isset($colorValuesMap[$colorValue])) {
                        $colorCode = self::getColorCodeFromName($colorValue);
                        $colorValuesMap[$colorValue] = [
                            'name' => $colorValue,
                            'code' => $colorCode,
                            'id' => strtolower(str_replace(' ', '', $colorValue)) . 'a8'
                        ];
                    }
                }
            }
            
            // Convert to collection and sort
            $availableColors = collect($colorValuesMap)->values()->sortBy('name');
        }
        
        // Get brands with product counts (respecting current filters except brand filter)
        // Build a base query that matches current filters (category, sizes, colors, price) but excludes brand filter
        $brandCountQuery = Product::where('status', 'published');
        
        // Apply category filter if selected
        if ($selectedCategory) {
            $categoryIds = [$selectedCategory->id];
            $categoryIds = array_merge($categoryIds, $selectedCategory->getDescendantIds());
            $brandCountQuery->where(function($query) use ($categoryIds) {
                $query->whereIn('category_id', $categoryIds)
                      ->orWhereHas('categories', function($q) use ($categoryIds) {
                          $q->whereIn('categories.id', $categoryIds);
                      });
            });
        }
        
        // Apply search filter if provided
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $brandCountQuery->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('short_description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('slug', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Apply price filter if provided
        if ($request->has('min_price') || $request->has('max_price')) {
            $minPrice = $request->input('min_price');
            $maxPrice = $request->input('max_price');
            
            $brandCountQuery->whereHas('variants', function($q) use ($minPrice, $maxPrice) {
                $q->where('is_active', true);
                if ($minPrice !== null) {
                    $q->where(function($priceQ) use ($minPrice) {
                        $priceQ->whereRaw('COALESCE(sale_price, price) >= ?', [$minPrice]);
                    });
                }
                if ($maxPrice !== null) {
                    $q->where(function($priceQ) use ($maxPrice) {
                        $priceQ->whereRaw('COALESCE(sale_price, price) <= ?', [$maxPrice]);
                    });
                }
            });
        }
        
        // Apply size filter if provided
        if ($request->has('sizes') && is_array($request->sizes) && count($request->sizes) > 0) {
            $sizes = array_filter($request->sizes);
            if (count($sizes) > 0) {
                $brandCountQuery->whereHas('variants', function($q) use ($sizes) {
                    $q->where('is_active', true)
                      ->where(function($sizeQ) use ($sizes) {
                          foreach ($sizes as $size) {
                              $sizeQ->orWhereJsonContains('attributes->variable->size', $size)
                                    ->orWhereJsonContains('attributes->size', $size)
                                    ->orWhereJsonContains('attributes->Size', $size)
                                    ->orWhere('attributes', 'like', '%"' . $size . '"%');
                          }
                      });
                });
            }
        }
        
        // Apply color filter if provided (but not brand filter)
        if ($request->has('colors') && is_array($request->colors) && count($request->colors) > 0) {
            $colors = array_filter($request->colors);
            if (count($colors) > 0) {
                $colorAttribute = \App\Models\ProductAttribute::where(function($q) {
                    $q->where('type', 'color')
                      ->orWhere('slug', 'color')
                      ->orWhere('name', 'Color');
                })->first();
                
                $brandCountQuery->whereHas('variants', function($q) use ($colors, $colorAttribute) {
                    $q->where('is_active', true)
                      ->where(function($colorQ) use ($colors, $colorAttribute) {
                          foreach ($colors as $color) {
                              $colorLower = strtolower(trim($color));
                              $colorUpper = ucfirst($colorLower);
                              
                              $colorQ->orWhere(function($cq) use ($colorLower, $colorUpper, $color, $colorAttribute) {
                                  // Check new structured format: attributes->color->label
                                  $cq->whereJsonContains('attributes->color->label', $colorLower)
                                     ->orWhereJsonContains('attributes->color->label', $colorUpper)
                                     ->orWhereJsonContains('attributes->color->label', $color)
                                     ->orWhereJsonContains('attributes->Color->label', $colorLower)
                                     ->orWhereJsonContains('attributes->Color->label', $colorUpper)
                                     ->orWhereJsonContains('attributes->Color->label', $color)
                                     ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(attributes, "$.color.label"))) = ?', [$colorLower])
                                     ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(attributes, "$.Color.label"))) = ?', [$colorLower])
                                     ->orWhere('attributes', 'like', '%"color":{"label":"' . $colorLower . '"%')
                                     ->orWhere('attributes', 'like', '%"Color":{"label":"' . $colorLower . '"%')
                                     ->orWhere('attributes', 'like', '%"color":{"label":"' . $colorUpper . '"%')
                                     ->orWhere('attributes', 'like', '%"Color":{"label":"' . $colorUpper . '"%')
                                     ->orWhereJsonContains('attributes->color', $colorLower)
                                     ->orWhereJsonContains('attributes->Color', $colorLower)
                                     ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(attributes, "$.color"))) = ?', [$colorLower])
                                     ->orWhere('attributes', 'like', '%"color":"' . $colorLower . '"%');
                              });
                          }
                      });
                });
            }
        }
        
        // Get brands with product counts (using filtered query)
        $brands = \App\Models\Brand::where('is_active', true)
            ->where('slug', '!=', 'other')
            ->get()
            ->map(function($brand) use ($brandCountQuery) {
                // Count products for this brand using the filtered query
                $count = (clone $brandCountQuery)
                    ->where('brand_id', $brand->id)
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
        
        $view = view('frontend.shop', compact('selectedCategory', 'childCategories', 'parentCategories', 'breadcrumb', 'products', 'totalProducts', 'hasMoreProducts', 'currentPage', 'minPrice', 'maxPrice', 'availableSizes', 'availableColors', 'brands'));
        
        // If we generated a new session_id, set it as a cookie so JavaScript can sync it
        if ($sessionIdGenerated) {
            return response($view)->cookie('wishlist_session_id', $sessionId, 525600); // 1 year in minutes
        }
        
        return $view;
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
        
        // Get or create session_id for wishlist (same logic as shop method)
        $sessionId = $request->input('session_id') 
                  ?? $request->query('session_id') 
                  ?? $request->header('X-Session-ID')
                  ?? $request->cookie('wishlist_session_id');
        
        // If no session_id exists, generate one (matches JavaScript format)
        if (!$sessionId) {
            $sessionId = 'session_' . time() . '_' . substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 9);
        }
        
        // Fallback to Laravel session ID only if we still don't have one (shouldn't happen)
        if (!$sessionId) {
            $sessionId = session()->getId();
        }
        
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
                  ->orWhere('short_description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('slug', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Get total count
        $totalProducts = $productsQuery->count();
        
        // Get products for this page
        $productsToMap = $productsQuery->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($perPage)
            ->get();
        
        // If we don't have wishlist items but have products, check if any products are in wishlist with different session_id
        // This handles the case where cookie doesn't exist or doesn't match the session_id used to store wishlist items
        if (empty($wishlistProductIds) && !$customerId && $productsToMap->count() > 0 && $sessionId) {
            $productIds = $productsToMap->pluck('id')->toArray();
            $wishlistItems = \App\Models\Wishlist::whereIn('product_id', $productIds)
                ->whereNull('customer_id')
                ->whereNotNull('session_id')
                ->where('session_id', '!=', $sessionId)
                ->get();
            
            if ($wishlistItems->count() > 0) {
                $sessionIdCounts = $wishlistItems->groupBy('session_id')->map->count();
                $mostCommonSessionId = $sessionIdCounts->sortDesc()->keys()->first();
                
                if ($mostCommonSessionId) {
                    $sessionId = $mostCommonSessionId;
                    $wishlistProductIds = \App\Models\Wishlist::where('session_id', $sessionId)
                        ->pluck('product_id')->toArray();
                }
            }
        } elseif (empty($wishlistProductIds) && !$customerId && $productsToMap->count() > 0 && !$sessionId) {
            $productIds = $productsToMap->pluck('id')->toArray();
            $wishlistItems = \App\Models\Wishlist::whereIn('product_id', $productIds)
                ->whereNull('customer_id')
                ->whereNotNull('session_id')
                ->get();
            
            if ($wishlistItems->count() > 0) {
                $sessionIdCounts = $wishlistItems->groupBy('session_id')->map->count();
                $mostCommonSessionId = $sessionIdCounts->sortDesc()->keys()->first();
                
                if ($mostCommonSessionId) {
                    $sessionId = $mostCommonSessionId;
                    $wishlistProductIds = \App\Models\Wishlist::where('session_id', $sessionId)
                        ->pluck('product_id')->toArray();
                }
            }
        }
        
        // Map products to array format
        $products = $productsToMap->map(function($product) use ($wishlistProductIds) {
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
                
                // Get variant image first, then placeholder (no product image fallback)
                $imageUrl = asset('assets/images/placeholder.jpg'); // Default placeholder
                
                // Try to get first variant image
                $firstVariant = $activeVariants->first();
                if ($firstVariant && $firstVariant->images && $firstVariant->images->count() > 0) {
                    $primaryVariantImage = $firstVariant->images->where('is_primary', true)->first();
                    if ($primaryVariantImage) {
                        $imageUrl = asset('storage/' . $primaryVariantImage->image_path);
                    } else {
                        $firstVariantImage = $firstVariant->images->first();
                        if ($firstVariantImage) {
                            $imageUrl = asset('storage/' . $firstVariantImage->image_path);
                        }
                    }
                }
                
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
                    'color_variants' => $colorVariants->map(function($variant) use ($imageUrl) {
                        // Parse attributes using new helper function
                        $parsed = self::parseVariantAttributes($variant->attributes);
                        
                        // Get color from new structured format
                        $colorValue = $parsed['color'] ? $parsed['color']['label'] : null;
                        $colorCode = $parsed['color'] ? $parsed['color']['code'] : '#ccc';
                        
                        // Get variant image only - use placeholder if no variant image
                        $variantImage = asset('assets/images/placeholder.jpg'); // Default placeholder
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
                            'discount_type' => $variant->discount_type ?? null,
                            'discount_value' => $variant->discount_value ?? null,
                            'discount_active' => $variant->discount_active ?? false,
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
                  ->with([
                      'images' => function($imgQ) {
                          $imgQ->orderBy('sort_order')->orderBy('id');
                      },
                      'inventoryStocks'
                  ]);
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
        
        // Helper function to calculate GST-inclusive price
        $calculateGstInclusivePrice = function($basePrice, $gstType, $gstPercentage) {
            if ($basePrice <= 0) return 0;
            
            // If gst_type is true, price is already inclusive
            if ($gstType === true || $gstType === 1 || $gstType === '1') {
                return $basePrice;
            }
            
            // If gst_type is false, add GST
            if ($gstType === false || $gstType === 0 || $gstType === '0') {
                $gstPercent = $gstPercentage ?? 0;
                if ($gstPercent > 0) {
                    return $basePrice + ($basePrice * $gstPercent / 100);
                }
            }
            
            return $basePrice;
        };
        
        // Get GST settings from product
        $gstType = $product->gst_type ?? true; // Default to inclusive
        $gstPercentage = $product->gst_percentage ?? 0;
        
        // Get price range with GST applied
        $prices = $activeVariants->map(function($variant) use ($calculateGstInclusivePrice, $gstType, $gstPercentage) {
            $basePrice = $variant->price ?? 0;
            return $calculateGstInclusivePrice($basePrice, $gstType, $gstPercentage);
        })->filter();
        
        $salePrices = $activeVariants->map(function($variant) use ($calculateGstInclusivePrice, $gstType, $gstPercentage) {
            $baseSalePrice = $variant->sale_price;
            if ($baseSalePrice && $baseSalePrice > 0) {
                return $calculateGstInclusivePrice($baseSalePrice, $gstType, $gstPercentage);
            }
            return null;
        })->filter();
        
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
                    'url' => asset('assets/images/placeholder.jpg'),
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
            // Parse attributes using new helper function
            $parsed = self::parseVariantAttributes($variant->attributes);
            
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
            
            // Get variant image only - use placeholder if no variant image
            $variantImage = asset('assets/images/placeholder.jpg'); // Default placeholder
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
            
            $basePrice = $variant->price ?? 0;
            $baseSalePrice = $variant->sale_price;
            
            // Calculate GST-inclusive prices
            $price = $calculateGstInclusivePrice($basePrice, $gstType, $gstPercentage);
            $salePrice = $baseSalePrice ? $calculateGstInclusivePrice($baseSalePrice, $gstType, $gstPercentage) : null;
            $hasVariantSale = $salePrice && $salePrice < $price;
            
            // Extract color from new structured format
            if ($parsed['color']) {
                $colorValue = $parsed['color']['label'];
                $colorCode = $parsed['color']['code'];
                
                // Populate color arrays
                if (!isset($colorVariantsMap[$colorValue])) {
                    $colors[] = $colorValue;
                    $colorVariantsMap[$colorValue] = [
                        'color' => $colorValue,
                        'color_code' => $colorCode,
                        'image' => $variantImage,
                        'images' => !empty($variantImages) ? $variantImages : [],
                        'price' => $price,
                        'sale_price' => $salePrice,
                        'has_sale' => $hasVariantSale,
                        'display_price' => $hasVariantSale ? $salePrice : $price,
                    ];
                }
                
                // Find color attribute to add to attributeValues
                $colorAttribute = $allVariantAttributes->first(function($attr) {
                    return $attr->type === 'color' || strtolower($attr->name) === 'color' || strtolower($attr->slug) === 'color';
                });
                
                if ($colorAttribute) {
                    if (!isset($attributeValues[$colorAttribute->id])) {
                        $attributeValues[$colorAttribute->id] = [];
                    }
                    if (!isset($attributeVariantsMap[$colorAttribute->id])) {
                        $attributeVariantsMap[$colorAttribute->id] = [];
                    }
                    if (!in_array($colorValue, $attributeValues[$colorAttribute->id])) {
                        $attributeValues[$colorAttribute->id][] = $colorValue;
                    }
                    if (!isset($attributeVariantsMap[$colorAttribute->id][$colorValue])) {
                        $attributeVariantsMap[$colorAttribute->id][$colorValue] = [
                            'value' => $colorValue,
                            'images' => $variantImages,
                            'color_code' => $colorCode,
                        ];
                    }
                }
            }
            
            // Extract variable attributes (size, material, etc.)
            foreach ($parsed['variable'] as $varKey => $varValue) {
                // Find attribute by name or slug
                $attribute = $allVariantAttributes->first(function($attr) use ($varKey) {
                    return strtolower($attr->name) === strtolower($varKey) 
                        || strtolower($attr->slug) === strtolower($varKey);
                });
                
                if ($attribute) {
                    if (!isset($attributeValues[$attribute->id])) {
                        $attributeValues[$attribute->id] = [];
                    }
                    if (!isset($attributeVariantsMap[$attribute->id])) {
                        $attributeVariantsMap[$attribute->id] = [];
                    }
                    if (!in_array($varValue, $attributeValues[$attribute->id])) {
                        $attributeValues[$attribute->id][] = $varValue;
                    }
                    if (!isset($attributeVariantsMap[$attribute->id][$varValue])) {
                        $attributeVariantsMap[$attribute->id][$varValue] = [
                            'value' => $varValue,
                            'images' => $variantImages,
                        ];
                    }
                    
                    // Legacy size support
                    if (strtolower($attribute->name) === 'size' || strtolower($attribute->slug) === 'size') {
                        if (!in_array($varValue, $sizes)) {
                            $sizes[] = $varValue;
                        }
                    }
                } else {
                    // Attribute not found in ProductAttribute table - create dynamic entry
                    // Use a dynamic key to distinguish from real attributes
                    $dynamicAttributeKey = 'dynamic_' . strtolower($varKey);
                    
                    if (!isset($attributeValues[$dynamicAttributeKey])) {
                        $attributeValues[$dynamicAttributeKey] = [];
                        $attributeVariantsMap[$dynamicAttributeKey] = [];
                    }
                    
                    if (!in_array($varValue, $attributeValues[$dynamicAttributeKey])) {
                        $attributeValues[$dynamicAttributeKey][] = $varValue;
                    }
                    
                    if (!isset($attributeVariantsMap[$dynamicAttributeKey][$varValue])) {
                        $attributeVariantsMap[$dynamicAttributeKey][$varValue] = [
                            'value' => $varValue,
                            'images' => $variantImages,
                        ];
                    }
                    
                    // Legacy size support
                    if (strtolower($varKey) === 'size' && !in_array($varValue, $sizes)) {
                        $sizes[] = $varValue;
                    }
                }
            }
        }
        
        // Build attributes structure for frontend
        $attributesData = [];
        
        // First, add attributes from ProductAttribute table
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
        
        // Then, add dynamic attributes (from variants but not in ProductAttribute table)
        foreach ($attributeValues as $attrKey => $values) {
            if (strpos($attrKey, 'dynamic_') === 0 && !empty($values)) {
                $attrName = str_replace('dynamic_', '', $attrKey);
                $attrName = ucfirst(str_replace('_', ' ', $attrName));
                
                $valueDataArray = collect($values)->map(function($value) use ($attrKey, $attributeVariantsMap) {
                    $valueData = [
                        'value' => $value,
                    ];
                    
                    if (isset($attributeVariantsMap[$attrKey][$value])) {
                        $variantData = $attributeVariantsMap[$attrKey][$value];
                        if (isset($variantData['images'])) {
                            $valueData['images'] = $variantData['images'];
                        }
                    }
                    
                    return $valueData;
                })->values()->toArray();
                
                // Determine type based on attribute name
                $attrType = 'text';
                if (stripos($attrName, 'color') !== false) {
                    $attrType = 'color';
                } elseif (stripos($attrName, 'size') !== false || stripos($attrName, 'length') !== false) {
                    $attrType = 'size';
                }
                
                $attributesData[] = [
                    'id' => $attrKey, // Use dynamic key as ID
                    'name' => $attrName,
                    'slug' => strtolower(str_replace(' ', '-', $attrName)),
                    'type' => $attrType,
                    'values' => $valueDataArray,
                ];
            }
        }
        
        // Build variant data map with description and highlights_details for JavaScript
        $variantDataMap = [];
        foreach ($activeVariants as $variant) {
            // Parse attributes using new helper function
            $parsed = self::parseVariantAttributes($variant->attributes);
            
            $colorValue = null;
            $sizeValue = null;
            $allAttributeValues = []; // Store all attribute values for key generation
            
            // Get color value
            if ($parsed['color']) {
                $colorValue = $parsed['color']['label'];
                // Find color attribute
                $colorAttribute = $allVariantAttributes->first(function($attr) {
                    return $attr->type === 'color' || strtolower($attr->name) === 'color' || strtolower($attr->slug) === 'color';
                });
                if ($colorAttribute) {
                    $allAttributeValues[$colorAttribute->id] = $colorValue;
                }
            }
            
            // Get variable attributes (size, material, etc.)
            foreach ($parsed['variable'] as $varKey => $varValue) {
                // Find attribute by name or slug
                $attribute = $allVariantAttributes->first(function($attr) use ($varKey) {
                    return strtolower($attr->name) === strtolower($varKey) 
                        || strtolower($attr->slug) === strtolower($varKey);
                });
                
                if ($attribute) {
                    $allAttributeValues[$attribute->id] = $varValue;
                    if (strtolower($attribute->name) === 'size' || strtolower($attribute->slug) === 'size') {
                        $sizeValue = $varValue;
                    }
                } else {
                    // Fallback: treat as size if key is 'size'
                    if (strtolower($varKey) === 'size') {
                        $sizeValue = $varValue;
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
            
            $basePrice = $variant->price ?? 0;
            $baseSalePrice = $variant->sale_price;
            
            // Calculate GST-inclusive prices
            $price = $calculateGstInclusivePrice($basePrice, $gstType, $gstPercentage);
            $salePrice = $baseSalePrice ? $calculateGstInclusivePrice($baseSalePrice, $gstType, $gstPercentage) : null;
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
            
            // Parse measurements if available
            $measurements = [];
            if ($variant->measurements) {
                if (is_string($variant->measurements)) {
                    $measurements = json_decode($variant->measurements, true) ?? [];
                } else {
                    $measurements = is_array($variant->measurements) ? $variant->measurements : [];
                }
            }
            
            $variantDataMap[$key] = [
                'id' => $variant->id,
                'sku' => $variant->sku ?? '',
                'price' => $price,
                'sale_price' => $salePrice,
                'has_sale' => $hasVariantSale,
                'display_price' => $hasVariantSale ? $salePrice : $price,
                'discount_type' => $variant->discount_type ?? null,
                'discount_value' => $variant->discount_value ?? null,
                'discount_active' => $variant->discount_active ?? false,
                'description' => $variant->description ?? '',
                'highlights_details' => $highlightsDetails,
                'attributes' => $allAttributeValues, // Include all attributes
                'images' => $variantImages, // Include variant images
                'measurements' => $measurements, // Include measurements
                // Calculate stock status from actual quantity (not database value)
                'is_in_stock' => (function() use ($variant) {
                    // Calculate total stock from inventory_stocks if available, otherwise use stock_quantity
                    $warehouseStock = $variant->inventoryStocks()->sum('quantity');
                    $totalStock = ($variant->inventoryStocks()->count() > 0) 
                        ? $warehouseStock 
                        : ($variant->stock_quantity ?? 0);
                    // If quantity > 0, in stock; otherwise out of stock
                    return $totalStock > 0;
                })(),
            ];
        }
        
        // Check stock status (use warehouse inventory if available)
        // Always calculate from actual quantity, not database stock_status value
        $inStock = $activeVariants->filter(function($variant) {
            // Calculate total stock from inventory_stocks if available, otherwise use stock_quantity
            $warehouseStock = $variant->inventoryStocks()->sum('quantity');
            $totalStock = ($variant->inventoryStocks()->count() > 0) 
                ? $warehouseStock 
                : ($variant->stock_quantity ?? 0);
            // If quantity > 0, in stock; otherwise out of stock (regardless of manage_stock setting)
            return $totalStock > 0;
        })->count() > 0;
        
        // Get wishlist status
        $customer = $request->user();
        $customerId = ($customer && $customer instanceof \App\Models\Customer) ? $customer->id : null;
        
        // Prioritize session_id from query parameter (for guest users with localStorage session_id)
        $sessionId = $request->query('session_id') 
                  ?? $request->input('session_id') 
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
 
        // Count recent purchases (last 30 days)
        $recentPurchaseCount = \App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.product_id', $product->id)
            ->whereIn('orders.status', ['delivered', 'shipped', 'processing', 'completed'])
            ->where('orders.payment_status', 'paid')
            ->where('orders.created_at', '>=', now()->subDays(30))
            ->sum('order_items.quantity');
         
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
        
            $imageUrl = asset('assets/images/placeholder.jpg');
        
            $firstSimilarVariant = $activeVariants->first();
            if ($firstSimilarVariant && $firstSimilarVariant->images && $firstSimilarVariant->images->count() > 0) {
                $primaryVariantImage = $firstSimilarVariant->images->where('is_primary', true)->first();
        
                if ($primaryVariantImage) {
                    $imageUrl = asset('storage/' . $primaryVariantImage->image_path);
                } else {
                    $firstVariantImage = $firstSimilarVariant->images->first();
                    if ($firstVariantImage) {
                        $imageUrl = asset('storage/' . $firstVariantImage->image_path);
                    }
                }
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
            'variantDataMap',
            'gstType',
            'gstPercentage',
            'recentPurchaseCount'
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
                  ->with([
                      'images' => function($imgQ) {
                          $imgQ->orderBy('sort_order')->orderBy('id');
                      },
                      'inventoryStocks'
                  ]);
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
        
        // Helper function to calculate GST-inclusive price
        $calculateGstInclusivePrice = function($basePrice, $gstType, $gstPercentage) {
            if ($basePrice <= 0) return 0;
            
            // If gst_type is true, price is already inclusive
            if ($gstType === true || $gstType === 1 || $gstType === '1') {
                return $basePrice;
            }
            
            // If gst_type is false, add GST
            if ($gstType === false || $gstType === 0 || $gstType === '0') {
                $gstPercent = $gstPercentage ?? 0;
                if ($gstPercent > 0) {
                    return $basePrice + ($basePrice * $gstPercent / 100);
                }
            }
            
            return $basePrice;
        };
        
        // Get GST settings from product
        $gstType = $product->gst_type ?? true; // Default to inclusive
        $gstPercentage = $product->gst_percentage ?? 0;
        
        // Get price range with GST applied
        $prices = $activeVariants->map(function($variant) use ($calculateGstInclusivePrice, $gstType, $gstPercentage) {
            $basePrice = $variant->price ?? 0;
            return $calculateGstInclusivePrice($basePrice, $gstType, $gstPercentage);
        })->filter();
        
        $salePrices = $activeVariants->map(function($variant) use ($calculateGstInclusivePrice, $gstType, $gstPercentage) {
            $baseSalePrice = $variant->sale_price;
            if ($baseSalePrice && $baseSalePrice > 0) {
                return $calculateGstInclusivePrice($baseSalePrice, $gstType, $gstPercentage);
            }
            return null;
        })->filter();
        
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
                    'url' => asset('assets/images/placeholder.jpg'),
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
            // Parse attributes using new helper function
            $parsed = self::parseVariantAttributes($variant->attributes);
            
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
            
            // Extract color from new structured format
            if ($parsed['color']) {
                $colorValue = $parsed['color']['label'];
                $colorCode = $parsed['color']['code'];
                
                // Populate color arrays
                if (!in_array($colorValue, $colors)) {
                    $colors[] = $colorValue;
                    $colorVariantsMap[$colorValue] = [
                        'color' => $colorValue,
                        'color_code' => $colorCode,
                        'images' => $variantImages,
                    ];
                }
                
                // Find color attribute to add to attributeValues
                $colorAttribute = $allVariantAttributes->first(function($attr) {
                    return $attr->type === 'color' || strtolower($attr->name) === 'color' || strtolower($attr->slug) === 'color';
                });
                
                if ($colorAttribute) {
                    if (!isset($attributeValues[$colorAttribute->id])) {
                        $attributeValues[$colorAttribute->id] = [];
                    }
                    if (!isset($attributeVariantsMap[$colorAttribute->id])) {
                        $attributeVariantsMap[$colorAttribute->id] = [];
                    }
                    if (!in_array($colorValue, $attributeValues[$colorAttribute->id])) {
                        $attributeValues[$colorAttribute->id][] = $colorValue;
                    }
                    if (!isset($attributeVariantsMap[$colorAttribute->id][$colorValue])) {
                        $attributeVariantsMap[$colorAttribute->id][$colorValue] = [
                            'value' => $colorValue,
                            'images' => $variantImages,
                            'color_code' => $colorCode,
                        ];
                    }
                }
            }
            
            // Extract variable attributes (size, material, etc.)
            foreach ($parsed['variable'] as $varKey => $varValue) {
                // Find attribute by name or slug
                $attribute = $allVariantAttributes->first(function($attr) use ($varKey) {
                    return strtolower($attr->name) === strtolower($varKey) 
                        || strtolower($attr->slug) === strtolower($varKey);
                });
                
                if ($attribute) {
                    if (!isset($attributeValues[$attribute->id])) {
                        $attributeValues[$attribute->id] = [];
                    }
                    if (!isset($attributeVariantsMap[$attribute->id])) {
                        $attributeVariantsMap[$attribute->id] = [];
                    }
                    if (!in_array($varValue, $attributeValues[$attribute->id])) {
                        $attributeValues[$attribute->id][] = $varValue;
                    }
                    if (!isset($attributeVariantsMap[$attribute->id][$varValue])) {
                        $attributeVariantsMap[$attribute->id][$varValue] = [
                            'value' => $varValue,
                            'images' => $variantImages,
                        ];
                    }
                    
                    // Legacy size support
                    if (strtolower($attribute->name) === 'size' || strtolower($attribute->slug) === 'size') {
                        if (!in_array($varValue, $sizes)) {
                            $sizes[] = $varValue;
                        }
                    }
                } else {
                    // Attribute not found in ProductAttribute table - create dynamic entry
                    // Use a dynamic key to distinguish from real attributes
                    $dynamicAttributeKey = 'dynamic_' . strtolower($varKey);
                    
                    if (!isset($attributeValues[$dynamicAttributeKey])) {
                        $attributeValues[$dynamicAttributeKey] = [];
                        $attributeVariantsMap[$dynamicAttributeKey] = [];
                    }
                    
                    if (!in_array($varValue, $attributeValues[$dynamicAttributeKey])) {
                        $attributeValues[$dynamicAttributeKey][] = $varValue;
                    }
                    
                    if (!isset($attributeVariantsMap[$dynamicAttributeKey][$varValue])) {
                        $attributeVariantsMap[$dynamicAttributeKey][$varValue] = [
                            'value' => $varValue,
                            'images' => $variantImages,
                        ];
                    }
                    
                    // Legacy size support
                    if (strtolower($varKey) === 'size' && !in_array($varValue, $sizes)) {
                        $sizes[] = $varValue;
                    }
                }
            }
        }
        
        // Build attributes structure for frontend
        $attributesData = [];
        
        // First, add attributes from ProductAttribute table
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
        
        // Then, add dynamic attributes (from variants but not in ProductAttribute table)
        foreach ($attributeValues as $attrKey => $values) {
            if (strpos($attrKey, 'dynamic_') === 0 && !empty($values)) {
                $attrName = str_replace('dynamic_', '', $attrKey);
                $attrName = ucfirst(str_replace('_', ' ', $attrName));
                
                $valueDataArray = collect($values)->map(function($value) use ($attrKey, $attributeVariantsMap) {
                    $valueData = [
                        'value' => $value,
                    ];
                    
                    if (isset($attributeVariantsMap[$attrKey][$value])) {
                        $variantData = $attributeVariantsMap[$attrKey][$value];
                        if (isset($variantData['images'])) {
                            $valueData['images'] = $variantData['images'];
                        }
                    }
                    
                    return $valueData;
                })->values()->toArray();
                
                // Determine type based on attribute name
                $attrType = 'text';
                if (stripos($attrName, 'color') !== false) {
                    $attrType = 'color';
                } elseif (stripos($attrName, 'size') !== false || stripos($attrName, 'length') !== false) {
                    $attrType = 'size';
                }
                
                $attributesData[] = [
                    'id' => $attrKey, // Use dynamic key as ID
                    'name' => $attrName,
                    'slug' => strtolower(str_replace(' ', '-', $attrName)),
                    'type' => $attrType,
                    'values' => $valueDataArray,
                ];
            }
        }
        
        // Check stock status (use warehouse inventory if available)
        // Always calculate from actual quantity, not database stock_status value
        $inStock = $activeVariants->filter(function($variant) {
            // Calculate total stock from inventory_stocks if available, otherwise use stock_quantity
            $warehouseStock = $variant->inventoryStocks()->sum('quantity');
            $totalStock = ($variant->inventoryStocks()->count() > 0) 
                ? $warehouseStock 
                : ($variant->stock_quantity ?? 0);
            // If quantity > 0, in stock; otherwise out of stock (regardless of manage_stock setting)
            return $totalStock > 0;
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
                    ? '₹' . number_format($minPrice, 2) . ' - ₹' . number_format($maxPrice, 2)
                    : '₹' . number_format($minPrice, 2),
                'gst_type' => $gstType, // Add GST type for price display
                'gst_percentage' => $gstPercentage, // Add GST percentage for price display
                'colors' => array_values($colors), // Legacy support
                'sizes' => array_values($sizes), // Legacy support
                'color_variants' => $colorVariantsMap, // Legacy support
                'attributes' => $attributesData, // New: All variant attributes dynamically
                'in_stock' => $inStock,
                'variants' => $activeVariants->map(function($variant) use ($allVariantAttributes, $calculateGstInclusivePrice, $gstType, $gstPercentage) {
                    // Parse attributes using new helper function
                    $parsed = self::parseVariantAttributes($variant->attributes);
                    
                    $colorValue = null;
                    $sizeValue = null;
                    $allAttributes = []; // New: All attributes for this variant
                    
                    // Get color value
                    if ($parsed['color']) {
                        $colorValue = $parsed['color']['label'];
                        // Find color attribute
                        $colorAttribute = $allVariantAttributes->first(function($attr) {
                            return $attr->type === 'color' || strtolower($attr->name) === 'color' || strtolower($attr->slug) === 'color';
                        });
                        if ($colorAttribute) {
                            $allAttributes[] = [
                                'attribute_id' => $colorAttribute->id,
                                'attribute_name' => $colorAttribute->name,
                                'attribute_slug' => $colorAttribute->slug,
                                'attribute_type' => $colorAttribute->type,
                                'value' => $colorValue,
                            ];
                        }
                    }
                    
                    // Get variable attributes (size, material, etc.)
                    foreach ($parsed['variable'] as $varKey => $varValue) {
                        // Find attribute by name or slug
                        $attribute = $allVariantAttributes->first(function($attr) use ($varKey) {
                            return strtolower($attr->name) === strtolower($varKey) 
                                || strtolower($attr->slug) === strtolower($varKey);
                        });
                        
                        if ($attribute) {
                            $allAttributes[] = [
                                'attribute_id' => $attribute->id,
                                'attribute_name' => $attribute->name,
                                'attribute_slug' => $attribute->slug,
                                'attribute_type' => $attribute->type,
                                'value' => $varValue,
                            ];
                            if (strtolower($attribute->name) === 'size' || strtolower($attribute->slug) === 'size') {
                                $sizeValue = $varValue;
                            }
                        } else {
                            // Dynamic attribute (not in ProductAttribute table)
                            // Add it to allAttributes with dynamic key as attribute_id
                            $dynamicKey = 'dynamic_' . strtolower($varKey);
                            $allAttributes[] = [
                                'attribute_id' => $dynamicKey, // Use dynamic key as ID
                                'attribute_name' => ucfirst(str_replace('_', ' ', $varKey)),
                                'attribute_slug' => strtolower(str_replace('_', '-', $varKey)),
                                'attribute_type' => (stripos($varKey, 'size') !== false || stripos($varKey, 'length') !== false) ? 'size' : 'text',
                                'value' => $varValue,
                            ];
                            
                            // Fallback: treat as size if key is 'size'
                            if (strtolower($varKey) === 'size') {
                                $sizeValue = $varValue;
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
                    
                    // Parse measurements if available
                    $measurements = [];
                    if ($variant->measurements) {
                        if (is_string($variant->measurements)) {
                            $measurements = json_decode($variant->measurements, true) ?? [];
                        } else {
                            $measurements = is_array($variant->measurements) ? $variant->measurements : [];
                        }
                    }
                    
                    // Calculate GST-inclusive prices
                    $basePrice = $variant->price ?? 0;
                    $baseSalePrice = $variant->sale_price;
                    $price = $calculateGstInclusivePrice($basePrice, $gstType, $gstPercentage);
                    $salePrice = $baseSalePrice ? $calculateGstInclusivePrice($baseSalePrice, $gstType, $gstPercentage) : null;
                    $hasSale = $salePrice && $salePrice < $price;
                    
                    return [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'price' => $price,
                        'sale_price' => $salePrice,
                        'has_sale' => $hasSale,
                        'discount_type' => $variant->discount_type ?? null,
                        'discount_value' => $variant->discount_value ?? null,
                        'discount_active' => $variant->discount_active ?? false,
                        'color' => $colorValue, // Legacy support
                        'size' => $sizeValue, // Legacy support
                        'attributes' => $allAttributes, // New: All attributes for this variant
                        'images' => $variantImages,
                        'stock_quantity' => $variant->total_stock_quantity ?? $variant->stock_quantity,
                        'stock_status' => $variant->stock_status,
                        'manage_stock' => $variant->manage_stock,
                        // Calculate stock status from actual quantity (not database value)
                        'is_in_stock' => (function() use ($variant) {
                            // Calculate total stock from inventory_stocks if available, otherwise use stock_quantity
                            $warehouseStock = $variant->inventoryStocks()->sum('quantity');
                            $totalStock = ($variant->inventoryStocks()->count() > 0) 
                                ? $warehouseStock 
                                : ($variant->stock_quantity ?? 0);
                            // If quantity > 0, in stock; otherwise out of stock
                            return $totalStock > 0;
                        })(),
                        'highlights_details' => $highlightsDetails,
                        'measurements' => $measurements, // Include measurements
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
                        $imgQ->orderBy('is_primary', 'desc')->orderBy('sort_order')->orderBy('id');
                    }]);
                }]);
            }])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('frontend.my-orders', compact('customer', 'orders'));
    }

    /**
     * Cancel order (customer-facing)
     */
    public function cancelOrder(Request $request, $id)
    {
        $customer = Auth::guard('customer')->user();
        
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to cancel an order'
            ], 401);
        }

        $order = \App\Models\Order::with('items.product', 'items.variant')->findOrFail($id);
        
        // Verify order belongs to customer
        if ($order->customer_id !== $customer->id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to cancel this order'
            ], 403);
        }

        // Validate order can be cancelled
        $cancellableStatuses = ['pending', 'processing'];
        if (!in_array($order->status, $cancellableStatuses)) {
            return response()->json([
                'success' => false,
                'message' => "Order cannot be cancelled. Current status: " . ucfirst($order->status) . ". Only orders with status 'Pending' or 'Processing' can be cancelled."
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Restore stock using CheckoutService
            $checkoutService = new \App\Services\CheckoutService();
            $checkoutService->restoreOrderStock($order);
            
            // Update order status
            $order->status = 'cancelled';
            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully. Stock has been restored.',
                'data' => $order->load('items')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error cancelling order', [
                'order_id' => $order->id,
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling order: ' . $e->getMessage()
            ], 500);
        }
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
            
            // Skip if product doesn't exist (deleted)
            if (!$product) {
                return null;
            }
            
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
                    : asset('frontend/images/product/sample-product.jpg'));
            
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
                    if (!is_string($key)) continue; // Skip non-string keys
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
                        // Handle array values (new structured format)
                        $colorValue = is_array($value) ? ($value['label'] ?? $value['value'] ?? '') : (string)$value;
                        $colorAttributeId = (int)$key;
                        break;
                    }
                    if (!is_string($key)) continue; // Skip non-string keys
                    $attribute = $colorAttributeNameMap[$key] ?? 
                                $colorAttributeNameMap[ucfirst($key)] ?? 
                                null;
                    if ($attribute) {
                        // Handle array values (new structured format)
                        $colorValue = is_array($value) ? ($value['label'] ?? $value['value'] ?? '') : (string)$value;
                        $colorAttributeId = $attribute->id;
                        break;
                    }
                    if (strtolower($key) === 'color') {
                        // Handle array values (new structured format)
                        $colorValue = is_array($value) ? ($value['label'] ?? $value['value'] ?? '') : (string)$value;
                        $attribute = $colorAttributeNameMap->first();
                        $colorAttributeId = $attribute ? $attribute->id : null;
                        break;
                    }
                }
                
                // Normalize colorValue to always be a string
                if ($colorValue) {
                    $colorValue = is_array($colorValue) ? ($colorValue['label'] ?? $colorValue['value'] ?? '') : (string)$colorValue;
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
                    'discount_type' => $variant->discount_type ?? null,
                    'discount_value' => $variant->discount_value ?? null,
                    'discount_active' => $variant->discount_active ?? false,
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
        })->filter(); // Remove null entries (deleted products)
        
        return view('frontend.wishlist', compact('wishlistProducts', 'customer'));
    }

    public function profileInfo(Request $request)
    {
        $customer = Auth::guard('customer')->user();
         
        
        if ($customer) {
            
            
            // Verify database directly
            $dbCustomer = \DB::table('customers')->where('id', $customer->id)->first();
            if ($dbCustomer) {
                 
                
                // Check if profile_image file exists and sync if needed
                if ($dbCustomer->profile_image) {
                    $fullPath = storage_path('app/public/' . $dbCustomer->profile_image);
                    $publicPath = public_path('storage/' . $dbCustomer->profile_image);
                    
                    
                    // Sync file to public/storage if it exists in storage but not in public
                    if (file_exists($fullPath) && !file_exists($publicPath)) {
                        $destinationDir = dirname($publicPath);
                        if (!is_dir($destinationDir)) {
                            File::makeDirectory($destinationDir, 0755, true);
                        }
                        
                        if (File::copy($fullPath, $publicPath)) {
                        } else {
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
        
        
        return view('frontend.profile-info', compact('customer', 'fields', 'qolFields'));
    }

    public function updateProfileInfo(Request $request)
    {
       
        $customer = Auth::guard('customer')->user();
        
        if (!$customer) {
            return redirect()->route('frontend.index')->with('error', 'Please login to update your profile.');
        }
        
        // Get fields for validation (basic_info and qol groups)
        $fields = FieldManagement::whereIn('field_group', ['basic_info', 'qol'])
            ->active()
            ->visible()
            ->ordered()
            ->get();
        
        
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
        
      
        
        // Validate request
        try {
            $validated = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        }
        
        // Handle file uploads first
        foreach ($fields as $field) {
            if ($field->input_type === 'file' && $request->hasFile($field->field_key)) {
                $file = $request->file($field->field_key); 
                
                // Generate unique filename
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                // Store in storage/app/public/customers/profile_images
                $path = $file->storeAs('customers/profile_images', $filename, 'public');
                
               
                
                // Check if storage link exists
                $publicStoragePath = public_path('storage');
                $storageLinkExists = is_link($publicStoragePath) || is_dir($publicStoragePath);
               
                
                // Ensure file is copied to public/storage for immediate access
                $sourceFile = storage_path('app/public/' . $path);
                $destinationFile = public_path('storage/' . $path);
                $destinationDir = dirname($destinationFile);
                
                if (file_exists($sourceFile)) {
                    // Create destination directory if it doesn't exist
                    if (!is_dir($destinationDir)) {
                        File::makeDirectory($destinationDir, 0755, true);
                    }
                    
                    // Copy file to public/storage
                    if (File::copy($sourceFile, $destinationFile)) {
                    } else {
                    }
                } else {
                }
                
                // Delete old image if exists
                if ($customer->{$field->field_key}) {
                    $oldPath = storage_path('app/public/' . $customer->{$field->field_key});
                     
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                        
                    }
                }
                
                // Save relative path (normalize to forward slashes for cross-platform compatibility)
                $normalizedPath = str_replace('\\', '/', $path);
                $customer->{$field->field_key} = $normalizedPath;
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
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            $oldValue = $customer->{$field->field_key};
            $customer->{$field->field_key} = $fieldValue;
         
        }
        
       
        
        $saved = $customer->save();
        
         
        
        // Refresh customer data
        $customer->refresh();
        
        
        
        // Verify database directly
        $dbCustomer = \DB::table('customers')->where('id', $customer->id)->first();
         
        
        // Return JSON response for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            $profileImageUrl = null;
            if ($customer->profile_image) {
                // Use Storage facade to get the URL, which handles path correctly
                $profileImageUrl = Storage::disk('public')->url($customer->profile_image);
                 
            }
            
             
            
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
            
             
            return redirect()->route('frontend.change-password')
                ->with('success', 'Password changed successfully!');
                
        } catch (\Exception $e) {
             
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
                        $pincodeData = json_decode($objectJson, true);
                        if ($pincodeData && is_array($pincodeData)) {
                            return $pincodeData;
                        } else {
                            return null;
                        }
                    }
                }
            }
            $objectEnd++;
        }
        
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
            
            
            if (!$pincode || strlen(trim($pincode)) < 4) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please enter a valid pincode'
                ], 400);
            }
            
            $pincode = trim($pincode);
            
            // Load pincodes JSON (new format)
            $pincodesPath = public_path('location-json/pincodes.json');
            
            if (!file_exists($pincodesPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location data not available'
                ], 404);
            }
            
            // Use optimized search method for pincodes.json
            $foundPincode = $this->searchPincodeInPincodesJson($pincodesPath, $pincode);
            
            if (!$foundPincode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location not found for this pincode'
                ], 404);
            }
            
            
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
            
           
            
            return redirect()->route('frontend.addresses')->with('success', $message);
            
        } catch (\Exception $e) {
           
            
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
            
           
            
            return response()->json([
                'success' => true,
                'message' => 'Address deleted successfully.'
            ]);
            
        } catch (\Exception $e) { 
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
        
        // Prioritize session_id from query parameter (for guest users with localStorage session_id)
        $sessionId = $request->query('session_id') 
                  ?? $request->input('session_id') 
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
                'items.variant.images' => function($q) {
                    $q->orderBy('is_primary', 'desc')->orderBy('sort_order')->orderBy('id');
                },
                'items.variant',
                'coupon'
            ]);
            
            // Recalculate cart totals manually
            $cart->load('items.product', 'coupon');
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
            
            // Calculate GST from cart items
            // For tax-inclusive items: tax is already included in price, don't add it again
            // For tax-exclusive items: calculate tax and add it separately
            $taxAmount = 0;
            
            foreach ($cart->items as $item) {
                $product = $item->product;
                if (!$product) {
                    continue;
                }
                
                // Get GST settings from product
                $gstType = $product->gst_type ?? true; // Default to inclusive
                $gstPercentage = $product->gst_percentage ?? 0;
                
                if ($gstPercentage > 0) {
                    $itemTotalPrice = $item->total_price ?? 0;
                    
                    if ($itemTotalPrice > 0) {
                        if ($gstType) {
                            // GST inclusive: tax is already included in price
                            // Don't add tax again - it's already in the price
                            $itemGstAmount = 0;
                        } else {
                            // GST exclusive: calculate tax to be added on top
                            $itemGstAmount = $itemTotalPrice * ($gstPercentage / 100);
                        }
                        $taxAmount += $itemGstAmount; // Only add tax for exclusive items
                    }
                }
            }
            
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
        // Get validated data from middleware attributes
        $cart = $request->attributes->get('validated_cart');
        $customer = $request->attributes->get('authenticated_customer');
        $addressData = $request->attributes->get('address_data');
        
        // Safety check - if middleware didn't provide data, redirect back
        if (!$cart || !$customer) {
           
            return redirect()->route('frontend.shoping-cart')
                ->with('error', 'Unable to proceed to checkout. Please try again.');
        }
        
        $sessionId = $request->input('session_id') 
                  ?? $request->query('session_id') 
                  ?? $request->header('X-Session-ID') 
                  ?? session()->getId();
        
        // Load cart items with relationships for display
        $cart->load([
            'items.product.primaryImage',
            'items.product.images' => function($q) {
                $q->orderBy('sort_order')->orderBy('id')->limit(1);
            },
            'items.product.category',
            'items.variant.images' => function($q) {
                $q->orderBy('sort_order')->orderBy('id')->limit(1);
            },
            'items.variant.inventoryStocks',
            'items.variant',
            'coupon'
        ]);
        
        // Recalculate cart totals using final prices (after discounts) - matching shopping cart logic
        $this->recalculateCartTotalsWithFinalPrices($cart);
        
        // Persist checkout data in session for validation errors
        session()->put('checkout_data', [
            'cart_id' => $cart->id,
            'session_id' => $sessionId,
            'shipping_address_id' => $request->old('shipping_address_id', $addressData['default_shipping']->id ?? null),
            'billing_address_id' => $request->old('billing_address_id', $addressData['default_billing']->id ?? null),
            'billing_same_as_shipping' => $request->old('billing_same_as_shipping', true),
        ]);
        
       
        return view('frontend.checkout', [
            'cart' => $cart,
            'customer' => $customer,
            'addresses' => $addressData['addresses'],
            'defaultShippingAddress' => $addressData['default_shipping'],
            'defaultBillingAddress' => $addressData['default_billing'],
            'hasAddresses' => $addressData['has_addresses'],
            'singleAddress' => $addressData['single_address'],
            'sessionId' => $sessionId,
            'checkoutData' => session('checkout_data', []),
        ]);
    }

    public function processCheckout(Request $request)
    {
        // Get validated data from middleware attributes
        $cart = $request->attributes->get('validated_cart');
        $customer = $request->attributes->get('authenticated_customer');
        
       
        
        try {
            $checkoutService = app(CheckoutService::class);
            
            // Validate request data
            $validatedData = $checkoutService->validateCheckoutRequest($request->all());
            
            // Validate addresses
            $addressValidation = $checkoutService->validateAddresses(
                $customer,
                $validatedData['shipping_address_id'] ?? null,
                $validatedData['billing_address_id'] ?? null,
                $validatedData['billing_same_as_shipping'] ?? false
            );
            
            if (!$addressValidation['valid']) {
                // Convert errors array to key-value pairs for proper display
                $errorBag = [];
                foreach ($addressValidation['errors'] as $index => $error) {
                    $errorBag['address_' . $index] = $error;
                }
                return redirect()->route('frontend.checkout')
                    ->withInput()
                    ->withErrors($errorBag)
                    ->with('error', 'Please fix the address errors and try again.');
            }
            
            // Validate cart stock before creating order
            $cartValidation = $checkoutService->validateCart($cart);
           
            if (!$cartValidation['valid']) {
               
                // Convert errors array to key-value pairs for proper display
                $errorBag = [];
                foreach ($cartValidation['errors'] as $index => $error) {
                    $errorBag['cart_' . $index] = $error;
                }
                return redirect()->route('frontend.checkout')
                    ->withInput()
                    ->withErrors($errorBag)
                    ->with('error', 'Please review the following errors and try again.');
            }
            
            // Create order
            $order = $checkoutService->createOrder(
                $cart,
                $customer,
                $addressValidation['shipping_address'],
                $addressValidation['billing_address'],
                $validatedData['billing_same_as_shipping'] ?? false,
                [
                    'payment_method' => $validatedData['payment_method'] ?? 'cash_on_delivery',
                    'notes' => $validatedData['notes'] ?? null,
                ] 
            );
            
            // Clear checkout session data
            session()->forget('checkout_data');
            
            // Redirect to order confirmation
            return redirect()->route('frontend.complete-order', ['order' => $order->order_number])
                ->with('success', 'Order placed successfully!');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('frontend.checkout')
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
           
            return redirect()->route('frontend.checkout')
                ->withInput()
                ->with('error', 'Checkout failed: ' . $e->getMessage());
        }
    }

    /**
     * Create Razorpay order
     */
    public function createRazorpayOrder(Request $request)
    {
        try {
            $cart = $request->attributes->get('validated_cart');
            $customer = $request->attributes->get('authenticated_customer');
            
            $checkoutService = app(CheckoutService::class);
            
            // Validate request data
            $validatedData = $checkoutService->validateCheckoutRequest($request->all());
            
            // Validate addresses
            $addressValidation = $checkoutService->validateAddresses(
                $customer,
                $validatedData['shipping_address_id'] ?? null,
                $validatedData['billing_address_id'] ?? null,
                $validatedData['billing_same_as_shipping'] ?? false
            );
            
            if (!$addressValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Address validation failed',
                    'errors' => $addressValidation['errors']
                ], 422);
            }
            
            // Validate cart
            $cartValidation = $checkoutService->validateCart($cart);
            if (!$cartValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart validation failed',
                    'errors' => $cartValidation['errors']
                ], 400);
            }
            
            // Create order first
            $order = $checkoutService->createOrder(
                $cart,
                $customer,
                $addressValidation['shipping_address'],
                $addressValidation['billing_address'],
                $validatedData['billing_same_as_shipping'] ?? false,
                [
                    'payment_method' => 'razorpay',
                    'notes' => $validatedData['notes'] ?? null,
                ]
            );
            
            // Calculate amount in paise (Razorpay uses smallest currency unit)
            $amount = round($order->total_amount * 100); // Convert to paise
            
            // Create Razorpay order
            $razorpayKeyId = env('RAZORPAY_KEY_ID', 'rzp_test_RytPVtZvClzzRV');
            $razorpayKeySecret = env('RAZORPAY_KEY_SECRET', 'J1eq1gkUD4049W9CuDsqljXw');
            
            $razorpayOrderData = [
                'amount' => $amount,
                'currency' => 'INR',
                'receipt' => $order->order_number,
                'notes' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_id' => $customer->id,
                ]
            ];
            
            // Create order via Razorpay API
            $ch = curl_init('https://api.razorpay.com/v1/orders');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($razorpayOrderData));
            curl_setopt($ch, CURLOPT_USERPWD, $razorpayKeyId . ':' . $razorpayKeySecret);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                \Log::error('Razorpay order creation failed', [
                    'response' => $response,
                    'http_code' => $httpCode,
                    'order_id' => $order->id
                ]);
                
                // Delete the order if Razorpay order creation failed
                $order->delete();
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create payment order. Please try again.'
                ], 500);
            }
            
            $razorpayOrder = json_decode($response, true);
            
            // Update order with Razorpay order ID
            $order->update([
                'razorpay_order_id' => $razorpayOrder['id'] ?? null,
            ]);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'razorpay_order_id' => $razorpayOrder['id'],
                    'amount' => $amount,
                    'currency' => 'INR',
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Razorpay order creation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment order: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Handle Razorpay payment success callback
     */
    public function razorpayPaymentSuccess(Request $request)
    {
        try {
            $razorpayOrderId = $request->input('razorpay_order_id');
            $razorpayPaymentId = $request->input('razorpay_payment_id');
            $razorpaySignature = $request->input('razorpay_signature');
            $orderId = $request->input('order_id');
            
            if (!$razorpayOrderId || !$razorpayPaymentId || !$razorpaySignature || !$orderId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing payment parameters'
                ], 400);
            }
            
            $order = \App\Models\Order::find($orderId);
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }
            
            // Verify signature
            $razorpayKeySecret = env('RAZORPAY_KEY_SECRET', 'J1eq1gkUD4049W9CuDsqljXw');
            $generatedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, $razorpayKeySecret);
            
            if ($generatedSignature !== $razorpaySignature) {
                \Log::error('Razorpay signature verification failed', [
                    'order_id' => $orderId,
                    'expected' => $generatedSignature,
                    'received' => $razorpaySignature
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Payment verification failed'
                ], 400);
            }
            
            // Update order payment status
            $order->update([
                'payment_status' => 'paid',
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $razorpaySignature,
                'status' => 'processing', // Move order to processing after payment
            ]);
            
            // Clear cart
            if ($order->customer_id) {
                $cart = \App\Models\Cart::where('customer_id', $order->customer_id)->active()->first();
                if ($cart) {
                    $cart->items()->delete();
                    $cart->delete();
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Payment successful',
                'order_number' => $order->order_number
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Razorpay payment success error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function completeOrder(Request $request)
    {
        $orderNumber = $request->route('order');
        $order = null;
        
        if ($orderNumber) {
            $customer = Auth::guard('customer')->user();
            if ($customer) {
                $order = \App\Models\Order::where('order_number', $orderNumber)
                    ->where('customer_id', $customer->id)
                    ->with(['items.product', 'items.variant' => function($q) {
                        $q->with(['images' => function($imgQ) {
                            $imgQ->orderBy('is_primary', 'desc')->orderBy('sort_order')->orderBy('id');
                        }]);
                    }])
                    ->first();
            }
        }
        
        return view('frontend.complete-order', [
            'order' => $order
        ]);
    }

    
    /**
     * Parse variant attributes (handles both new structured format and old format)
     * New format: {"color": {"label": "black", "code": "#000000"}, "variable": {"size": "S", "material": "cotton"}}
     * Old format: {"1": "red", "size": "M"} or {"color": "red", "size": "M"}
     * 
     * Returns: ['color' => ['label' => 'black', 'code' => '#000000'], 'variable' => ['size' => 'S'], 'all' => [...]]
     */
    public static function parseVariantAttributes($attributes)
    {
        if (empty($attributes)) {
            return ['color' => null, 'variable' => [], 'all' => []];
        }
        
        // Decode if string
        $attrs = is_string($attributes) 
            ? json_decode($attributes, true) 
            : ($attributes ?? []);
        
        if (!is_array($attrs)) {
            return ['color' => null, 'variable' => [], 'all' => []];
        }
        
        $result = [
            'color' => null,
            'variable' => [],
            'all' => []
        ];
        
        // Check for new structured format
        if (isset($attrs['color']) && is_array($attrs['color'])) {
            $result['color'] = [
                'label' => $attrs['color']['label'] ?? '',
                'code' => $attrs['color']['code'] ?? '#ccc'
            ];
        }
        
        if (isset($attrs['variable']) && is_array($attrs['variable'])) {
            $result['variable'] = $attrs['variable'];
        }
        
        // If new format found, return it
        if ($result['color'] !== null || !empty($result['variable'])) {
            // Build 'all' array for backward compatibility
            if ($result['color']) {
                $result['all']['color'] = $result['color']['label'];
            }
            foreach ($result['variable'] as $key => $value) {
                $result['all'][$key] = $value;
            }
            return $result;
        }
        
        // Fallback to old format parsing
        foreach ($attrs as $key => $value) {
            if (empty($value)) {
                continue;
            }
            
            // Check if it's a color (by key name)
            if (strtolower($key) === 'color' || strtolower($key) === 'colour') {
                $result['color'] = [
                    'label' => is_array($value) ? ($value['label'] ?? $value['value'] ?? '') : (string)$value,
                    'code' => (is_array($value) && isset($value['code'])) ? $value['code'] : self::getColorCodeFromName($value)
                ];
                $result['all']['color'] = $result['color']['label'];
            } else {
                // Treat as variable attribute
                $result['variable'][$key] = is_array($value) ? ($value['value'] ?? $value['label'] ?? '') : (string)$value;
                $result['all'][$key] = $result['variable'][$key];
            }
        }
        
        return $result;
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
    
    /**
     * Recalculate cart totals using final prices (after discounts) - matching shopping cart logic
     */
    private function recalculateCartTotalsWithFinalPrices($cart)
    {
        // Helper function to calculate final price for an item (after discounts)
        $calculateItemFinalPrice = function($variant) {
            if (!$variant) {
                return 0;
            }
            
            $basePrice = $variant->price ?? 0;
            $salePrice = $variant->sale_price ?? null;
            $discountType = $variant->discount_type ?? '';
            $discountValue = $variant->discount_value ?? 0;
            $discountActive = $variant->discount_active ?? false;
            
            // Round base price
            $basePrice = round($basePrice);
            
            // Round sale price if it exists
            $roundedSalePrice = null;
            if ($salePrice !== null) {
                $roundedSalePrice = round($salePrice);
            }
            
            // Calculate final price
            $priceToDiscount = $basePrice;
            if ($roundedSalePrice !== null && $roundedSalePrice < $basePrice) {
                $priceToDiscount = $roundedSalePrice;
            }
            
            $finalPrice = $priceToDiscount;
            
            // Apply discount if active
            if ($discountActive && $discountType && $discountValue > 0) {
                if ($discountType === 'percentage') {
                    $discountAmount = ($priceToDiscount * $discountValue) / 100;
                    $finalPrice = max(0, $priceToDiscount - $discountAmount);
                } elseif ($discountType === 'amount' || $discountType === 'flat') {
                    $finalPrice = max(0, $priceToDiscount - $discountValue);
                }
            } elseif ($roundedSalePrice !== null && $roundedSalePrice < $basePrice) {
                $finalPrice = $roundedSalePrice;
            }
            
            // Round final price
            return round($finalPrice);
        };
        
        $subtotal = 0;
        $taxAmount = 0;
        $hasExclusiveItems = false;
        
        foreach ($cart->items as $item) {
            $product = $item->product;
            $variant = $item->variant;
            if (!$product) {
                continue;
            }
            
            // Calculate final price for this item (after discounts)
            $itemFinalPrice = $calculateItemFinalPrice($variant);
            
            // Get GST settings
            $gstType = $product->gst_type ?? true;
            $gstPercentage = $product->gst_percentage ?? 0;
            $quantity = $item->quantity ?? 1;
            
            if ($gstPercentage > 0) {
                if (!$gstType) {
                    // Exclusive of tax: Extract base price for tax calculation
                    $baseForTax = $itemFinalPrice / (1 + ($gstPercentage / 100));
                    $itemTax = $baseForTax * ($gstPercentage / 100);
                    $taxAmount += $itemTax * $quantity;
                    $hasExclusiveItems = true;
                    // Use final price in subtotal (will have tax added separately)
                    $subtotal += $itemFinalPrice * $quantity;
                } else {
                    // Inclusive of tax: Use final price directly (tax already included)
                    $subtotal += $itemFinalPrice * $quantity;
                    // Don't add tax since it's already in the price
                }
            } else {
                // No GST - use final price directly
                $subtotal += $itemFinalPrice * $quantity;
            }
        }
        
        // Calculate discount from coupon if exists
        $discountAmount = 0;
        if ($cart->coupon_code && $cart->coupon) {
            $coupon = $cart->coupon;
            if (property_exists($coupon, 'discount_type') && property_exists($coupon, 'discount_value')) {
                if ($coupon->discount_type === 'percentage') {
                    $discountAmount = ($subtotal * $coupon->discount_value) / 100;
                } else {
                    $discountAmount = min($coupon->discount_value, $subtotal);
                }
            }
        }
        
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
        
        // Update cart totals
        $cart->subtotal = $subtotal;
        $cart->discount_amount = $discountAmount;
        $cart->tax_amount = $taxAmount;
        $cart->shipping_amount = $shippingAmount;
        $cart->total_amount = $subtotal - $discountAmount + $taxAmount + $shippingAmount;
        $cart->save();
        
        return $cart;
    }
}
