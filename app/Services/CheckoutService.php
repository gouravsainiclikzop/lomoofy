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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use App\Mail\OrderPlaced;
use App\Mail\OrderStatusUpdated;

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
                $availableStock = 0;
                
                if ($variant) {
                    $variant->load('inventoryStocks');
                    $warehouseStock = $variant->inventoryStocks()->sum('quantity');
                    $reservedStock = $variant->inventoryStocks()->sum('reserved_quantity');
                    
                    if ($warehouseStock > 0) {
                        $availableStock = max(0, $warehouseStock - $reservedStock);
                    } else {
                        $availableStock = $variant->stock_quantity ?? 0;
                    }
                } else {
                    $availableStock = $product->stock_quantity ?? 0;
                }
          
                
                if ($item->quantity > $availableStock) {
                    $errors[] = "Sorry, \"{$productName}\" is not available in the quantity you requested. Only {$availableStock} items are available.";
                } else if ($availableStock <= 0) {
                    $errors[] = "Sorry, \"{$productName}\" is not available in the quantity you requested. Only {$availableStock} items are available.";
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
            
            // Recalculate cart totals using Cart model's method for consistency
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
                
                // Calculate item final price (after variant discounts)
                $itemFinalPrice = $calculateItemFinalPrice($variant);
                
                // Get GST settings from product
                $gstType = $product->gst_type ?? true; // Default to inclusive
                $gstPercentage = $product->gst_percentage ?? 0;
                
                // Calculate tax info for this item
                $taxType = $gstType;
                $taxPercentage = $gstPercentage;
                $taxValue = 0;
                
                if ($gstPercentage > 0) {
                    if (!$gstType) {
                        // Exclusive: Calculate tax on the final price (after discounts)
                        $taxValue = $itemFinalPrice * ($gstPercentage / 100);
                    } else {
                        // Inclusive: Tax is already included, calculate the tax portion
                        // For inclusive: tax = price - (price / (1 + tax_rate))
                        $taxValue = $itemFinalPrice - ($itemFinalPrice / (1 + ($gstPercentage / 100)));
                    }
                }
                
                // Calculate discount_percentage if discount_type is percentage
                $discountPercentage = null;
                if ($variant && $variant->discount_active && $variant->discount_type === 'percentage') {
                    $discountPercentage = $variant->discount_value;
                }
                
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
                    'original_variant_price' => $variant ? ($variant->price ?? null) : null,
                    'variant_sale_price' => $variant ? ($variant->sale_price ?? null) : null,
                    'discount_type' => $variant ? ($variant->discount_type ?? null) : null,
                    'discount_value' => $variant ? ($variant->discount_value ?? null) : null,
                    'discount_percentage' => $discountPercentage,
                    'discount_active' => $variant ? ($variant->discount_active ?? false) : false,
                    'tax_type' => $taxType,
                    'tax_value' => $taxValue,
                    'tax_percentage' => $taxPercentage > 0 ? $taxPercentage : null,
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
            
            // Send order confirmation email
            $this->sendOrderPlacedEmail($order);
            
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
    self::addDebugLog("DECREMENT_STOCK: Method called - variant: " . ($variant ? $variant->id : 'null') . ", manage_stock: " . ($variant ? ($variant->manage_stock ? 'true' : 'false') : 'n/a') . ", quantity: $quantity, warehouseId: " . ($warehouseId ?? 'null') . ", locationId: " . ($locationId ?? 'null'));
    
    // Variant priority - Always decrement stock if variant exists and has stock quantity
    // Check if there's stock to decrement (either in inventory_stocks or stock_quantity)
    if ($variant) {
        // Check if variant has stock (either in inventory_stocks or stock_quantity)
        $hasStock = false;
        if ($variant->inventoryStocks()->exists()) {
            $totalInventoryStock = $variant->inventoryStocks()->sum('quantity');
            $hasStock = $totalInventoryStock > 0;
        } else {
            $hasStock = ($variant->stock_quantity ?? 0) > 0;
        }
        
        // Always decrement if there's stock, regardless of manage_stock setting
        // This ensures inventory is always updated when orders are placed
        if ($hasStock || $variant->manage_stock) {
            self::addDebugLog("DECREMENT_STOCK: Inside IF - will decrement stock for variant {$variant->id}");
        
        // Check if warehouse-based inventory exists
        if ($variant->inventoryStocks()->exists()) {
            self::addDebugLog("DECREMENT_STOCK: Warehouse inventory exists for variant {$variant->id}");
            
            // Get or determine warehouse
            if (!$warehouseId) {
                $warehouse = \App\Models\Warehouse::getDefault();
                $warehouseId = $warehouse?->id;
                self::addDebugLog("DECREMENT_STOCK: Using default warehouse: " . ($warehouseId ?? 'null'));
            }
            
            if ($warehouseId) {
                // Warehouse inventory path - find or create inventory stock record
                $inventoryStock = \App\Models\InventoryStock::firstOrNew([
                    'product_variant_id' => $variant->id,
                    'warehouse_id' => $warehouseId,
                    'warehouse_location_id' => $locationId, // Optional location
                ]);
                
                $oldStock = $inventoryStock->quantity ?? 0;
                $newStock = max(0, $oldStock - $quantity);
                
                self::addDebugLog("DECREMENT_STOCK: Warehouse stock - old: $oldStock, quantity: $quantity, new: $newStock");
                
                // Update inventory stock
                $inventoryStock->quantity = $newStock;
                $inventoryStock->save();
                
                // Log inventory history
                if ($oldStock != $newStock) {
                    \App\Models\InventoryHistory::create([
                        'product_variant_id' => $variant->id,
                        'warehouse_id' => $warehouseId,
                        'warehouse_location_id' => $locationId,
                        'previous_quantity' => $oldStock,
                        'new_quantity' => $newStock,
                        'quantity_change' => -$quantity,
                        'change_type' => 'decrement',
                        'reference_type' => 'order',
                        'notes' => 'Stock decremented for order',
                        'user_id' => Auth::check() ? Auth::id() : null,
                    ]);
                }
                
                // Sync total to variant stock_quantity
                $variant->refresh();
                $totalStock = $variant->inventoryStocks()->sum('quantity');
                $variant->stock_quantity = $totalStock;
                $variant->stock_status = $totalStock > 0 ? 'in_stock' : 'out_of_stock';
                $variant->save();
                
                self::addDebugLog("DECREMENT_STOCK: Synced variant stock_quantity to $totalStock");
                return;
            }
        }
        
        // Fallback: Simple variant stock decrement - use direct database update
        self::addDebugLog("DECREMENT_STOCK: No warehouse inventory, using variant stock_quantity fallback");
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
         
        $variant->refresh();
        self::addDebugLog("DECREMENT_STOCK: After refresh - variant_id: {$variant->id}, model_stock_quantity: {$variant->stock_quantity}");

            return;
        } else {
            self::addDebugLog("DECREMENT_STOCK: SKIPPED - variant: " . ($variant ? $variant->id : 'null') . " - no stock to decrement and manage_stock is false");
        }
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
     * Increment stock for a product or variant (reverse of decrementStock)
     */
    private function incrementStock($product, $variant, int $quantity, $warehouseId = null, $locationId = null): void
    {
        self::addDebugLog("INCREMENT_STOCK: Method called - variant: " . ($variant ? $variant->id : 'null') . ", manage_stock: " . ($variant ? ($variant->manage_stock ? 'true' : 'false') : 'n/a') . ", quantity: $quantity, warehouseId: " . ($warehouseId ?? 'null') . ", locationId: " . ($locationId ?? 'null'));
         
        if ($variant && $variant->manage_stock) {
            self::addDebugLog("INCREMENT_STOCK: Inside IF - will increment stock for variant {$variant->id}");
             
            if ($variant->inventoryStocks()->exists()) {
                self::addDebugLog("INCREMENT_STOCK: Warehouse inventory exists for variant {$variant->id}");
                 
                if (!$warehouseId) {
                    $warehouse = \App\Models\Warehouse::getDefault();
                    $warehouseId = $warehouse?->id;
                    self::addDebugLog("INCREMENT_STOCK: Using default warehouse: " . ($warehouseId ?? 'null'));
                }
                
                if ($warehouseId) {
                    // Warehouse inventory path - find or create inventory stock record
                    $inventoryStock = \App\Models\InventoryStock::firstOrNew([
                        'product_variant_id' => $variant->id,
                        'warehouse_id' => $warehouseId,
                        'warehouse_location_id' => $locationId, // Optional location
                    ]);
                    
                    $oldStock = $inventoryStock->quantity ?? 0;
                    $newStock = $oldStock + $quantity;
                    
                    self::addDebugLog("INCREMENT_STOCK: Warehouse stock - old: $oldStock, quantity: $quantity, new: $newStock");
                    
                    $inventoryStock->quantity = $newStock;
                    $inventoryStock->save();
                    
                    // Log inventory history
                    if ($oldStock != $newStock) {
                        \App\Models\InventoryHistory::create([
                            'product_variant_id' => $variant->id,
                            'warehouse_id' => $warehouseId,
                            'warehouse_location_id' => $locationId,
                            'previous_quantity' => $oldStock,
                            'new_quantity' => $newStock,
                            'quantity_change' => $quantity,
                            'change_type' => 'increment',
                            'reference_type' => 'order_cancellation',
                            'notes' => 'Stock restored from cancelled order',
                            'user_id' => Auth::check() ? Auth::id() : null,
                        ]);
                    }
                    
                    // Sync total to variant stock_quantity
                    $variant->refresh();
                    $totalStock = $variant->inventoryStocks()->sum('quantity');
                    $variant->stock_quantity = $totalStock;
                    $variant->stock_status = $totalStock > 0 ? 'in_stock' : 'out_of_stock';
                    $variant->save();
                    
                    self::addDebugLog("INCREMENT_STOCK: Synced variant stock_quantity to $totalStock");
                    return;
                }
            }
            
            // Fallback: Simple variant stock increment - use direct database update
            self::addDebugLog("INCREMENT_STOCK: No warehouse inventory, using variant stock_quantity fallback");
            $oldStock = $variant->stock_quantity;
            $newStock = $oldStock + $quantity;
            
            self::addDebugLog("INCREMENT_STOCK: Before update - variant_id: {$variant->id}, old_stock: $oldStock, quantity: $quantity, new_stock: $newStock");
            
            // Use direct database update (simplest approach)
            $updated = DB::table('product_variants')
                ->where('id', $variant->id)
                ->update([
                    'stock_quantity' => $newStock,
                    'stock_status' => $newStock > 0 ? 'in_stock' : 'out_of_stock',
                    'updated_at' => now()
                ]);
            
            self::addDebugLog("INCREMENT_STOCK: After update - variant_id: {$variant->id}, rows_updated: $updated, new_stock: $newStock");
            
            // Verify the update actually happened by querying database directly (bypass Eloquent cache)
            $actualStock = DB::table('product_variants')
                ->where('id', $variant->id)
                ->value('stock_quantity');
            
            self::addDebugLog("INCREMENT_STOCK: Direct DB query result - variant_id: {$variant->id}, actual_stock_quantity: $actualStock");
            
            if ($actualStock != $newStock) {
                self::addDebugLog("INCREMENT_STOCK: WARNING - Stock mismatch! Expected: $newStock, Got from DB: $actualStock. Retrying update...");
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
                self::addDebugLog("INCREMENT_STOCK: After retry - variant_id: {$variant->id}, rows_updated: $retryUpdated, actual_stock_quantity: $actualStock");
            }
            
            // Refresh variant to get new stock value
            $variant->refresh();
            self::addDebugLog("INCREMENT_STOCK: After refresh - variant_id: {$variant->id}, model_stock_quantity: {$variant->stock_quantity}");

            return;
        } else {
            self::addDebugLog("INCREMENT_STOCK: SKIPPED - variant: " . ($variant ? $variant->id : 'null') . ", manage_stock: " . ($variant ? ($variant->manage_stock ? 'true' : 'false') : 'n/a'));
        }

        // Product fallback
        if ($product && $product->manage_stock) {
            self::addDebugLog("INCREMENT_STOCK: Incrementing product stock for product {$product->id}");
            $product->stock_quantity = ($product->stock_quantity ?? 0) + $quantity;
            $product->stock_status = $product->stock_quantity > 0 ? 'in_stock' : 'out_of_stock';
            $product->save();
        }
    }

    /**
     * Restore stock for order items (increment stock back)
     */
    public function restoreOrderStock(Order $order): void
    {
        self::addDebugLog("RESTORE_ORDER_STOCK: Starting for order {$order->id}");
        
        $order->load('items.product', 'items.variant');
        
        foreach ($order->items as $item) {
            $product = $item->product;
            if (!$product) {
                self::addDebugLog("RESTORE_ORDER_STOCK: Product not found for item {$item->id}");
                continue;
            }

            $variant = $item->variant;
            
            self::addDebugLog("RESTORE_ORDER_STOCK: Restoring stock for item {$item->id} - product: {$product->id}, variant: " . ($variant ? $variant->id : 'null') . ", quantity: {$item->quantity}");
            
            // Use warehouse from order item if available
            $this->incrementStock($product, $variant, $item->quantity, $item->warehouse_id, $item->warehouse_location_id);
        }
        
        self::addDebugLog("RESTORE_ORDER_STOCK: Completed for order {$order->id}");
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
    
    /**
     * Recalculate cart totals using final prices (after discounts) - matching checkout logic
     */
    private function recalculateCartTotalsWithFinalPricesForOrder(Cart $cart)
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
            if (method_exists($coupon, 'isActive') && method_exists($coupon, 'canBeUsed') && method_exists($coupon, 'calculateDiscount')) {
                if ($coupon->isActive() && $coupon->canBeUsed()) {
                    if (!property_exists($coupon, 'min_order_amount') || !$coupon->min_order_amount || $subtotal >= $coupon->min_order_amount) {
                        $discountAmount = $coupon->calculateDiscount($subtotal);
                    }
                }
            } elseif (property_exists($coupon, 'discount_type') && property_exists($coupon, 'discount_value')) {
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

    /**
     * Send order placed email to customer
     */
    protected function sendOrderPlacedEmail(Order $order)
    {
        try {
            $emailService = app(EmailService::class);
            
            // Check if email service is configured
            if (!$emailService->isConfigured()) {
                Log::info('Email service not configured, skipping order confirmation email', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number
                ]);
                return;
            }

            // Send email
            Mail::to($order->customer->email)->send(new OrderPlaced($order));

            Log::info('Order placed email sent successfully', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_email' => $order->customer->email
            ]);

        } catch (\Exception $e) {
            // Log error but don't fail the order
            Log::error('Failed to send order placed email', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send order status updated email to customer
     */
    public function sendOrderStatusEmail(Order $order, $oldStatus = null, $newStatus = null)
    {
        try {
            $emailService = app(EmailService::class);
            
            // Check if email service is configured
            if (!$emailService->isConfigured()) {
                Log::info('Email service not configured, skipping order status email', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number
                ]);
                return;
            }

            // Send email
            Mail::to($order->customer->email)->send(new OrderStatusUpdated($order, $oldStatus, $newStatus));

            Log::info('Order status email sent successfully', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_email' => $order->customer->email,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);

        } catch (\Exception $e) {
            // Log error but don't fail the update
            Log::error('Failed to send order status email', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $e->getMessage()
            ]);
        }
    }
}
