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
            \Log::info('CheckoutPrerequisites middleware started', ['url' => $request->url()]);
            
            // Resolve CheckoutService from container
            $checkoutService = app(CheckoutService::class);
            \Log::info('CheckoutService resolved successfully');
            
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
            })->active()->with('items.product', 'items.variant')->first();

            \Log::info('Cart found', ['cart_id' => $cart ? $cart->id : null, 'items_count' => $cart ? $cart->items->count() : 0]);

            // Check if cart exists and has items
            if (!$cart || $cart->items->count() === 0) {
                \Log::info('Cart empty or not found, redirecting to cart');
                return redirect()->route('frontend.shoping-cart')
                    ->with('error', 'Your cart is empty');
            }

            // Validate cart using checkout service
            \Log::info('Validating cart with checkout service');
            $cartValidation = $checkoutService->validateCart($cart);
            \Log::info('Cart validation result', ['valid' => $cartValidation['valid'], 'errors' => $cartValidation['errors'] ?? []]);
            
            if (!$cartValidation['valid']) {
                \Log::info('Cart validation failed, redirecting to cart');
                return redirect()->route('frontend.shoping-cart')
                    ->with('error', 'Cart validation failed: ' . implode(', ', $cartValidation['errors']));
            }

            // Check if customer has addresses
            \Log::info('Checking customer addresses');
            $addressData = $checkoutService->getCustomerAddressesForCheckout($customer);
            \Log::info('Address data', ['has_addresses' => $addressData['has_addresses'], 'address_count' => $addressData['addresses']->count()]);
            
            if (!$addressData['has_addresses']) {
                \Log::info('No addresses found, redirecting to addresses page');
                return redirect()->route('frontend.addresses')
                    ->with('info', 'Please add an address before proceeding to checkout');
            }

            // Store cart and customer in request for controllers
            $request->merge([
                'validated_cart' => $cart,
                'authenticated_customer' => $customer,
                'address_data' => $addressData
            ]);

            \Log::info('CheckoutPrerequisites middleware completed successfully');
            return $next($request);
            
        } catch (\Exception $e) {
            \Log::error('CheckoutPrerequisites middleware error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('frontend.shoping-cart')
                ->with('error', 'An error occurred. Please try again.');
        }
    }
}