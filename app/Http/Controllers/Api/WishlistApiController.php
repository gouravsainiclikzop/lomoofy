<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WishlistApiController extends Controller
{
    /**
     * Get wishlist items
     * GET /api/wishlist
     */
    public function index(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $customerId = ($customer && $customer instanceof \App\Models\Customer) ? $customer->id : null;
        
        $sessionId = $request->input('session_id') 
                  ?? $request->query('session_id') 
                  ?? $request->header('X-Session-ID') 
                  ?? session()->getId();
        
        $wishlistQuery = Wishlist::with(['product']);
        
        if ($customerId) {
            $wishlistQuery->where('customer_id', $customerId);
        } else {
            $wishlistQuery->where('session_id', $sessionId);
        }
        
        $items = $wishlistQuery->get();
        
        return response()->json([
            'success' => true,
            'data' => $items->map(function($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product' => $item->product ? [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'slug' => $item->product->slug,
                    ] : null,
                ];
            }),
        ]);
    }

    /**
     * Get wishlist count
     * GET /api/wishlist/count
     */
    public function count(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $customerId = ($customer && $customer instanceof \App\Models\Customer) ? $customer->id : null;
        
        $sessionId = $request->input('session_id') 
                  ?? $request->query('session_id') 
                  ?? $request->header('X-Session-ID') 
                  ?? session()->getId();
        
        $wishlistQuery = Wishlist::query();
        
        if ($customerId) {
            $wishlistQuery->where('customer_id', $customerId);
        } else {
            $wishlistQuery->where('session_id', $sessionId);
        }
        
        $count = $wishlistQuery->count();
        
        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * Add product to wishlist
     * POST /api/wishlist
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'session_id' => 'nullable|string',
        ]);
        
        $customer = Auth::guard('customer')->user();
        $customerId = ($customer && $customer instanceof \App\Models\Customer) ? $customer->id : null;
        
        $sessionId = $request->input('session_id') 
                  ?? $request->header('X-Session-ID') 
                  ?? session()->getId();
        
        // Check if already in wishlist
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
                'success' => true,
                'message' => 'Product already in wishlist',
            ]);
        }
        
        Wishlist::create([
            'customer_id' => $customerId,
            'session_id' => $customerId ? null : $sessionId,
            'product_id' => $request->product_id,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Product added to wishlist',
        ]);
    }

    /**
     * Remove wishlist item by ID
     * DELETE /api/wishlist/{id}
     */
    public function destroy($id)
    {
        $wishlist = Wishlist::find($id);
        
        if (!$wishlist) {
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Wishlist item not found'],
            ], 404);
        }
        
        $wishlist->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Item removed from wishlist',
        ]);
    }

    /**
     * Remove wishlist item by product ID
     * DELETE /api/wishlist/product/{productId}
     */
    public function removeByProduct(Request $request, $productId)
    {
        $customer = Auth::guard('customer')->user();
        $customerId = ($customer && $customer instanceof \App\Models\Customer) ? $customer->id : null;
        
        $sessionId = $request->input('session_id') 
                  ?? $request->header('X-Session-ID') 
                  ?? session()->getId();
        
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
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Product not in wishlist'],
            ], 404);
        }
        
        $wishlist->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Product removed from wishlist',
        ]);
    }
}
