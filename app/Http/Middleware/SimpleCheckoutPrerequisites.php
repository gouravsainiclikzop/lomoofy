<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;

class SimpleCheckoutPrerequisites
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            \Log::info('SimpleCheckoutPrerequisites middleware started', ['url' => $request->url()]);
            
            // Check if customer is authenticated
            $customer = Auth::guard('customer')->user();
            \Log::info('Customer auth check', ['customer_id' => $customer ? $customer->id : null]);
            
            if (!$customer) {
                \Log::info('Customer not authenticated, redirecting to cart');
                return redirect()->route('frontend.shoping-cart')
                    ->with('error', 'Please login to proceed to checkout');
            }

            // Get customer's cart
            $customerId = $customer->id;
            $sessionId = $request->input('session_id') 
                      ?? $request->query('session_id') 
                      ?? $request->header('X-Session-ID') 
                      ?? session()->getId();

            \Log::info('Looking for cart', ['customer_id' => $customerId, 'session_id' => $sessionId]);

            $cart = Cart::where(function($query) use ($customerId, $sessionId) {
                if ($customerId) {
                    $query->where('customer_id', $customerId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })->active()->with('items')->first();

            \Log::info('Cart found', ['cart_id' => $cart ? $cart->id : null, 'items_count' => $cart ? $cart->items->count() : 0]);

            // Check if cart exists and has items
            if (!$cart || $cart->items->count() === 0) {
                \Log::info('Cart empty or not found, redirecting to cart');
                return redirect()->route('frontend.shoping-cart')
                    ->with('error', 'Your cart is empty');
            }

            // Check if customer has addresses (simple check)
            $addresses = $customer->addresses;
            $addressCount = $addresses->count();
            \Log::info('Address count', ['count' => $addressCount]);
            
            if ($addressCount === 0) {
                \Log::info('No addresses found, redirecting to addresses page');
                return redirect()->route('frontend.addresses')
                    ->with('info', 'Please add an address before proceeding to checkout');
            }

            // Store cart and customer in request attributes (not merge)
            $request->attributes->set('validated_cart', $cart);
            $request->attributes->set('authenticated_customer', $customer);
            $request->attributes->set('address_data', [
                'addresses' => $addresses,
                'default_shipping' => $addresses->where('is_default', true)->first() ?: $addresses->first(),
                'default_billing' => $addresses->where('is_default', true)->first() ?: $addresses->first(),
                'has_addresses' => true,
                'single_address' => $addressCount === 1,
            ]);

            \Log::info('SimpleCheckoutPrerequisites middleware completed successfully');
            return $next($request);
            
        } catch (\Exception $e) {
            \Log::error('SimpleCheckoutPrerequisites middleware error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('frontend.shoping-cart')
                ->with('error', 'An error occurred. Please try again.');
        }
    }
}
