<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Customer;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderApiController extends Controller
{
    protected $checkoutService;
    
    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }
    
    /**
     * Validate cart before checkout
     * GET /api/orders/validate-cart
     */
    public function validateCart(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $customerId = ($customer && $customer instanceof Customer) ? $customer->id : null;
        
        $sessionId = $request->input('session_id') 
                  ?? $request->header('X-Session-ID') 
                  ?? session()->getId();
        
        $cart = Cart::where(function($query) use ($customerId, $sessionId) {
            if ($customerId) {
                $query->where('customer_id', $customerId);
            } else {
                $query->where('session_id', $sessionId);
            }
        })->active()->with('items.product', 'items.variant')->first();
        
        $validation = $this->checkoutService->validateCart($cart);
        
        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'errors' => $validation['errors'],
            ], 400);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Cart is valid',
            'data' => [
                'cart_summary' => [
                    'items_count' => $cart->items->count(),
                    'subtotal' => $cart->subtotal,
                    'total_amount' => $cart->total_amount,
                ]
            ]
        ]);
    }
    
    /**
     * Get customer addresses for checkout
     * GET /api/orders/addresses
     */
    public function getAddresses(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Authentication required'],
            ], 401);
        }
        
        $addressData = $this->checkoutService->getCustomerAddressesForCheckout($customer);
        
        return response()->json([
            'success' => true,
            'data' => $addressData
        ]);
    }
    
    /**
     * Validate addresses for checkout
     * POST /api/orders/validate-addresses
     */
    public function validateAddresses(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Authentication required'],
            ], 401);
        }
        
        $shippingAddressId = $request->input('shipping_address_id');
        $billingAddressId = $request->input('billing_address_id');
        $billingSameAsShipping = $request->boolean('billing_same_as_shipping');
        
        $validation = $this->checkoutService->validateAddresses(
            $customer, 
            $shippingAddressId, 
            $billingAddressId, 
            $billingSameAsShipping
        );
        
        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'errors' => $validation['errors'],
            ], 400);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Addresses are valid',
            'data' => [
                'shipping_address' => $validation['shipping_address'],
                'billing_address' => $validation['billing_address'],
            ]
        ]);
    }

    /**
     * Create order from cart
     * POST /api/orders
     */
    public function store(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Authentication required'],
            ], 401);
        }
        
        try {
            // Validate request data
            $validatedData = $this->checkoutService->validateCheckoutRequest($request->all());
            
            // Get customer's cart
            $customerId = $customer->id;
            $sessionId = $request->input('session_id') 
                      ?? $request->header('X-Session-ID') 
                      ?? session()->getId();
            
            $cart = Cart::where(function($query) use ($customerId, $sessionId) {
                if ($customerId) {
                    $query->where('customer_id', $customerId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })->active()->with('items.product', 'items.variant')->first();
            
            // Validate cart
            $cartValidation = $this->checkoutService->validateCart($cart);
            if (!$cartValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'errors' => $cartValidation['errors'],
                ], 400);
            }
            
            // Validate addresses
            $addressValidation = $this->checkoutService->validateAddresses(
                $customer,
                $validatedData['shipping_address_id'] ?? null,
                $validatedData['billing_address_id'] ?? null,
                $validatedData['billing_same_as_shipping'] ?? false
            );
            
            if (!$addressValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'errors' => $addressValidation['errors'],
                ], 400);
            }
            
            // Create order
            $order = $this->checkoutService->createOrder(
                $cart,
                $customer,
                $addressValidation['shipping_address'],
                $addressValidation['billing_address'],
                $validatedData['billing_same_as_shipping'] ?? false,
                [
                    'payment_method' => $validatedData['payment_method'] ?? null,
                    'notes' => $validatedData['notes'] ?? null,
                ]
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'order' => $order->load('items'),
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                ]
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Order creation failed: ' . $e->getMessage(), [
                'customer_id' => $customer->id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Order creation failed. Please try again.'],
            ], 500);
        }
        $customer = Auth::guard('customer')->user();
        
        if (!$customer) {
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Customer authentication required'],
            ], 401);
        }
        
        $cart = Cart::where('customer_id', $customer->id)
            ->active()
            ->first();
        
        if (!$cart || $cart->items->count() === 0) {
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Cart is empty'],
            ], 400);
        }
        
        // TODO: Implement order creation logic
        
        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'data' => [
                'order_id' => null, // TODO: Return actual order ID
            ],
        ]);
    }
}
