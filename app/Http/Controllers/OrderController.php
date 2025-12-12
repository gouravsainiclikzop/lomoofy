<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use App\Models\InventoryStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Display orders listing page
     */
    public function index()
    {
        return view('admin.orders.index');
    }

    /**
     * Get orders data for DataTables
     */
    public function getData(Request $request)
    {
        $query = Order::with(['customer', 'items']);

        // Search
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $totalRecords = $query->count();
        $orders = $query->orderBy('created_at', 'desc')
                       ->skip($request->start)
                       ->take($request->length)
                       ->get();

        $data = $orders->map(function($order) {
            $itemsCount = $order->items->sum('quantity');
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'date' => $order->created_at->format('Y-m-d H:i:s'),
                'customer_name' => $order->customer ? $order->customer->full_name : '-',
                'customer_email' => $order->customer ? $order->customer->email : '-',
                'customer_phone' => $order->customer ? ($order->customer->phone ?? '-') : '-',
                'items_count' => $itemsCount,
                'total_amount' => $order->total_amount,
                'payment_method' => $order->payment_method ?? '-',
                'payment_status' => $order->payment_status,
                'status' => $order->status,
                'source' => $order->source ?? 'admin',
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => Order::count(),
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ]);
    }

    /**
     * Get customers for dropdown
     */
    public function getCustomers()
    {
        $customers = Customer::select('id', 'full_name', 'email', 'phone')
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $customers
        ]);
    }

    /**
     * Get full customer details for display
     */
    public function getCustomerDetails($id)
    {
        try {
            $customer = Customer::with(['addresses' => function($query) {
                $query->orderBy('is_default', 'desc')->orderBy('created_at', 'desc');
            }])->findOrFail($id);

            $customerData = [
                'id' => $customer->id,
                'full_name' => $customer->full_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'alternate_phone' => $customer->alternate_phone,
                'date_of_birth' => $customer->date_of_birth ? $customer->date_of_birth->format('Y-m-d') : null,
                'gender' => $customer->gender,
                'preferred_contact_method' => $customer->preferred_contact_method,
                'preferred_payment_method' => $customer->preferred_payment_method,
                'preferred_delivery_slot' => $customer->preferred_delivery_slot,
                'profile_image' => $customer->profile_image ? asset('storage/' . $customer->profile_image) : null,
                'addresses' => $customer->addresses->map(function($address) {
                    return [
                        'id' => $address->id,
                        'address_type' => $address->address_type,
                        'full_address' => $address->full_address,
                        'landmark' => $address->landmark,
                        'state' => $address->state,
                        'city' => $address->city,
                        'pincode' => $address->pincode,
                        'delivery_instructions' => $address->delivery_instructions,
                        'is_default' => $address->is_default,
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'data' => $customerData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }
    }

    /**
     * Get products for dropdown
     */
    public function getProducts()
    {
        try {
            $products = Product::with(['variants' => function($query) {
                $query->where('is_active', true)->orderBy('sort_order');
            }])
                ->where('status', 'published')
                ->orderBy('name')
                ->get()
                ->map(function($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'type' => $product->type,
                        'price' => $product->price ?? 0,
                        'variants' => $product->variants->map(function($variant) use ($product) {
                            return [
                                'id' => $variant->id,
                                'name' => $variant->name,
                                'sku' => $variant->sku,
                                'price' => $variant->price ?? $product->price ?? 0,
                                'stock_quantity' => $variant->total_stock_quantity ?? ($variant->stock_quantity ?? 0),
                                'available_stock' => $variant->available_stock ?? ($variant->stock_quantity ?? 0),
                            ];
                        })
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading products: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get warehouses for dropdown
     */
    public function getWarehouses()
    {
        $warehouses = Warehouse::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json([
            'success' => true,
            'data' => $warehouses
        ]);
    }

    /**
     * Get stock availability for a product/variant in a warehouse
     */
    public function getStockAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::findOrFail($request->product_id);
        $variant = $request->product_variant_id ? ProductVariant::findOrFail($request->product_variant_id) : null;
        $warehouseId = $request->warehouse_id;
        $quantity = $request->quantity ?? 1;

        $stockCheck = $this->checkStockAvailability($product, $variant, $quantity, $warehouseId);

        return response()->json([
            'success' => true,
            'data' => $stockCheck
        ]);
    }

    /**
     * Get a single order for editing
     */
    public function edit($id)
    {
        $order = Order::with(['customer', 'items.product', 'items.variant'])->findOrFail($id);
        
        $orderData = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'customer_id' => $order->customer_id,
            'status' => $order->status,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'subtotal' => $order->subtotal,
            'tax_amount' => $order->tax_amount,
            'shipping_amount' => $order->shipping_amount,
            'discount_amount' => $order->discount_amount,
            'total_amount' => $order->total_amount,
            'notes' => $order->notes,
            'items' => $order->items->map(function($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'warehouse_id' => $item->warehouse_id,
                    'warehouse_location_id' => $item->warehouse_location_id,
                    'product_name' => $item->product_name,
                    'variant_name' => $item->variant_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                ];
            })
        ];

        return response()->json([
            'success' => true,
            'data' => $orderData
        ]);
    }

    /**
     * Decrement stock for a product or variant from warehouse
     */
    private function decrementStock($product, $variant, $quantity, $warehouseId = null, $locationId = null)
    {
        if ($variant) {
            // Handle variant stock
            if ($variant->manage_stock) {
                // If warehouse is specified, use warehouse-based inventory
                if ($warehouseId) {
                    $inventoryStock = InventoryStock::firstOrNew([
                        'product_variant_id' => $variant->id,
                        'warehouse_id' => $warehouseId,
                        'warehouse_location_id' => $locationId,
                    ]);
                    
                    $inventoryStock->quantity = max(0, ($inventoryStock->quantity ?? 0) - $quantity);
                    $inventoryStock->save();
                    
                    // Sync total to variant stock_quantity
                    $totalStock = $variant->inventoryStocks()->sum('quantity');
                    $variant->stock_quantity = $totalStock;
                } else {
                    // Fallback to variant stock_quantity
                    $newQuantity = max(0, ($variant->stock_quantity ?? 0) - $quantity);
                    $variant->stock_quantity = $newQuantity;
                }
                
                // Update stock status
                $totalStock = $variant->total_stock_quantity ?? ($variant->stock_quantity ?? 0);
                if ($totalStock <= 0) {
                    $variant->stock_status = 'out_of_stock';
                }
                $variant->save();
            }
        } else {
            // Handle product stock (legacy - products don't have variants)
            if ($product->manage_stock) {
                $newQuantity = max(0, ($product->stock_quantity ?? 0) - $quantity);
                $product->stock_quantity = $newQuantity;
                
                // Update stock status
                if ($newQuantity <= 0) {
                    $product->stock_status = 'out_of_stock';
                }
                $product->save();
            }
        }
    }

    /**
     * Increment stock for a product or variant to warehouse
     */
    private function incrementStock($product, $variant, $quantity, $warehouseId = null, $locationId = null)
    {
        if ($variant) {
            // Handle variant stock
            if ($variant->manage_stock) {
                // If warehouse is specified, use warehouse-based inventory
                if ($warehouseId) {
                    $inventoryStock = InventoryStock::firstOrNew([
                        'product_variant_id' => $variant->id,
                        'warehouse_id' => $warehouseId,
                        'warehouse_location_id' => $locationId,
                    ]);
                    
                    $inventoryStock->quantity = ($inventoryStock->quantity ?? 0) + $quantity;
                    $inventoryStock->save();
                    
                    // Sync total to variant stock_quantity
                    $totalStock = $variant->inventoryStocks()->sum('quantity');
                    $variant->stock_quantity = $totalStock;
                } else {
                    // Fallback to variant stock_quantity
                    $variant->stock_quantity = ($variant->stock_quantity ?? 0) + $quantity;
                }
                
                // Update stock status
                $totalStock = $variant->total_stock_quantity ?? ($variant->stock_quantity ?? 0);
                if ($totalStock > 0) {
                    $variant->stock_status = 'in_stock';
                }
                $variant->save();
            }
        } else {
            // Handle product stock (legacy - products don't have variants)
            if ($product->manage_stock) {
                $product->stock_quantity = ($product->stock_quantity ?? 0) + $quantity;
                
                // Update stock status
                if ($product->stock_quantity > 0) {
                    $product->stock_status = 'in_stock';
                }
                $product->save();
            }
        }
    }

    /**
     * Restore stock for order items
     */
    private function restoreOrderStock($order)
    {
        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            if (!$product) continue;

            $variant = $item->product_variant_id ? ProductVariant::find($item->product_variant_id) : null;
            
            // Use warehouse from order item if available
            $this->incrementStock($product, $variant, $item->quantity, $item->warehouse_id, $item->warehouse_location_id);
        }
    }

    /**
     * Check stock availability from warehouse
     */
    private function checkStockAvailability($product, $variant, $quantity, $warehouseId = null)
    {
        if ($variant) {
            if (!$variant->manage_stock) {
                return ['available' => true, 'quantity' => null];
            }
            
            // If warehouse is specified, check warehouse stock
            if ($warehouseId) {
                $totalStock = InventoryStock::where('product_variant_id', $variant->id)
                    ->where('warehouse_id', $warehouseId)
                    ->sum('quantity');
                
                $reservedStock = InventoryStock::where('product_variant_id', $variant->id)
                    ->where('warehouse_id', $warehouseId)
                    ->sum('reserved_quantity');
                
                $availableStock = max(0, $totalStock - $reservedStock);
                
                return [
                    'available' => $availableStock >= $quantity,
                    'quantity' => $availableStock,
                    'total' => $totalStock,
                    'reserved' => $reservedStock
                ];
            } else {
                // Fallback to variant stock_quantity
                $availableStock = $variant->available_stock ?? ($variant->stock_quantity ?? 0);
                return [
                    'available' => $availableStock >= $quantity,
                    'quantity' => $availableStock
                ];
            }
        } else {
            // Product-level stock (legacy)
            if (!$product->manage_stock) {
                return ['available' => true, 'quantity' => null];
            }
            
            $availableStock = $product->stock_quantity ?? 0;
            return [
                'available' => $availableStock >= $quantity,
                'quantity' => $availableStock
            ];
        }
    }

    /**
     * Get warehouse for product (use default warehouse or first available)
     */
    private function getWarehouseForProduct($product, $warehouseId = null)
    {
        // Priority 1: Use provided warehouse
        if ($warehouseId) {
            return Warehouse::find($warehouseId);
        }
        
        // Priority 2: Use product's default warehouse
        if ($product->default_warehouse_id) {
            return Warehouse::find($product->default_warehouse_id);
        }
        
        // Priority 3: Use primary/default warehouse from warehouse master
        $primaryWarehouse = Warehouse::getDefault();
        if ($primaryWarehouse) {
            return $primaryWarehouse;
        }
        
        // Priority 4: Fallback to first active warehouse
        return Warehouse::where('status', 'active')->first();
    }

    /**
     * Store a new order
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'status' => 'nullable|in:pending,processing,shipped,delivered,cancelled,refunded',
            'payment_method' => 'nullable|string',
            'payment_status' => 'nullable|in:pending,paid,failed,refunded',
            'subtotal' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'shipping_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $order = Order::create([
                'source' => 'admin',
                'customer_id' => $request->customer_id,
                'status' => $request->status ?? 'pending',
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_status ?? 'pending',
                'subtotal' => $request->subtotal ?? 0,
                'tax_amount' => $request->tax_amount ?? 0,
                'shipping_amount' => $request->shipping_amount ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'total_amount' => $request->total_amount,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $variant = null;
                
                if (!empty($itemData['product_variant_id'])) {
                    $variant = ProductVariant::findOrFail($itemData['product_variant_id']);
                }

                // Get warehouse for this item
                $warehouse = $this->getWarehouseForProduct($product, $itemData['warehouse_id'] ?? null);
                $warehouseId = $warehouse ? $warehouse->id : null;
                $locationId = $itemData['warehouse_location_id'] ?? null;

                // Check stock availability
                $stockCheck = $this->checkStockAvailability($product, $variant, $itemData['quantity'], $warehouseId);
                if (!$stockCheck['available']) {
                    DB::rollBack();
                    $itemName = $variant ? $variant->name : $product->name;
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for {$itemName}. Available: {$stockCheck['quantity']}, Requested: {$itemData['quantity']}"
                    ], 422);
                }

                $unitPrice = $variant ? ($variant->price ?? $product->price) : $product->price;
                $totalPrice = $unitPrice * $itemData['quantity'];

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
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ]);

                // Decrement stock (only if order status is not cancelled/refunded)
                if (!in_array($order->status, ['cancelled', 'refunded'])) {
                    $this->decrementStock($product, $variant, $itemData['quantity'], $warehouseId, $locationId);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order->load(['customer', 'items'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show order invoice details
     */
    public function show($id)
    {
        $order = Order::with(['customer.addresses', 'items'])->findOrFail($id);
        
        // Get shipping address - try customer addresses first, then check notes for online orders
        $shippingAddress = null;
        if ($order->customer && $order->customer->addresses) {
            $shippingAddress = $order->customer->addresses->where('is_default', true)->first() 
                            ?? $order->customer->addresses->first();
        }
        
        // For online orders, check if address is stored in notes
        if (!$shippingAddress && $order->source === 'online' && $order->notes) {
            $notesData = json_decode($order->notes, true);
            if (isset($notesData['shipping_address'])) {
                $shippingAddress = (object) $notesData['shipping_address'];
            }
        }
        
        $orderData = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'customer' => [
                'id' => $order->customer ? $order->customer->id : null,
                'full_name' => $order->customer ? $order->customer->full_name : 'N/A',
                'email' => $order->customer ? $order->customer->email : 'N/A',
                'phone' => $order->customer ? ($order->customer->phone ?? 'N/A') : 'N/A',
            ],
            'shipping_address' => $shippingAddress,
            'items' => $order->items->map(function($item) {
                return [
                    'product_name' => $item->product_name,
                    'variant_name' => $item->variant_name,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'total_price' => $item->total_price,
                ];
            }),
            'subtotal' => $order->subtotal,
            'discount_amount' => $order->discount_amount,
            'shipping_amount' => $order->shipping_amount,
            'total_amount' => $order->total_amount,
            'payment_method' => $order->payment_method,
            'created_at' => $order->created_at->format('Y-m-d H:i:s'),
        ];

        return response()->json([
            'success' => true,
            'data' => $orderData
        ]);
    }

    /**
     * Update order status only (for inline editing)
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,refunded',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'data' => $order
        ]);
    }

    /**
     * Update an order
     */
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Prevent editing online orders
        if ($order->source === 'online') {
            return response()->json([
                'success' => false,
                'message' => 'Online orders cannot be edited'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'status' => 'nullable|in:pending,processing,shipped,delivered,cancelled,refunded',
            'payment_method' => 'nullable|string',
            'payment_status' => 'nullable|in:pending,paid,failed,refunded',
            'subtotal' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'shipping_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $oldStatus = $order->status;
            $newStatus = $request->status ?? $order->status;
            
            $order->update([
                'customer_id' => $request->customer_id,
                'status' => $newStatus,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_status ?? $order->payment_status,
                'subtotal' => $request->subtotal ?? 0,
                'tax_amount' => $request->tax_amount ?? 0,
                'shipping_amount' => $request->shipping_amount ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'total_amount' => $request->total_amount,
                'notes' => $request->notes,
            ]);

            // Handle status changes: restore stock if order is cancelled/refunded
            $wasActive = !in_array($oldStatus, ['cancelled', 'refunded']);
            $isNowCancelled = in_array($newStatus, ['cancelled', 'refunded']);
            
            // Restore stock from old items if order was active and is now cancelled/refunded
            if ($wasActive && $isNowCancelled) {
                $this->restoreOrderStock($order);
            }

            // Get old items before deleting (for stock restoration)
            $oldItems = $order->items()->get();
            
            // Restore stock from old items if order was active and items are being changed
            if ($wasActive && !$isNowCancelled) {
                foreach ($oldItems as $oldItem) {
                    $oldProduct = Product::find($oldItem->product_id);
                    if (!$oldProduct) continue;
                    
                    $oldVariant = $oldItem->product_variant_id ? ProductVariant::find($oldItem->product_variant_id) : null;
                    $this->incrementStock($oldProduct, $oldVariant, $oldItem->quantity, $oldItem->warehouse_id, $oldItem->warehouse_location_id);
                }
            }

            // Delete existing items
            $order->items()->delete();

            // Create new items
            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $variant = null;
                
                if (!empty($itemData['product_variant_id'])) {
                    $variant = ProductVariant::findOrFail($itemData['product_variant_id']);
                }

                // Get warehouse for this item
                $warehouse = $this->getWarehouseForProduct($product, $itemData['warehouse_id'] ?? null);
                $warehouseId = $warehouse ? $warehouse->id : null;
                $locationId = $itemData['warehouse_location_id'] ?? null;

                // Check stock availability
                $stockCheck = $this->checkStockAvailability($product, $variant, $itemData['quantity'], $warehouseId);
                if (!$stockCheck['available']) {
                    DB::rollBack();
                    $itemName = $variant ? $variant->name : $product->name;
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for {$itemName}. Available: {$stockCheck['quantity']}, Requested: {$itemData['quantity']}"
                    ], 422);
                }

                $unitPrice = $variant ? ($variant->price ?? $product->price) : $product->price;
                $totalPrice = $unitPrice * $itemData['quantity'];

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
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ]);

                // Decrement stock for new items (only if order is not cancelled/refunded)
                if (!$isNowCancelled) {
                    $this->decrementStock($product, $variant, $itemData['quantity'], $warehouseId, $locationId);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'data' => $order->load(['customer', 'items'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an order
     */
    public function destroy($id)
    {
        $order = Order::with('items')->findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Restore stock if order was not already cancelled/refunded
            if (!in_array($order->status, ['cancelled', 'refunded'])) {
                $this->restoreOrderStock($order);
            }
            
            $order->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting order: ' . $e->getMessage()
            ], 500);
        }
    }
}
