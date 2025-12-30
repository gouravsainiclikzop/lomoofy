<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    // Temporary debug logs collection
    private static $debugLogs = [];
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
            
            // Get product name for error messages
            $productName = $product ? $product->name : ($item->product_name ?? 'Unknown Product');
            
            // Check if product exists and is active
            // if (!$product || !$product->is_active) {   
            //     $errors[] = "Product '{$productName}' is no longer available";
            //     continue;
            // }
  
            
            if ($variant && !$variant->is_active) {
                $errors[] = "Variant for '{$productName}' is no longer available";
                continue;
            } 
            
            // Check stock availability
            $stockSource = $variant ?: $product;
        
            if ($stockSource && $stockSource->manage_stock) {
                // Simple stock checking - use variant/product stock_quantity directly
                // Warehouse inventory logic is commented out for now (not fully implemented yet)
                $availableStock = $variant 
                    ? ($variant->stock_quantity ?? 0)
                    : ($product->stock_quantity ?? 0);
                
                // // Check warehouse-based inventory if available
                // if ($variant && $variant->inventoryStocks()->exists()) {
                //     $totalStock = $variant->inventoryStocks()->sum('quantity');
                //     $reservedStock = $variant->inventoryStocks()->sum('reserved_quantity');
                //     
                //     // If warehouse stocks exist but have zero total stock, fall back to variant stock_quantity
                //     if ($totalStock > 0) {
                //         $availableStock = max(0, $totalStock - $reservedStock);
                //     } else {
                //         // Fall back to variant stock_quantity when warehouse stocks have zero total stock
                //         $availableStock = $variant->stock_quantity ?? 0;
                //     }
                // } else {
                //     // Fallback to variant/product stock_quantity
                //     $availableStock = $variant 
                //         ? ($variant->available_stock ?? ($variant->stock_quantity ?? 0))
                //         : ($product->stock_quantity ?? 0);
                // }
          
                 
                if ($item->quantity > $availableStock) {
                    $errors[] = "Insufficient stock for '{$productName}'. Available: {$availableStock}, Requested: {$item->quantity}";
                } else if ($availableStock <= 0) {
                    $errors[] = "Product '{$productName}' is out of stock";
                } 
            }
            
            // Validate pricing (ensure prices haven't changed dramatically)
            // $currentPrice = $variant ? ($variant->price ?? $product->price) : $product->price;
            // $priceDifference = abs($currentPrice - $item->unit_price);
            // $priceChangeThreshold = $item->unit_price * 0.1; // 10% threshold
            
            // if ($priceDifference > $priceChangeThreshold) {
            //     $errors[] = "Price has changed for '{$productName}'. Please review your cart";
            // }
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
        // Reset debug logs
        self::$debugLogs = [];
        self::addDebugLog("createOrder called - cart_id: {$cart->id}, items_count: " . $cart->items->count());
        
        // Ensure cart items are loaded with variants
        $cart->load('items.variant', 'items.product');
        
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
            
            // Create order items and decrement stock
            self::addDebugLog("Starting order items loop - items: " . $cart->items->count());
            
            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;
                // Refresh variant from database to get latest stock data
                $variant = $cartItem->variant;
                
                self::addDebugLog("Processing cart item - item_id: {$cartItem->id}, variant: " . ($variant ? $variant->id : 'null'));
                
                if ($variant) {
                    $variant = \App\Models\ProductVariant::find($variant->id);
                    self::addDebugLog("Variant reloaded - id: {$variant->id}, manage_stock: " . ($variant->manage_stock ? 'true' : 'false') . ", stock_quantity: {$variant->stock_quantity}");
                } else {
                    self::addDebugLog("WARNING: Variant is null for cart item {$cartItem->id}");
                }
                
                // Warehouse logic commented out for now (not fully implemented yet)
                // // Get warehouse for this item (if warehouse-based inventory is used)
                // $warehouseId = null;
                // $locationId = null;
                // if ($variant && $variant->inventoryStocks()->exists()) {
                //     $warehouse = \App\Models\Warehouse::getDefault();
                //     $warehouseId = $warehouse ? $warehouse->id : null;
                // }
                $warehouseId = null;
                $locationId = null;
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant ? $variant->id : null,
                    'warehouse_id' => $warehouseId,
                    'warehouse_location_id' => $locationId,
                    'product_name' => $product->name,
                    'product_sku' => $variant ? $variant->sku : ($product->sku ?? '-'),
                    'variant_name' => $variant ? $variant->name : null,
                    'variant_sku' => $variant ? $variant->sku : null,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->unit_price,
                    'total_price' => $cartItem->total_price,
                ]);
                
                // Decrement stock (handles warehouse-based inventory)
                self::addDebugLog("About to decrement stock - variant: " . ($variant ? $variant->id : 'null') . ", quantity: {$cartItem->quantity}");
                
                if ($variant) {
                    self::addDebugLog("Calling decrementStock for variant {$variant->id}, manage_stock: " . ($variant->manage_stock ? 'true' : 'false'));
                    $this->decrementStock($product, $variant, $cartItem->quantity, $warehouseId, $locationId);
                } else {
                    self::addDebugLog("WARNING - Variant is null for cart item {$cartItem->id}");
                }
            }
            
            // Clear the cart
            $cart->items()->delete();
            $cart->delete();
            
            self::addDebugLog("Transaction completed successfully - Order ID: {$order->id}");
            
            // Verify stock one more time after transaction
            foreach ($order->items as $orderItem) {
                if ($orderItem->product_variant_id) {
                    $variant = \App\Models\ProductVariant::find($orderItem->product_variant_id);
                    if ($variant) {
                        self::addDebugLog("Final verification - variant_id: {$variant->id}, stock_quantity: {$variant->stock_quantity}");
                    }
                }
            }
            
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
    
 

