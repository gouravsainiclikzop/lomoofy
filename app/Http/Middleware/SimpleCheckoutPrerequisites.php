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
           
            
            // Check if customer is authenticated
            $customer = Auth::guard('customer')->user();
            
            if (!$customer) {
               
                return redirect()->route('frontend.shoping-cart')
                    ->with('error', 'Please login to proceed to checkout')
                    ->with('show_login', true)
                    ->with('redirect_after_login', $request->fullUrl());
            }

            // Get customer's cart
            $customerId = $customer->id;
            $sessionId = $request->input('session_id') 
                      ?? $request->query('session_id') 
                      ?? $request->header('X-Session-ID') 
                      ?? session()->getId();

           

            $cart = Cart::where(function($query) use ($customerId, $sessionId) {
                if ($customerId) {
                    $query->where('customer_id', $customerId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })->active()->with('items')->first();

           

            // Check if cart exists and has items
            if (!$cart || $cart->items->count() === 0) {
               
                return redirect()->route('frontend.shoping-cart')
                    ->with('error', 'Your cart is empty');
            }

            // Check if customer has addresses (simple check)
            $addresses = $customer->addresses;
            $addressCount = $addresses->count();
            
            if ($addressCount === 0) {
               
                // Store redirect URL in session (not flash) so it persists through address creation
                session()->put('redirect_after_address', route('frontend.shoping-cart'));
                
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

           
            return $next($request);
            
        } catch (\Exception $e) {
            
            
            return redirect()->route('frontend.shoping-cart')
                ->with('error', 'An error occurred. Please try again.');
        }
    }
}
