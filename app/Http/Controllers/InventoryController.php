<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Models\InventoryStock;
use App\Models\InventoryHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class InventoryController extends Controller
{
    /**
     * Display inventory management page.
     */
    public function index()
    {
        return view('admin.inventory.index');
    }

    /**
     * Get inventory data for DataTable.
     * Returns a flat list showing all variants directly (not nested under products).
     */
    public function getData(Request $request)
    {
        // Query variants directly with their parent products
        // Only include variants whose products are NOT soft-deleted
        $query = ProductVariant::with([
            'product' => function($q) {
                $q->with(['primaryImage', 'images', 'defaultWarehouse']);
            },
            'images' => function($q) {
                $q->orderBy('sort_order')->orderBy('id');
            },
            'inventoryStocks.warehouse',
            'inventoryStocks.warehouseLocation'
        ])->whereHas('product', function($q) {
            // Only get variants where product is not soft-deleted
            $q->whereNull('deleted_at');
        });

        // Filter by warehouse if provided
        if ($request->has('warehouse_id') && $request->warehouse_id) {
            $query->whereHas('inventoryStocks', function($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id);
            });
        }

        // Search
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhereHas('product', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by stock status
        if ($request->has('stock_status') && $request->stock_status) {
            $query->where('stock_status', $request->stock_status);
        }

        // Filter low stock
        if ($request->has('low_stock') && $request->low_stock == '1') {
            $query->where('manage_stock', true)
                  ->where(function($q) {
                      $q->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                        ->orWhere('stock_quantity', '<=', 0);
                  });
        }

        $totalRecords = $query->count();
        $variants = $query->orderBy('created_at', 'desc')
                         ->skip($request->start ?? 0)
                         ->take($request->length ?? 25)
                         ->get();

        $data = $variants->map(function($variant) {
            $product = $variant->product;
            
            // Handle case where product is null (soft deleted or doesn't exist)
            if (!$product) {
                return [
                    'id' => $variant->id,
                    'is_variant' => true,
                    'name' => $variant->name . ' (Orphaned - Product Deleted)',
                    'sku' => $variant->sku,
                    'image_url' => asset('assets/images/placeholder.jpg'),
                    'type' => 'Variant',
                    'product_id' => null,
                    'product_name' => 'Product Deleted',
                    'product_sku' => '-',
                    'manage_stock' => $variant->manage_stock ? 'Yes' : 'No',
                    'stock_quantity' => $variant->stock_quantity ?? 0,
                    'stock_status' => 'Unknown',
                    'stock_status_value' => 'out_of_stock',
                    'low_stock_threshold' => $variant->low_stock_threshold ?? 0,
                    'stock_location' => '-',
                    'allow_backorder' => '-',
                    'is_low_stock' => false,
                    'created_at' => $variant->created_at->format('Y-m-d H:i:s'),
                ];
            }
            
            // Calculate stock from warehouse-based inventory or fallback to variant stock_quantity
            $totalStockQty = $variant->total_stock_quantity ?? ($variant->stock_quantity ?? 0);
            $availableStock = $variant->available_stock ?? $totalStockQty;
            
            // Calculate stock status based on total stock quantity if manage_stock is enabled
            $calculatedStockStatus = $variant->stock_status;
            if ($variant->manage_stock) {
                $calculatedStockStatus = $totalStockQty > 0 ? 'in_stock' : 'out_of_stock';
            }
            
            // Get warehouse breakdown - group by warehouse and sum quantities across all locations
            $warehouseBreakdown = $variant->inventoryStocks->groupBy('warehouse_id')->map(function($stocks, $warehouseId) {
                $firstStock = $stocks->first();
                return [
                    'warehouse_id' => $warehouseId,
                    'warehouse_name' => $firstStock->warehouse->name ?? 'N/A',
                    'warehouse_code' => $firstStock->warehouse->code ?? 'N/A',
                    'quantity' => $stocks->sum('quantity'),
                    'reserved_quantity' => $stocks->sum('reserved_quantity'),
                    'available_quantity' => $stocks->sum('available_quantity'),
                ];
            })->values()->toArray();
            
            // Get variant image or product image
            $variantImage = $variant->images->first();
            $imageUrl = $variantImage 
                ? asset('storage/' . $variantImage->image_path)
                : ($product->image_url ?? asset('assets/images/placeholder.jpg'));
            
            // Calculate low stock: stock quantity <= low stock threshold (and > 0, otherwise it's out of stock)
            $lowStockThreshold = $variant->low_stock_threshold ?? 0;
            $isLowStock = $variant->manage_stock && $totalStockQty > 0 && $totalStockQty <= $lowStockThreshold;
            
            return [
                'id' => $variant->id,
                'is_variant' => true,
                'name' => $variant->name,
                'sku' => $variant->sku,
                'image_url' => $imageUrl,
                'type' => 'Variant',
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => '-', // Products don't have SKU at product level (variant-level only)
                'default_warehouse_id' => $product->default_warehouse_id,
                'default_warehouse_name' => $product->defaultWarehouse->name ?? null,
                'manage_stock' => $variant->manage_stock ? 'Yes' : 'No',
                'stock_quantity' => $totalStockQty,
                'available_stock' => $availableStock,
                'stock_status' => ucfirst(str_replace('_', ' ', $calculatedStockStatus)),
                'stock_status_value' => $calculatedStockStatus,
                'low_stock_threshold' => $lowStockThreshold,
                'warehouse_breakdown' => $warehouseBreakdown,
                'warehouse_count' => count($warehouseBreakdown),
                'stock_location' => '-', // Variants don't have stock_location
                'allow_backorder' => '-', // Variants don't have allow_backorder
                'is_low_stock' => $isLowStock,
                'created_at' => $variant->created_at->format('Y-m-d H:i:s'),
            ];
        })->filter(); // Remove any null entries

        return response()->json([
            'draw' => intval($request->draw ?? 1),
            'recordsTotal' => ProductVariant::whereHas('product', function($q) {
                $q->whereNull('deleted_at');
            })->count(),
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ]);
    }

    /**
     * Update inventory for a product or variant.
     */
    public function update(Request $request, $id)
    {
        // Check if updating variant or product
        $isVariant = $request->has('is_variant') && $request->is_variant;
        
        if ($isVariant) {
            $variant = ProductVariant::find($id);
            if (!$variant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Variant not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'manage_stock' => 'nullable|boolean',
                'stock_quantity' => 'nullable|integer|min:0',
                'low_stock_threshold' => 'nullable|integer|min:0',
                'warehouse_id' => 'required_with:stock_quantity|exists:warehouses,id',
                'warehouse_location_id' => 'nullable|exists:warehouse_locations,id',
                'update_type' => 'nullable|in:set,increment,decrement', // How to update: set value, add to, or subtract from
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            try {
                DB::beginTransaction();
                
                $manageStock = $request->has('manage_stock') ? (bool)$request->manage_stock : $variant->manage_stock;
                
                // Update variant-level settings
                $updateData = [
                    'manage_stock' => $manageStock,
                ];
                
                // Update low_stock_threshold if provided
                if ($request->has('low_stock_threshold')) {
                    $updateData['low_stock_threshold'] = $request->low_stock_threshold;
                }
                
                $variant->update($updateData);
                
                // Handle warehouse-based stock update (required when stock_quantity is provided)
                if ($request->has('stock_quantity')) {
                    if (!$request->has('warehouse_id') || !$request->warehouse_id) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Warehouse selection is required to update stock quantity',
                            'errors' => ['warehouse_id' => ['Warehouse is required when updating stock quantity']]
                        ], 422);
                    }
                    
                    $warehouseId = $request->warehouse_id;
                    $locationId = $request->warehouse_location_id ?: null; // Convert empty string to null
                    $stockQuantity = $request->stock_quantity;
                    $updateType = $request->update_type ?? 'set';
                    
                    // Find or create inventory stock record
                    $inventoryStock = InventoryStock::firstOrNew([
                        'product_variant_id' => $variant->id,
                        'warehouse_id' => $warehouseId,
                        'warehouse_location_id' => $locationId,
                    ]);
                    
                    $previousQuantity = $inventoryStock->quantity ?? 0;
                    
                    // Update quantity based on update type
                    if ($updateType === 'increment') {
                        $inventoryStock->quantity = $previousQuantity + $stockQuantity;
                    } elseif ($updateType === 'decrement') {
                        $inventoryStock->quantity = max(0, $previousQuantity - $stockQuantity);
                    } else {
                        // 'set' - set the exact value
                        $inventoryStock->quantity = $stockQuantity;
                    }
                    
                    $inventoryStock->save();
                    
                    // Log history if quantity changed
                    if ($previousQuantity != $inventoryStock->quantity) {
                        InventoryHistory::create([
                            'product_variant_id' => $variant->id,
                            'warehouse_id' => $warehouseId,
                            'warehouse_location_id' => $locationId, // Use the same locationId (null if empty)
                            'previous_quantity' => $previousQuantity,
                            'new_quantity' => $inventoryStock->quantity,
                            'quantity_change' => $inventoryStock->quantity - $previousQuantity,
                            'change_type' => $updateType,
                            'reference_type' => 'manual',
                            'notes' => $request->notes ?? null,
                            'user_id' => Auth::id(),
                        ]);
                    }
                    
                    // Sync total to variant stock_quantity for backward compatibility
                    // Use fresh query to ensure we get the latest data
                    $variant->refresh();
                    $totalStock = $variant->inventoryStocks()->sum('quantity');
                    $variant->stock_quantity = $totalStock;
                    // Auto-calculate stock status: 0 = out_of_stock, > 0 = in_stock
                    $variant->stock_status = $totalStock > 0 ? 'in_stock' : 'out_of_stock';
                    $variant->save();
                }

                DB::commit();
                
                // Refresh variant to get updated relationships
                $variant->refresh();
                $variant->load(['inventoryStocks.warehouse', 'inventoryStocks.warehouseLocation']);

                // Calculate total stock directly from inventory stocks (more reliable than accessor)
                $totalStockFromWarehouses = $variant->inventoryStocks()->sum('quantity');
                $totalStock = $totalStockFromWarehouses > 0 ? $totalStockFromWarehouses : ($variant->stock_quantity ?? 0);
                $totalReserved = $variant->inventoryStocks()->sum('reserved_quantity');
                $availableStock = max(0, $totalStock - $totalReserved);
                
                // Auto-calculate: 0 = out_of_stock, > 0 = in_stock
                $displayStockStatus = $totalStock > 0 ? 'in_stock' : 'out_of_stock';
                
                // Calculate low stock flag
                $lowStockThreshold = $variant->low_stock_threshold ?? 0;
                $isLowStock = $variant->manage_stock && $totalStock > 0 && $totalStock <= $lowStockThreshold;
                
                return response()->json([
                    'success' => true,
                    'message' => 'Variant inventory updated successfully',
                    'variant' => [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'sku' => $variant->sku,
                        'manage_stock' => $variant->manage_stock ? 'Yes' : 'No',
                        'stock_quantity' => $totalStock,
                        'available_stock' => $availableStock,
                        'stock_status' => ucfirst(str_replace('_', ' ', $displayStockStatus)),
                        'stock_status_value' => $displayStockStatus,
                        'low_stock_threshold' => $lowStockThreshold,
                        'is_low_stock' => $isLowStock,
                        'warehouse_breakdown' => $variant->inventoryStocks->map(function($stock) {
                            return [
                                'warehouse_id' => $stock->warehouse_id,
                                'warehouse_name' => $stock->warehouse->name ?? 'N/A',
                                'quantity' => $stock->quantity,
                                'available_quantity' => $stock->available_quantity,
                            ];
                        })->toArray(),
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating variant inventory: ' . $e->getMessage()
                ], 500);
            }
        } else {
            $product = Product::find($id);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            // Check if product has variants
            if ($product->variants && $product->variants->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'This product has variants. Please update variant stocks individually.'
                ], 422);
            }

            $rules = [
                'manage_stock' => 'nullable|boolean',
                'stock_quantity' => 'nullable|integer|min:0',
                'stock_status' => 'nullable|in:in_stock,out_of_stock,on_backorder',
                'allow_backorder' => 'nullable|boolean',
            ];
            
            // Only validate these fields if they exist in the products table
            if (Schema::hasColumn('products', 'low_stock_threshold')) {
                $rules['low_stock_threshold'] = 'nullable|integer|min:0';
            }
            if (Schema::hasColumn('products', 'stock_location')) {
                $rules['stock_location'] = 'nullable|string|max:255';
            }
            
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            try {
                $updateData = [
                    'manage_stock' => $request->has('manage_stock') ? (bool)$request->manage_stock : $product->manage_stock,
                    'stock_quantity' => $request->has('stock_quantity') ? $request->stock_quantity : $product->stock_quantity,
                    'stock_status' => $request->stock_status ?? $product->stock_status,
                    'allow_backorder' => $request->has('allow_backorder') ? (bool)$request->allow_backorder : $product->allow_backorder,
                ];
                
                // Only update these fields if they exist in the products table
                if (Schema::hasColumn('products', 'low_stock_threshold')) {
                    $updateData['low_stock_threshold'] = $request->has('low_stock_threshold') ? $request->low_stock_threshold : $product->low_stock_threshold;
                }
                if (Schema::hasColumn('products', 'stock_location')) {
                    $updateData['stock_location'] = $request->stock_location ?? $product->stock_location;
                }
                
                $product->update($updateData);

                return response()->json([
                    'success' => true,
                    'message' => 'Inventory updated successfully',
                    'product' => [
                        'id' => $product->id,
                        'manage_stock' => $product->manage_stock ? 'Yes' : 'No',
                        'stock_quantity' => $product->stock_quantity ?? 0,
                        'stock_status' => ucfirst(str_replace('_', ' ', $product->stock_status)),
                        'stock_status_value' => $product->stock_status,
                    'allow_backorder' => $product->allow_backorder ? 'Yes' : 'No',
                    'low_stock_threshold' => isset($product->low_stock_threshold) ? $product->low_stock_threshold : 0,
                    'stock_location' => isset($product->stock_location) ? $product->stock_location : '-',
                    'is_low_stock' => $product->manage_stock && $product->stock_quantity <= (isset($product->low_stock_threshold) ? $product->low_stock_threshold : 0),
                    ]
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating inventory: ' . $e->getMessage()
                ], 500);
            }
        }
    }

    /**
     * Bulk add stock to multiple products.
     */
    public function bulkAddStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'required|integer|exists:products,id',
            'variant_ids' => 'nullable|array',
            'variant_ids.*' => 'required|integer|exists:product_variants,id',
            'stock_quantity' => 'required|integer|min:0',
            'warehouse_id' => 'required_with:variant_ids|exists:warehouses,id',
            'warehouse_location_id' => 'nullable|exists:warehouse_locations,id',
            'update_type' => 'nullable|in:set,increment,decrement',
        ]);
        
        // Additional validation: warehouse is required for variants
        if ($validator->passes() && $request->has('variant_ids') && count($request->variant_ids ?? []) > 0) {
            if (!$request->has('warehouse_id') || !$request->warehouse_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Warehouse selection is required when adding stock to variants',
                    'errors' => ['warehouse_id' => ['Warehouse is required when updating variant stock']]
                ], 422);
            }
        }

        // At least one of product_ids or variant_ids must be provided
        if ((!$request->has('product_ids') || count($request->product_ids) === 0) && 
            (!$request->has('variant_ids') || count($request->variant_ids) === 0)) {
            return response()->json([
                'success' => false,
                'message' => 'Please select at least one product or variant',
                'errors' => []
            ], 422);
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
            
            $productIds = $request->product_ids ?? [];
            $variantIds = $request->variant_ids ?? [];
            $stockQuantity = $request->stock_quantity;
            $warehouseId = $request->warehouse_id;
            $locationId = $request->warehouse_location_id ?: null; // Convert empty string to null
            $updateType = $request->update_type ?? 'increment';
            
            $updated = 0;
            $skipped = 0;
            $errors = [];

            // Process simple products (legacy support - not recommended for warehouse-based inventory)
            foreach ($productIds as $productId) {
                $product = Product::find($productId);
                
                if (!$product) {
                    $skipped++;
                    continue;
                }

                // Skip products with variants (they should be updated via variants)
                if ($product->variants && $product->variants->count() > 0) {
                    $skipped++;
                    $errors[] = "Product '{$product->name}' has variants and was skipped. Please update variants individually.";
                    continue;
                }

                // Enable stock management if not already enabled
                if (!$product->manage_stock) {
                    $product->manage_stock = true;
                }

                // Legacy: update product stock directly (not warehouse-based)
                $currentStock = $product->stock_quantity ?? 0;
                if ($updateType === 'increment') {
                    $newStock = $currentStock + $stockQuantity;
                } elseif ($updateType === 'decrement') {
                    $newStock = max(0, $currentStock - $stockQuantity);
                } else {
                    $newStock = $stockQuantity;
                }

                // Auto-calculate stock status
                $finalStockStatus = $newStock > 0 ? 'in_stock' : 'out_of_stock';

                $product->update([
                    'manage_stock' => true,
                    'stock_quantity' => $newStock,
                    'stock_status' => $finalStockStatus,
                ]);

                $updated++;
            }

            // Process variants with warehouse support
            foreach ($variantIds as $variantId) {
                $variant = ProductVariant::find($variantId);
                
                if (!$variant) {
                    $skipped++;
                    continue;
                }

                // Enable stock management if not already enabled
                if (!$variant->manage_stock) {
                    $variant->manage_stock = true;
                    $variant->save();
                }

                // Warehouse-based stock update (required for variants)
                if (!$warehouseId) {
                    $errors[] = "Variant '{$variant->name}' requires warehouse selection to update stock";
                    $skipped++;
                    continue;
                }
                
                $inventoryStock = InventoryStock::firstOrNew([
                    'product_variant_id' => $variant->id,
                    'warehouse_id' => $warehouseId,
                    'warehouse_location_id' => $locationId,
                ]);
                
                $previousQuantity = $inventoryStock->quantity ?? 0;
                
                // Update quantity based on update type
                if ($updateType === 'increment') {
                    $inventoryStock->quantity = $previousQuantity + $stockQuantity;
                } elseif ($updateType === 'decrement') {
                    $inventoryStock->quantity = max(0, $previousQuantity - $stockQuantity);
                } else {
                    // 'set' - set the exact value
                    $inventoryStock->quantity = $stockQuantity;
                }
                
                $inventoryStock->save();
                
                // Log history if quantity changed
                if ($previousQuantity != $inventoryStock->quantity) {
                    InventoryHistory::create([
                        'product_variant_id' => $variant->id,
                        'warehouse_id' => $warehouseId,
                        'warehouse_location_id' => $locationId, // Use the same locationId (null if empty)
                        'previous_quantity' => $previousQuantity,
                        'new_quantity' => $inventoryStock->quantity,
                        'quantity_change' => $inventoryStock->quantity - $previousQuantity,
                        'change_type' => $updateType,
                        'reference_type' => 'bulk_add',
                        'notes' => 'Bulk stock update',
                        'user_id' => Auth::id(),
                    ]);
                }
                
                // Sync total to variant stock_quantity
                $variant->refresh();
                $totalStock = $variant->inventoryStocks()->sum('quantity');
                $variant->stock_quantity = $totalStock;
                $variant->stock_status = $totalStock > 0 ? 'in_stock' : 'out_of_stock';
                $variant->save();

                $updated++;
            }
            
            DB::commit();

            DB::commit();
            
            $message = "Stock added to {$updated} item(s)";
            if ($skipped > 0) {
                $message .= ". {$skipped} item(s) skipped";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'updated' => $updated,
                'skipped' => $skipped,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error adding stock: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get warehouse and location codes reference for import
     */
    public function getWarehouseCodesReference()
    {
        $warehouses = Warehouse::where('status', 'active')
            ->with(['locations' => function($query) {
                $query->where('status', 'active')
                      ->orderBy('rack')
                      ->orderBy('shelf')
                      ->orderBy('bin');
            }])
            ->orderBy('code')
            ->get();
        
        $data = $warehouses->map(function($warehouse) {
            return [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'code' => $warehouse->code,
                'locations' => $warehouse->locations->map(function($location) {
                    return [
                        'id' => $location->id,
                        'code' => $location->location_code,
                        'rack' => $location->rack,
                        'shelf' => $location->shelf,
                        'bin' => $location->bin,
                    ];
                })->values(),
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Download sample import file.
     */
    public function downloadSample(Request $request)
    {
        $type = $request->get('type', 'default'); // default, all, low_stock
        $warehouseId = $request->get('warehouse_id'); // Optional warehouse filter
        
        $query = ProductVariant::with(['product', 'inventoryStocks.warehouse', 'inventoryStocks.warehouseLocation'])
            ->whereHas('product', function($q) {
                $q->whereNull('deleted_at');
            });
        
        // Filter based on type
        if ($type === 'low_stock') {
            $query->where('manage_stock', true)
                  ->where(function($q) {
                      $q->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                        ->orWhere('stock_quantity', '<=', 0);
                  });
        }
        
        // Filter by warehouse if provided
        if ($warehouseId) {
            $query->whereHas('inventoryStocks', function($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }
        
        // Limit results for 'all' to prevent huge files
        if ($type === 'all') {
            $variants = $query->orderBy('sku')->limit(1000)->get();
        } elseif ($type === 'low_stock') {
            $variants = $query->orderBy('sku')->get();
        } else {
            // Default: return empty sample with just headers
            $variants = collect();
        }
        
        // Get warehouses for reference
        $warehouses = Warehouse::where('status', 'active')
            ->orderBy('code')
            ->get(['id', 'name', 'code']);
        
        $filename = 'inventory_import_sample_' . $type . '_' . date('Y-m-d') . '.csv';
        if ($warehouseId) {
            $warehouse = Warehouse::find($warehouseId);
            if ($warehouse) {
                $filename = 'inventory_import_' . str_replace(' ', '_', $warehouse->code) . '_' . date('Y-m-d') . '.csv';
            }
        }
        
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Output BOM for UTF-8 Excel compatibility
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        
        // Headers with warehouse support
        fputcsv($output, [
            'SKU',
            'Stock Quantity to Add', 
            'Warehouse Code',
            'Location Code (Optional)'
        ]);
        
        if ($type === 'default') {
            // Sample data rows for default
            $warehouseCode = $warehouses->first()->code ?? 'WH-001';
            $sampleData = [
                ['VARIANT-SKU-001', 100, $warehouseCode, 'LOC-A1'],
                ['VARIANT-SKU-002', 50, $warehouseCode, ''], // Empty location to show it's optional
                ['VARIANT-SKU-003', 0, $warehouseCode, 'LOC-B2'],
            ];
            
            foreach ($sampleData as $row) {
                fputcsv($output, $row);
            }
        } else {
            // Real data from database
            foreach ($variants as $variant) {
                // Get warehouse stock info if available
                $warehouseStock = null;
                
                if ($warehouseId) {
                    $warehouseStock = $variant->inventoryStocks
                        ->where('warehouse_id', $warehouseId)
                        ->first();
                } else {
                    // Get first warehouse stock if any
                    $warehouseStock = $variant->inventoryStocks->first();
                }
                
                $warehouseCode = $warehouseStock ? $warehouseStock->warehouse->code : '';
                $locationCode = $warehouseStock && $warehouseStock->warehouseLocation 
                    ? $warehouseStock->warehouseLocation->code 
                    : '';
                
                fputcsv($output, [
                    $variant->sku,
                    '', // Empty quantity - user will fill
                    $warehouseCode, // Warehouse code
                    $locationCode // Location code (optional)
                ]);
            }
        }
        
        fclose($output);
        exit;
    }

    /**
     * Import inventory from file.
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB max
                function ($attribute, $value, $fail) {
                    if (!$value) {
                        return;
                    }
                    
                    $extension = strtolower($value->getClientOriginalExtension());
                    $mimeType = $value->getMimeType();
                    
                    $allowedExtensions = ['csv', 'xls', 'xlsx'];
                    $allowedMimeTypes = [
                        'text/csv',
                        'text/plain',
                        'application/csv',
                        'application/vnd.ms-excel',
                        'application/excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel.sheet.macroEnabled.12'
                    ];
                    
                    if (!in_array($extension, $allowedExtensions)) {
                        $fail('The file must be a CSV, XLS, or XLSX file.');
                    }
                    
                    // Also check MIME type for CSV files (can vary)
                    if ($extension === 'csv' && !in_array($mimeType, $allowedMimeTypes) && !in_array($mimeType, ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])) {
                        // Allow CSV even if MIME type is not recognized
                        return;
                    }
                }
            ],
            'update_existing_only' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file');
            $updateExistingOnly = $request->boolean('update_existing_only', false);
            
            $extension = strtolower($file->getClientOriginalExtension());
            
            // Read file based on extension
            $rows = [];
            if ($extension === 'csv') {
                // Read CSV file (handle both comma and tab-separated)
                $handle = fopen($file->getRealPath(), 'r');
                
                // Read first line to detect delimiter
                $firstLine = fgets($handle);
                rewind($handle);
                
                // Detect delimiter (comma or tab)
                $delimiter = (strpos($firstLine, "\t") !== false) ? "\t" : ",";
                
                while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                    // Skip empty rows
                    if (count(array_filter($data)) > 0) {
                        $rows[] = $data;
                    }
                }
                fclose($handle);
            } else {
                // Use Spout for Excel files
                $reader = ReaderEntityFactory::createReaderFromFile($file->getRealPath());
                $reader->open($file->getRealPath());
                
                foreach ($reader->getSheetIterator() as $sheet) {
                    foreach ($sheet->getRowIterator() as $row) {
                        $rowData = [];
                        foreach ($row->getCells() as $cell) {
                            $rowData[] = $cell->getValue();
                        }
                        $rows[] = $rowData;
                    }
                    break; // Only read first sheet
                }
                $reader->close();
            }
            
            if (count($rows) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'File is empty or has no data rows'
                ], 422);
            }
            
            // Get headers (first row)
            $headers = array_map('strtolower', array_map('trim', $rows[0]));
            
            // Find column indices
            $skuIndex = array_search('sku', $headers);
            // Support both "stock quantity" and "stock quantity to add"
            $stockQtyIndex = array_search('stock quantity', $headers);
            if ($stockQtyIndex === false) {
                $stockQtyIndex = array_search('stock quantity to add', $headers);
            }
            $warehouseCodeIndex = array_search('warehouse code', $headers);
            
            // Try multiple variations for location code column (case-insensitive)
            $locationCodeIndex = false;
            $locationVariations = [
                'location code (optional)',
                'location code',
                'location',
                'warehouse location',
                'location code(optional)'
            ];
            foreach ($locationVariations as $variation) {
                $index = array_search($variation, $headers);
                if ($index !== false) {
                    $locationCodeIndex = $index;
                    break;
                }
            }
            
            if ($skuIndex === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'SKU column is required in the import file'
                ], 422);
            }
            
            // Get default warehouse from request if provided
            $defaultWarehouseId = $request->input('warehouse_id');
            
            $processed = 0;
            $updated = 0;
            $skipped = 0;
            $errors = [];
            
            // Process data rows (skip header)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                $sku = trim($row[$skuIndex] ?? '');
                
                if (empty($sku)) {
                    $errors[] = "Row " . ($i + 1) . ": SKU is required";
                    $skipped++;
                    continue;
                }
                
                // Find variant by SKU
                $variant = ProductVariant::where('sku', $sku)
                    ->whereHas('product', function($q) {
                        $q->whereNull('deleted_at');
                    })
                    ->first();
                
                if (!$variant) {
                    if ($updateExistingOnly) {
                        $skipped++;
                        continue;
                    } else {
                        $errors[] = "Row " . ($i + 1) . ": Variant with SKU '{$sku}' not found";
                        $skipped++;
                        continue;
                    }
                }
                
                try {
                    DB::beginTransaction();
                    
                    // Determine warehouse
                    $warehouseId = null;
                    $locationId = null;
                    
                    if ($warehouseCodeIndex !== false && isset($row[$warehouseCodeIndex]) && trim($row[$warehouseCodeIndex])) {
                        // Warehouse code provided in file
                        $warehouseCode = trim($row[$warehouseCodeIndex]);
                        $warehouse = Warehouse::where('code', $warehouseCode)
                            ->where('status', 'active')
                            ->first();
                        
                        if (!$warehouse) {
                            $errors[] = "Row " . ($i + 1) . ": Warehouse with code '{$warehouseCode}' not found";
                            $skipped++;
                            DB::rollBack();
                            continue;
                        }
                        
                        $warehouseId = $warehouse->id;
                    } elseif ($defaultWarehouseId) {
                        // Use default warehouse from form
                        $warehouseId = $defaultWarehouseId;
                    }
                    
                    // Find location if provided (works for both warehouse from file and default warehouse)
                    if ($warehouseId && $locationCodeIndex !== false && isset($row[$locationCodeIndex]) && trim($row[$locationCodeIndex])) {
                        $locationCode = trim($row[$locationCodeIndex]);
                        
                        // Skip if location code is "N/A" or empty
                        if (strtoupper($locationCode) === 'N/A' || empty($locationCode)) {
                            $locationId = null;
                        } else {
                            // Get all active locations for this warehouse and match by computed location_code
                            // Since location_code is an accessor (computed from rack-shelf-bin), we need to
                            // load locations and compare the computed value
                            // Note: status is stored as enum 'active'/'inactive' in DB, not boolean
                            $locations = WarehouseLocation::where('warehouse_id', $warehouseId)
                                ->where('status', 'active')
                                ->get();
                            
                            $location = null;
                            $inputCode = strtolower(trim($locationCode));
                            
                            foreach ($locations as $loc) {
                                // Compare the computed location_code with the input (case-insensitive)
                                $locCode = strtolower(trim($loc->location_code));
                                
                                if ($locCode === $inputCode) {
                                    $location = $loc;
                                    break;
                                }
                                
                                // Also try matching against full_location (e.g., "Rack-A-Shelf-1-Bin-A")
                                // Extract just the values from full_location (remove "Rack:", "Shelf:", "Bin:" labels)
                                $fullLoc = strtolower(trim($loc->full_location));
                                // Remove labels and normalize
                                $fullLocNormalized = preg_replace('/\b(rack|shelf|bin):\s*/i', '', $fullLoc);
                                $fullLocNormalized = str_replace(', ', '-', $fullLocNormalized);
                                $fullLocNormalized = str_replace(' ', '-', $fullLocNormalized);
                                
                                if ($fullLocNormalized === $inputCode || $fullLoc === $inputCode) {
                                    $location = $loc;
                                    break;
                                }
                                
                                // Also try direct match on rack-shelf-bin combination
                                // If input is "rac-3-s-3-b1", try to match rack="rac-3", shelf="s-3", bin="b1"
                                $parts = explode('-', $inputCode);
                                if (count($parts) >= 3) {
                                    // Try matching last 3 parts as bin, shelf, rack (reverse order)
                                    $tryBin = $parts[count($parts) - 1];
                                    $tryShelf = count($parts) >= 2 ? $parts[count($parts) - 2] : null;
                                    $tryRack = count($parts) >= 3 ? implode('-', array_slice($parts, 0, count($parts) - 2)) : null;
                                    
                                    if ($tryRack && strtolower(trim($loc->rack ?? '')) === $tryRack &&
                                        $tryShelf && strtolower(trim($loc->shelf ?? '')) === $tryShelf &&
                                        $tryBin && strtolower(trim($loc->bin ?? '')) === $tryBin) {
                                        $location = $loc;
                                        break;
                                    }
                                }
                            }
                            
                            if ($location) {
                                $locationId = $location->id;
                            } else {
                                // Don't error out - location is optional, just log a warning
                                $errors[] = "Row " . ($i + 1) . ": Location with code '{$locationCode}' not found in warehouse. Available locations: " . 
                                    $locations->pluck('location_code')->implode(', ') . ". Stock will be added without specific location.";
                                $locationId = null; // Ensure it's null if not found
                            }
                        }
                    } else {
                        // No location code provided, ensure locationId is null
                        $locationId = null;
                    }
                    
                    // Update variant-level settings
                    $updateData = [];
                    
                    // Update stock quantity (requires warehouse)
                    if ($stockQtyIndex !== false && isset($row[$stockQtyIndex])) {
                        $stockQty = trim($row[$stockQtyIndex]);
                        if ($stockQty !== '' && is_numeric($stockQty)) {
                            $quantityToAdd = (int) $stockQty;
                            
                            if (!$warehouseId) {
                                $errors[] = "Row " . ($i + 1) . ": Warehouse is required to update stock quantity. Please provide Warehouse Code in the file or select a default warehouse.";
                                $skipped++;
                                DB::rollBack();
                                continue;
                            }
                            
                            // Update warehouse-specific stock
                            // Note: firstOrNew uses all attributes to find existing record
                            // So records with different locations are treated as separate records
                            $inventoryStock = InventoryStock::firstOrNew([
                                'product_variant_id' => $variant->id,
                                'warehouse_id' => $warehouseId,
                                'warehouse_location_id' => $locationId, // null or actual location ID
                            ]);
                            
                            // Store previous quantity for history
                            $previousQuantity = $inventoryStock->quantity ?? 0;
                            
                            // Always set location_id (even if null) to ensure it's saved correctly
                            $inventoryStock->warehouse_location_id = $locationId;
                            $inventoryStock->quantity = $previousQuantity + $quantityToAdd;
                            $inventoryStock->save();
                            
                            // Log history if quantity changed (always log for imports to track location)
                            if ($previousQuantity != $inventoryStock->quantity) {
                                InventoryHistory::create([
                                    'product_variant_id' => $variant->id,
                                    'warehouse_id' => $warehouseId,
                                    'warehouse_location_id' => $locationId, // This should be set if location was found
                                    'previous_quantity' => $previousQuantity,
                                    'new_quantity' => $inventoryStock->quantity,
                                    'quantity_change' => $inventoryStock->quantity - $previousQuantity,
                                    'change_type' => 'increment',
                                    'reference_type' => 'import',
                                    'notes' => 'Stock imported from file - Row ' . ($i + 1) . ($locationId ? ' (Location: ' . $locationCode . ')' : ''),
                                    'user_id' => Auth::id(),
                                ]);
                            }
                            
                            // Sync total to variant stock_quantity
                            $variant->refresh();
                            $totalStock = $variant->inventoryStocks()->sum('quantity');
                            $variant->stock_quantity = $totalStock;
                            $variant->stock_status = $totalStock > 0 ? 'in_stock' : 'out_of_stock';
                        }
                    }
                    
                    // Update variant if there are changes
                    if (!empty($updateData)) {
                        $variant->update($updateData);
                    }
                    
                    // Refresh variant to get updated data
                    $variant->refresh();
                    
                    DB::commit();
                    $updated++;
                    $processed++;
                } catch (\Exception $e) {
                    DB::rollBack();
                    $errors[] = "Row " . ($i + 1) . ": Error processing - " . $e->getMessage();
                    $skipped++;
                    $processed++;
                }
            }
            
            $message = "Import completed. Processed: {$processed}, Updated: {$updated}, Skipped: {$skipped}";
            if (count($errors) > 0) {
                $message .= ", Errors: " . count($errors);
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'processed' => $processed,
                'updated' => $updated,
                'skipped' => $skipped,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error importing inventory: ' . $e->getMessage()
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
     * Get locations for a warehouse
     */
    public function getWarehouseLocations(Request $request, $warehouseId)
    {
        $locations = WarehouseLocation::where('warehouse_id', $warehouseId)
            ->where('status', 'active')
            ->orderBy('rack')
            ->orderBy('shelf')
            ->orderBy('bin')
            ->get(['id', 'rack', 'shelf', 'bin']);

        $data = $locations->map(function($location) {
            return [
                'id' => $location->id,
                'location_code' => $location->location_code,
                'full_location' => $location->full_location,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get stock breakdown for a variant
     */
    public function getStockBreakdown($variantId)
    {
        $variant = ProductVariant::with(['inventoryStocks.warehouse', 'inventoryStocks.warehouseLocation'])
            ->findOrFail($variantId);

        $breakdown = $variant->inventoryStocks->map(function($stock) {
            return [
                'id' => $stock->id,
                'warehouse_id' => $stock->warehouse_id,
                'warehouse_name' => $stock->warehouse->name ?? 'N/A',
                'warehouse_code' => $stock->warehouse->code ?? 'N/A',
                'location_id' => $stock->warehouse_location_id,
                'location_code' => $stock->warehouseLocation->location_code ?? 'N/A',
                'quantity' => $stock->quantity,
                'reserved_quantity' => $stock->reserved_quantity,
                'available_quantity' => $stock->available_quantity,
            ];
        });

        // Calculate total stock from the actual breakdown data
        $totalStock = $breakdown->sum('quantity');
        $totalReserved = $breakdown->sum('reserved_quantity');
        $totalAvailable = $totalStock - $totalReserved;

        return response()->json([
            'success' => true,
            'data' => $breakdown,
            'total_stock' => $totalStock,
            'available_stock' => max(0, $totalAvailable),
        ]);
    }

    /**
     * Get inventory history for a variant
     */
    public function getHistory($variantId)
    {
        $variant = ProductVariant::findOrFail($variantId);
        
        $history = InventoryHistory::with(['warehouse', 'warehouseLocation', 'user'])
            ->forVariant($variantId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($record) {
                return [
                    'id' => $record->id,
                    'warehouse_name' => $record->warehouse ? $record->warehouse->name : 'N/A',
                    'location_name' => $record->warehouseLocation ? $record->warehouseLocation->full_location : 'N/A',
                    'previous_quantity' => $record->previous_quantity,
                    'new_quantity' => $record->new_quantity,
                    'quantity_change' => $record->quantity_change,
                    'change_type' => ucfirst($record->change_type),
                    'reference_type' => $record->reference_type,
                    'notes' => $record->notes,
                    'user_name' => $record->user ? $record->user->name : 'System',
                    'created_at' => $record->created_at->format('Y-m-d H:i:s'),
                    'is_cleared' => $record->reference_type === 'history_cleared',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'variant' => [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'sku' => $variant->sku,
                ],
                'history' => $history,
            ]
        ]);
    }

    /**
     * Clear inventory history for a variant
     */
    public function clearHistory(Request $request, $variantId)
    {
        $variant = ProductVariant::findOrFail($variantId);
        
        // Get count of records to be deleted
        $historyCount = InventoryHistory::forVariant($variantId)->count();
        
        if ($historyCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No history records to clear'
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            // Delete all history records for this variant
            InventoryHistory::forVariant($variantId)->delete();
            
            // Create a clearance record as the last activity
            InventoryHistory::create([
                'product_variant_id' => $variant->id,
                'warehouse_id' => null,
                'warehouse_location_id' => null,
                'previous_quantity' => 0,
                'new_quantity' => 0,
                'quantity_change' => 0,
                'change_type' => 'adjustment',
                'reference_type' => 'history_cleared',
                'reference_id' => null,
                'notes' => "History cleared - {$historyCount} record(s) deleted",
                'user_id' => Auth::id(),
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Inventory history cleared successfully. {$historyCount} record(s) deleted.",
                'deleted_count' => $historyCount
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error clearing history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export inventory data to CSV
     */
    public function export(Request $request)
    {
        $warehouseId = $request->get('warehouse_id');
        $stockStatus = $request->get('stock_status');
        $lowStock = $request->get('low_stock') === '1';
        
        $query = ProductVariant::with([
            'product',
            'inventoryStocks.warehouse',
            'inventoryStocks.warehouseLocation'
        ])->whereHas('product', function($q) {
            $q->whereNull('deleted_at');
        });
        
        // Filter by warehouse if provided
        if ($warehouseId) {
            $query->whereHas('inventoryStocks', function($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }
        
        // Filter by stock status
        if ($stockStatus) {
            $query->where('stock_status', $stockStatus);
        }
        
        // Filter low stock
        if ($lowStock) {
            $query->where('manage_stock', true)
                  ->where(function($q) {
                      $q->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                        ->orWhere('stock_quantity', '<=', 0);
                  });
        }
        
        $variants = $query->orderBy('sku')->get();
        
        $warehouse = null;
        if ($warehouseId) {
            $warehouse = Warehouse::find($warehouseId);
        }
        
        $filename = 'inventory_export_' . date('Y-m-d') . '.csv';
        if ($warehouse) {
            $filename = 'inventory_export_' . str_replace(' ', '_', $warehouse->code) . '_' . date('Y-m-d') . '.csv';
        }
        
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Output BOM for UTF-8 Excel compatibility
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        
        // Headers
        fputcsv($output, [
            'SKU',
            'Variant Name',
            'Product Name',
            'Stock Quantity',
            'Stock Status',
            'Low Stock Threshold',
            'Warehouse Code',
            'Location Code'
        ]);
        
        foreach ($variants as $variant) {
            $totalStock = $variant->total_stock_quantity ?? ($variant->stock_quantity ?? 0);
            
            if ($warehouseId && $variant->inventoryStocks->where('warehouse_id', $warehouseId)->count() > 0) {
                // Export warehouse-specific data
                foreach ($variant->inventoryStocks->where('warehouse_id', $warehouseId) as $stock) {
                    fputcsv($output, [
                        $variant->sku,
                        $variant->name,
                        $variant->product->name ?? 'N/A',
                        $totalStock,
                        ucfirst(str_replace('_', ' ', $variant->stock_status)),
                        $variant->low_stock_threshold ?? 0,
                        $stock->warehouse->code ?? '',
                        $stock->warehouseLocation->location_code ?? ''
                    ]);
                }
            } else {
                // Export general data (no warehouse filter or no warehouse stock)
                $warehouseStock = $variant->inventoryStocks->first();
                fputcsv($output, [
                    $variant->sku,
                    $variant->name,
                    $variant->product->name ?? 'N/A',
                    $totalStock,
                    ucfirst(str_replace('_', ' ', $variant->stock_status)),
                    $variant->low_stock_threshold ?? 0,
                    $warehouseStock ? $warehouseStock->warehouse->code ?? '' : '',
                    $warehouseStock && $warehouseStock->warehouseLocation ? $warehouseStock->warehouseLocation->location_code ?? '' : ''
                ]);
            }
        }
        
        fclose($output);
        exit;
    }
}