private function decrementStock($product, $variant, int $quantity, $warehouseId = null, $locationId = null): void
{
    self::addDebugLog("DECREMENT_STOCK: Method called - variant: " . ($variant ? $variant->id : 'null') . ", manage_stock: " . ($variant ? ($variant->manage_stock ? 'true' : 'false') : 'n/a') . ", quantity: $quantity");
    
    // Variant priority
    if ($variant && $variant->manage_stock) {
        self::addDebugLog("DECREMENT_STOCK: Inside IF - will decrement stock for variant {$variant->id}");
        // Simple stock decrement - use variant stock_quantity directly
        // Warehouse inventory logic is commented out for now (not fully implemented yet)
        
        // // Warehouse inventory path
        // if ($variant->inventoryStocks()->exists()) {
        // 
        //     if (!$warehouseId) {
        //         $warehouse = \App\Models\Warehouse::getDefault();
        //         $warehouseId = $warehouse?->id;
        //     }
        // 
        //     $stocks = $variant->inventoryStocks() 
        //         ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
        //         ->orderBy('available_quantity', 'desc')
        //         ->lockForUpdate()
        //         ->get();
        //  
        //     // If no warehouse stocks found after filtering, fall back to variant stock_quantity
        //     if (!$stocks->isEmpty()) {
        //         $remaining = $quantity;
        // 
        //         foreach ($stocks as $stock) {
        //             if ($remaining <= 0) break;
        // 
        //             $available = max(0, $stock->available_quantity);
        //             $deduct = min($remaining, $available);
        // 
        //             if ($deduct <= 0) continue;
        // 
        //             $stock->quantity = max(0, $stock->quantity - $deduct);
        //             $stock->save();
        // 
        //             $remaining -= $deduct;
        //         }
        // 
        //         if ($remaining > 0) {
        //             throw ValidationException::withMessages([
        //                 'inventory' => "Insufficient stock for {$variant->sku}."
        //             ]);
        //         }
        // 
        //         $variant->stock_quantity = $variant->inventoryStocks()->sum('quantity');
        //         $variant->available_stock = $variant->inventoryStocks()->sum('available_quantity');
        //         $variant->stock_status = $variant->stock_quantity > 0 ? 'in_stock' : 'out_of_stock';
        //         $variant->save();
        // 
        //         return;
        //     }
        //     // If stocks are empty, fall through to variant fallback logic below
        // }

        // Simple variant stock decrement - use direct database update
        $oldStock = $variant->stock_quantity;
        $newStock = max(0, $oldStock - $quantity);
        
        self::addDebugLog("DECREMENT_STOCK: Before update - variant_id: {$variant->id}, old_stock: $oldStock, quantity: $quantity, new_stock: $newStock");
        
        // Use direct database update (simplest approach)
        $updated = DB::table('product_variants')
            ->where('id', $variant->id)
            ->update([
                'stock_quantity' => $newStock,
                'stock_status' => $newStock > 0 ? 'in_stock' : 'out_of_stock',
                'updated_at' => now()
            ]);
        
        self::addDebugLog("DECREMENT_STOCK: After update - variant_id: {$variant->id}, rows_updated: $updated, new_stock: $newStock");
        
        // Verify the update actually happened by querying database directly (bypass Eloquent cache)
        $actualStock = DB::table('product_variants')
            ->where('id', $variant->id)
            ->value('stock_quantity');
        
        self::addDebugLog("DECREMENT_STOCK: Direct DB query result - variant_id: {$variant->id}, actual_stock_quantity: $actualStock");
        
        if ($actualStock != $newStock) {
            self::addDebugLog("DECREMENT_STOCK: WARNING - Stock mismatch! Expected: $newStock, Got from DB: $actualStock. Retrying update...");
            // Retry the update
            $retryUpdated = DB::table('product_variants')
                ->where('id', $variant->id)
                ->update([
                    'stock_quantity' => $newStock,
                    'stock_status' => $newStock > 0 ? 'in_stock' : 'out_of_stock',
                    'updated_at' => now()
                ]);
            $actualStock = DB::table('product_variants')
                ->where('id', $variant->id)
                ->value('stock_quantity');
            self::addDebugLog("DECREMENT_STOCK: After retry - variant_id: {$variant->id}, rows_updated: $retryUpdated, actual_stock_quantity: $actualStock");
        }
        
        // Refresh variant to get new stock value
        $variant->refresh();
        self::addDebugLog("DECREMENT_STOCK: After refresh - variant_id: {$variant->id}, model_stock_quantity: {$variant->stock_quantity}");

        return;
    } else {
        self::addDebugLog("DECREMENT_STOCK: SKIPPED - variant: " . ($variant ? $variant->id : 'null') . ", manage_stock: " . ($variant ? ($variant->manage_stock ? 'true' : 'false') : 'n/a'));
    }

       

    // Product fallback
    if ($product && $product->manage_stock) {
        self::addDebugLog("DECREMENT_STOCK: Decrementing product stock for product {$product->id}");
        $product->stock_quantity = max(0, ($product->stock_quantity ?? 0) - $quantity);
        $product->stock_status = $product->stock_quantity > 0 ? 'in_stock' : 'out_of_stock';
        $product->save();
    }
}

    /**
     * Add debug log message
     */
    private static function addDebugLog(string $message): void
    {
        self::$debugLogs[] = date('Y-m-d H:i:s') . ' - ' . $message;
    }
    
    /**
     * Get debug logs
     */
    public static function getDebugLogs(): array
    {
        return self::$debugLogs;
    }




    /**
     * Decrement product/variant stock (handles warehouse-based inventory)
     */
    // private function decrementStock($product, $variant, int $quantity, $warehouseId = null, $locationId = null): void
    // {
    //     if ($variant) {
    //         // Handle variant stock
    //         if ($variant->manage_stock) {
    //             // If warehouse-based inventory exists, use it
    //             if ($variant->inventoryStocks()->exists()) {
    //                 // Get default warehouse if not specified
    //                 if (!$warehouseId) {
    //                     $warehouse = \App\Models\Warehouse::getDefault();
    //                     $warehouseId = $warehouse ? $warehouse->id : null;
    //                 }
                    
    //                 if ($warehouseId) {
    //                     $inventoryStock = \App\Models\InventoryStock::firstOrNew([
    //                         'product_variant_id' => $variant->id,
    //                         'warehouse_id' => $warehouseId,
    //                         'warehouse_location_id' => $locationId,
    //                     ]);
                        
    //                     $inventoryStock->quantity = max(0, ($inventoryStock->quantity ?? 0) - $quantity);
    //                     $inventoryStock->save();
                        
    //                     // Sync total to variant stock_quantity
    //                     $totalStock = $variant->inventoryStocks()->sum('quantity');
    //                     $variant->stock_quantity = $totalStock;
    //                 } else {
    //                     // Fallback to variant stock_quantity
    //                     $newQuantity = max(0, ($variant->stock_quantity ?? 0) - $quantity);
    //                     $variant->stock_quantity = $newQuantity;
    //                 }
    //             } else {
    //                 // Fallback to variant stock_quantity
    //                 $newQuantity = max(0, ($variant->stock_quantity ?? 0) - $quantity);
    //                 $variant->stock_quantity = $newQuantity;
    //             }
                
    //             // Update stock status
    //             $totalStock = $variant->total_stock_quantity ?? ($variant->stock_quantity ?? 0);
    //             if ($totalStock <= 0) {
    //                 $variant->stock_status = 'out_of_stock';
    //             }
    //             $variant->save();
    //         }
    //     } else {
    //         // Handle product stock (legacy - products don't have variants)
    //         if ($product->manage_stock) {
    //             $newQuantity = max(0, ($product->stock_quantity ?? 0) - $quantity);
    //             $product->stock_quantity = $newQuantity;
                
    //             // Update stock status
    //             if ($newQuantity <= 0) {
    //                 $product->stock_status = 'out_of_stock';
    //             }
    //             $product->save();
    //         }
    //     }
    // }
    
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
        // Convert billing_same_as_shipping to boolean if it's a string
        if (isset($data['billing_same_as_shipping'])) {
            $data['billing_same_as_shipping'] = filter_var($data['billing_same_as_shipping'], FILTER_VALIDATE_BOOLEAN);
        }
        
        $validator = Validator::make($data, [
            'shipping_address_id' => 'nullable|integer|exists:customer_addresses,id',
            'billing_address_id' => 'nullable|integer|exists:customer_addresses,id',
            'billing_same_as_shipping' => 'nullable|boolean',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        if ($validator->fails()) {
            Log::error('Checkout validation failed', [
                'errors' => $validator->errors()->toArray(),
                'data' => $data
            ]);
            throw new ValidationException($validator);
        }
        
        return $validator->validated();
    }
}
