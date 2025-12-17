<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WishlistApiController extends Controller
{
    /**
     * Get session ID or customer ID
     */
    private function getSessionOrCustomerId(Request $request)
    {
        $customer = $request->user();
        $customerId = ($customer && $customer instanceof \App\Models\Customer) ? $customer->id : null;
        
        $sessionId = $request->input('session_id') 
                  ?? $request->query('session_id') 
                  ?? $request->header('X-Session-ID') 
                  ?? session()->getId();
        
        return [$customerId, $sessionId];
    }

    /**
     * Get wishlist items
     * GET /api/wishlist
     */
    public function index(Request $request)
    {
        [$customerId, $sessionId] = $this->getSessionOrCustomerId($request);
        
        $query = Wishlist::with(['product.primaryImage', 'product.images', 'product.variants']);
        
        if ($customerId) {
            $query->where('customer_id', $customerId);
        } else {
            $query->where('session_id', $sessionId);
        }
        
        $wishlists = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $wishlists->map(function($wishlist) {
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
                
                return [
                    'id' => $wishlist->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_slug' => $product->slug,
                    'image_url' => $imageUrl,
                    'min_price' => $minPrice,
                    'max_price' => $maxPrice,
                    'min_sale_price' => $minSalePrice,
                    'max_sale_price' => $maxSalePrice,
                    'has_sale' => $hasSale,
                    'price_display' => $minPrice != $maxPrice 
                        ? '$' . number_format($minPrice, 0) . ' - $' . number_format($maxPrice, 0)
                        : '$' . number_format($minPrice, 0),
                ];
            }),
            'count' => $wishlists->count(),
        ]);
    }

    /**
     * Add product to wishlist
     * POST /api/wishlist
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        [$customerId, $sessionId] = $this->getSessionOrCustomerId($request);
        
        // Check if product already in wishlist
        $existing = Wishlist::where('product_id', $request->product_id)
            ->where(function($query) use ($customerId, $sessionId) {
                if ($customerId) {
                    $query->where('customer_id', $customerId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->first();
        
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Product already in wishlist',
            ], 400);
        }
        
        $wishlist = Wishlist::create([
            'customer_id' => $customerId,
            'session_id' => $customerId ? null : $sessionId,
            'product_id' => $request->product_id,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Product added to wishlist successfully',
            'data' => $wishlist,
        ]);
    }

    /**
     * Remove product from wishlist
     * DELETE /api/wishlist/{id}
     */
    public function destroy(Request $request, $id)
    {
        [$customerId, $sessionId] = $this->getSessionOrCustomerId($request);
        
        $wishlist = Wishlist::where('id', $id)
            ->where(function($query) use ($customerId, $sessionId) {
                if ($customerId) {
                    $query->where('customer_id', $customerId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->first();
        
        if (!$wishlist) {
            return response()->json([
                'success' => false,
                'message' => 'Wishlist item not found',
            ], 404);
        }
        
        $wishlist->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Product removed from wishlist successfully',
        ]);
    }

    /**
     * Remove product from wishlist by product_id
     * DELETE /api/wishlist/product/{productId}
     */
    public function removeByProduct(Request $request, $productId)
    {
        [$customerId, $sessionId] = $this->getSessionOrCustomerId($request);
        
        $wishlist = Wishlist::where('product_id', $productId)
            ->where(function($query) use ($customerId, $sessionId) {
                if ($customerId) {
                    $query->where('customer_id', $customerId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->first();
        
        if (!$wishlist) {
            // Product not in wishlist - return success anyway (idempotent operation)
            return response()->json([
                'success' => true,
                'message' => 'Product not in wishlist',
            ]);
        }
        
        $wishlist->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Product removed from wishlist successfully',
        ]);
    }

    /**
     * Get wishlist count
     * GET /api/wishlist/count
     */
    public function count(Request $request)
    {
        [$customerId, $sessionId] = $this->getSessionOrCustomerId($request);
        
        $count = Wishlist::where(function($query) use ($customerId, $sessionId) {
            if ($customerId) {
                $query->where('customer_id', $customerId);
            } else {
                $query->where('session_id', $sessionId);
            }
        })->count();
        
        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }
}
