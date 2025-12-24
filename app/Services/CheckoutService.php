<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    /**
     * Validate cart for checkout
     */
    public function validateCart(Cart $cart): array
    {
        $errors = [];
        
        // Check if cart exists and has items
        if (!$cart || $cart->items->count() === 0) {
            $errors[] = 'Cart is empty';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Check if cart is expired
        if ($cart->isExpired()) {
            $errors[] = 'Cart has expired';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Validate each cart item
        foreach ($cart->items as $item) {
            $product = $item->product;
            $variant = $item->variant;
            
            // Check if product exists and is active
            if (!$product || !$product->is_active) {
                $errors[] = "Product '{$item->product_name}' is no longer available";
                continue;
            }
            
            // Check stock availability
            $stockSource = $variant ?: $product;
            if ($stockSource->manage_stock) {
                $availableStock = $stockSource->stock_quantity ?? 0;
                if ($item->quantity > $availableStock) {
                    $errors[] = "Insufficient stock for '{$item->product_name}'. Available: {$availableStock}, Requested: {$item->quantity}";
                }
            }
            
            // Validate pricing (ensure prices haven't changed dramatically)
            $currentPrice = $variant ? ($variant->price ?? $product->price) : $product->price;
            $priceDifference = abs($currentPrice - $item->unit_price);
            $priceChangeThreshold = $item->unit_price * 0.1; // 10% threshold
            
            if ($priceDifference > $priceChangeThreshold) {
                $errors[] = "Price has changed for '{$item->product_name}'. Please review your cart";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'cart' => $cart
        ];
    }
    
    /**
     * Validate customer addresses
     */
    public function validateAddresses(Customer $customer, ?int $shippingAddressId, ?int $billingAddressId, bool $billingSameAsShipping): array
    {
        $errors = [];
        
        // Get customer addresses
        $customerAddresses = $customer->addresses;
        
        if ($customerAddresses->count() === 0) {
            $errors[] = 'No addresses found. Please add an address first';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Validate shipping address
        $shippingAddress = null;
        if ($shippingAddressId) {
            $shippingAddress = $customerAddresses->find($shippingAddressId);
            if (!$shippingAddress) {
                $errors[] = 'Selected shipping address not found';
            }
        } else {
            // Use default address if no specific address selected
            $shippingAddress = $customer->defaultAddress;
            if (!$shippingAddress) {
                $errors[] = 'No default shipping address found';
            }
        }
        
        // Validate billing address
        $billingAddress = null;
        if ($billingSameAsShipping) {
            $billingAddress = $shippingAddress;
        } else {
            if ($billingAddressId) {
                $billingAddress = $customerAddresses->find($billingAddressId);
                if (!$billingAddress) {
                    $errors[] = 'Selected billing address not found';
                }
            } else {
                // Use default address if no specific address selected
                $billingAddress = $customer->defaultAddress;
                if (!$billingAddress) {
                    $errors[] = 'No default billing address found';
                }
            }
        }
        
        // Validate address completeness
        if ($shippingAddress && !$this->isAddressComplete($shippingAddress)) {
            $errors[] = 'Shipping address is incomplete';
        }
        
        if ($billingAddress && !$this->isAddressComplete($billingAddress)) {
            $errors[] = 'Billing address is incomplete';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'shipping_address' => $shippingAddress,
            'billing_address' => $billingAddress
        ];
    }
    
    /**
     * Check if address is complete
     */
    private function isAddressComplete(CustomerAddress $address): bool
    {
        $requiredFields = ['address_line1', 'city', 'state', 'pincode', 'country'];
        
        foreach ($requiredFields as $field) {
            if (empty($address->$field)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Create order from cart
     */
    public function createOrder(Cart $cart, Customer $customer, CustomerAddress $shippingAddress, CustomerAddress $billingAddress, bool $billingSameAsShipping, array $additionalData = []): Order
    {
        return DB::transaction(function () use ($cart, $customer, $shippingAddress, $billingAddress, $billingSameAsShipping, $additionalData) {
            
            // Recalculate cart totals one final time
            $cart->recalculateTotals();
            
            // Create immutable address snapshots
            $shippingSnapshot = $this->createAddressSnapshot($shippingAddress);
            $billingSnapshot = $billingSameAsShipping ? $shippingSnapshot : $this->createAddressSnapshot($billingAddress);
            
            // Create order
            $order = Order::create([
                'source' => 'frontend',
                'customer_id' => $customer->id,
                'status' => 'pending',
                'subtotal' => $cart->subtotal,
                'tax_amount' => $cart->tax_amount,
                'shipping_amount' => $cart->shipping_amount,
                'discount_amount' => $cart->discount_amount,
                'total_amount' => $cart->total_amount,
                'payment_method' => $additionalData['payment_method'] ?? null,
                'payment_status' => 'pending',
                'notes' => $additionalData['notes'] ?? null,
                'shipping_address' => $shippingSnapshot,
                'billing_address' => $billingSnapshot,
                'billing_same_as_shipping' => $billingSameAsShipping,
                'shipping_address_id' => $shippingAddress->id,
                'billing_address_id' => $billingAddress->id,
            ]);
            
            // Create order items
            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;
                $variant = $cartItem->variant;
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant ? $variant->id : null,
                    'product_name' => $product->name,
                    'product_sku' => $variant ? $variant->sku : ($product->sku ?? '-'),
                    'variant_name' => $variant ? $variant->name : null,
                    'variant_sku' => $variant ? $variant->sku : null,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->unit_price,
                    'total_price' => $cartItem->total_price,
                ]);
                
                // Decrement stock
                $this->decrementStock($product, $variant, $cartItem->quantity);
            }
            
            // Clear the cart
            $cart->items()->delete();
            $cart->delete();
            
            return $order;
        });
    }
    
    /**
     * Create immutable address snapshot
     */
    private function createAddressSnapshot(CustomerAddress $address): array
    {
        return [
            'address_type' => $address->address_type,
            'address_line1' => $address->address_line1,
            'address_line2' => $address->address_line2,
            'landmark' => $address->landmark,
            'country' => $address->country,
            'state' => $address->state,
            'city' => $address->city,
            'pincode' => $address->pincode,
            'delivery_instructions' => $address->delivery_instructions,
            'created_at' => now()->toISOString(),
        ];
    }
    
    /**
     * Decrement product/variant stock
     */
    private function decrementStock($product, $variant, int $quantity): void
    {
        $stockSource = $variant ?: $product;
        
        if ($stockSource->manage_stock && $stockSource->stock_quantity >= $quantity) {
            $stockSource->decrement('stock_quantity', $quantity);
        }
    }
    
    /**
     * Get customer addresses for checkout
     */
    public function getCustomerAddressesForCheckout(Customer $customer): array
    {
        $addresses = $customer->addresses()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $defaultShipping = $customer->defaultShippingAddress;
        $defaultBilling = $customer->defaultBillingAddress;
        
        return [
            'addresses' => $addresses,
            'default_shipping' => $defaultShipping,
            'default_billing' => $defaultBilling,
            'has_addresses' => $addresses->count() > 0,
            'single_address' => $addresses->count() === 1,
        ];
    }
    
    /**
     * Validate checkout request data
     */
    public function validateCheckoutRequest(array $data): array
    {
        $validator = Validator::make($data, [
            'shipping_address_id' => 'nullable|integer|exists:customer_addresses,id',
            'billing_address_id' => 'nullable|integer|exists:customer_addresses,id',
            'billing_same_as_shipping' => 'boolean',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        return $validator->validated();
    }
}
