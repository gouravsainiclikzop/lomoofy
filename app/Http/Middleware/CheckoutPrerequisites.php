<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Services\CheckoutService;

class CheckoutPrerequisites
{
    protected $checkoutService;

    public function __construct()
    {
        // We'll resolve the service in the handle method to avoid dependency injection issues
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
           
            
            // Resolve CheckoutService from container
            $checkoutService = app(CheckoutService::class);
           
            
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
            })->active()->with('items.product', 'items.variant')->first();

           

            // Check if cart exists and has items
            if (!$cart || $cart->items->count() === 0) {
               
                return redirect()->route('frontend.shoping-cart')
                    ->with('error', 'Your cart is empty');
            }

            // Validate cart using checkout service
           
            $cartValidation = $checkoutService->validateCart($cart);
           
            
            if (!$cartValidation['valid']) {
               
                return redirect()->route('frontend.shoping-cart')
                    ->with('error', 'Cart validation failed: ' . implode(', ', $cartValidation['errors']));
            }

            // Check if customer has addresses
           
            $addressData = $checkoutService->getCustomerAddressesForCheckout($customer);
           
            
            if (!$addressData['has_addresses']) {
               
                // Store redirect URL in session (not flash) so it persists through address creation
                session()->put('redirect_after_address', route('frontend.shoping-cart'));
                
                return redirect()->route('frontend.addresses')
                    ->with('info', 'Please add an address before proceeding to checkout');
            }

            // Store cart and customer in request for controllers
            $request->merge([
                'validated_cart' => $cart,
                'authenticated_customer' => $customer,
                'address_data' => $addressData
            ]);

           
            return $next($request);
            
        } catch (\Exception $e) {
           
            
            return redirect()->route('frontend.shoping-cart')
                ->with('error', 'An error occurred. Please try again.');
        }
    }
}