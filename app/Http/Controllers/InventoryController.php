<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Models\InventoryStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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
            
            // Calculate stock status based on available stock if manage_stock is enabled
            $calculatedStockStatus = $variant->stock_status;
            if ($variant->manage_stock) {
                $calculatedStockStatus = $availableStock > 0 ? 'in_stock' : 'out_of_stock';
            }
            
            // Get warehouse breakdown
            $warehouseBreakdown = $variant->inventoryStocks->map(function($stock) {
                return [
                    'warehouse_id' => $stock->warehouse_id,
                    'warehouse_name' => $stock->warehouse->name ?? 'N/A',
                    'warehouse_code' => $stock->warehouse->code ?? 'N/A',
                    'location_id' => $stock->warehouse_location_id,
                    'location_code' => $stock->warehouseLocation->location_code ?? 'N/A',
                    'quantity' => $stock->quantity,
                    'reserved_quantity' => $stock->reserved_quantity,
                    'available_quantity' => $stock->available_quantity,
                ];
            })->toArray();
            
            // Get variant image or product image
            $variantImage = $variant->images->first();
            $imageUrl = $variantImage 
                ? asset('storage/' . $variantImage->image_path)
                : ($product->image_url ?? asset('assets/images/placeholder.jpg'));
            
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
                'low_stock_threshold' => $variant->low_stock_threshold ?? 0,
                'warehouse_breakdown' => $warehouseBreakdown,
                'warehouse_count' => count($warehouseBreakdown),
                'stock_location' => '-', // Variants don't have stock_location
                'allow_backorder' => '-', // Variants don't have allow_backorder
                'is_low_stock' => $variant->manage_stock && $availableStock <= ($variant->low_stock_threshold ?? 0),
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
                'stock_status' => 'nullable|in:in_stock,out_of_stock,on_backorder',
                'low_stock_threshold' => 'nullable|integer|min:0',
                'warehouse_id' => 'nullable|exists:warehouses,id',
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
                
                // Handle warehouse-based stock update
                if ($request->has('warehouse_id') && $request->has('stock_quantity')) {
                    $warehouseId = $request->warehouse_id;
                    $locationId = $request->warehouse_location_id;
                    $stockQuantity = $request->stock_quantity;
                    $updateType = $request->update_type ?? 'set';
                    
                    // Find or create inventory stock record
                    $inventoryStock = InventoryStock::firstOrNew([
                        'product_variant_id' => $variant->id,
                        'warehouse_id' => $warehouseId,
                        'warehouse_location_id' => $locationId,
                    ]);
                    
                    // Update quantity based on update type
                    if ($updateType === 'increment') {
                        $inventoryStock->quantity = ($inventoryStock->quantity ?? 0) + $stockQuantity;
                    } elseif ($updateType === 'decrement') {
                        $inventoryStock->quantity = max(0, ($inventoryStock->quantity ?? 0) - $stockQuantity);
                    } else {
                        // 'set' - set the exact value
                        $inventoryStock->quantity = $stockQuantity;
                    }
                    
                    $inventoryStock->save();
                    
                    // Sync total to variant stock_quantity for backward compatibility
                    $totalStock = $variant->inventoryStocks()->sum('quantity');
                    $variant->stock_quantity = $totalStock;
                    $variant->stock_status = $totalStock > 0 ? 'in_stock' : 'out_of_stock';
                    $variant->save();
                } else {
                    // Legacy update - update variant stock_quantity directly
                    $stockQuantity = $request->has('stock_quantity') ? $request->stock_quantity : $variant->stock_quantity;
                    $stockStatus = $request->stock_status ?? $variant->stock_status;
                    
                    if ($manageStock && $request->has('stock_quantity')) {
                        $stockStatus = $stockQuantity > 0 ? 'in_stock' : 'out_of_stock';
                    }
                    
                    $variant->update([
                        'stock_quantity' => $stockQuantity,
                        'stock_status' => $stockStatus,
                    ]);
                }

                DB::commit();
                
                // Refresh variant to get updated relationships
                $variant->refresh();
                $variant->load(['inventoryStocks.warehouse', 'inventoryStocks.warehouseLocation']);

                // Calculate display stock status
                $totalStock = $variant->total_stock_quantity ?? ($variant->stock_quantity ?? 0);
                $availableStock = $variant->available_stock ?? $totalStock;
                $displayStockStatus = $variant->stock_status;
                if ($variant->manage_stock) {
                    $displayStockStatus = $availableStock > 0 ? 'in_stock' : 'out_of_stock';
                }
                
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
                        'low_stock_threshold' => $variant->low_stock_threshold ?? 0,
                        'is_low_stock' => $variant->manage_stock && $availableStock <= ($variant->low_stock_threshold ?? 0),
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
            'stock_status' => 'nullable|in:in_stock,out_of_stock,on_backorder',
        ]);

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
            $productIds = $request->product_ids ?? [];
            $variantIds = $request->variant_ids ?? [];
            $stockQuantity = $request->stock_quantity;
            $stockStatus = $request->stock_status ?? 'in_stock';
            
            $updated = 0;
            $skipped = 0;
            $errors = [];

            // Process simple products
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

                // Add stock quantity (increment existing stock)
                $currentStock = $product->stock_quantity ?? 0;
                $newStock = $currentStock + $stockQuantity;

                // Update stock status based on new quantity
                $finalStockStatus = $stockStatus;
                if ($newStock > 0 && $stockStatus === 'out_of_stock') {
                    $finalStockStatus = 'in_stock';
                } elseif ($newStock <= 0 && $stockStatus === 'in_stock') {
                    $finalStockStatus = 'out_of_stock';
                }

                $product->update([
                    'manage_stock' => true,
                    'stock_quantity' => $newStock,
                    'stock_status' => $finalStockStatus,
                ]);

                $updated++;
            }

            // Process variants
            foreach ($variantIds as $variantId) {
                $variant = ProductVariant::find($variantId);
                
                if (!$variant) {
                    $skipped++;
                    continue;
                }

                // Enable stock management if not already enabled
                if (!$variant->manage_stock) {
                    $variant->manage_stock = true;
                }

                // Add stock quantity (increment existing stock)
                $currentStock = $variant->stock_quantity ?? 0;
                $newStock = $currentStock + $stockQuantity;

                // Update stock status based on new quantity
                $finalStockStatus = $stockStatus;
                if ($newStock > 0 && $stockStatus === 'out_of_stock') {
                    $finalStockStatus = 'in_stock';
                } elseif ($newStock <= 0 && $stockStatus === 'in_stock') {
                    $finalStockStatus = 'out_of_stock';
                }

                $variant->update([
                    'manage_stock' => true,
                    'stock_quantity' => $newStock,
                    'stock_status' => $finalStockStatus,
                ]);

                $updated++;
            }

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
            return response()->json([
                'success' => false,
                'message' => 'Error adding stock: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download sample import file.
     */
    public function downloadSample(Request $request)
    {
        $type = $request->get('type', 'default'); // default, all, low_stock
        
        $query = ProductVariant::with('product')
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
        
        // Limit results for 'all' to prevent huge files
        if ($type === 'all') {
            $variants = $query->orderBy('sku')->limit(1000)->get();
        } elseif ($type === 'low_stock') {
            $variants = $query->orderBy('sku')->get();
        } else {
            // Default: return empty sample with just headers
            $variants = collect();
        }
        
        $filename = 'inventory_import_sample_' . $type . '_' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Output BOM for UTF-8 Excel compatibility
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        
        // Headers
        fputcsv($output, [
            'SKU',
            'Stock Quantity to Add', 
            'Manage Stock',
            'Low Stock Threshold'
        ]);
        
        if ($type === 'default') {
            // Sample data rows for default
            $sampleData = [
                ['VARIANT-SKU-001', 100,  'Yes', 10],
                ['VARIANT-SKU-002', 50,  'Yes', 5],
                ['VARIANT-SKU-003', 0,  'Yes', 10],
            ];
            
            foreach ($sampleData as $row) {
                fputcsv($output, $row);
            }
        } else {
            // Real data from database
            foreach ($variants as $variant) {
                fputcsv($output, [
                    $variant->sku,
                    '', // Empty quantity - user will fill
                    $variant->manage_stock ? 'Yes' : 'No',
                    $variant->low_stock_threshold ?? 0
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
            $manageStockIndex = array_search('manage stock', $headers);
            $lowStockThresholdIndex = array_search('low stock threshold', $headers);
            
            if ($skuIndex === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'SKU column is required in the import file'
                ], 422);
            }
            
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
                
                // Update variant inventory
                $updateData = [];
                
                if ($stockQtyIndex !== false && isset($row[$stockQtyIndex])) {
                    $stockQty = trim($row[$stockQtyIndex]);
                    if ($stockQty !== '' && is_numeric($stockQty)) {
                        // ADD to existing quantity instead of replacing
                        $quantityToAdd = (int) $stockQty;
                        $currentQuantity = (int) ($variant->stock_quantity ?? 0);
                        $newQuantity = $currentQuantity + $quantityToAdd;
                        $updateData['stock_quantity'] = $newQuantity;
                        
                        // Auto-update stock_status based on new quantity if manage_stock is enabled
                        if ($variant->manage_stock) {
                            $updateData['stock_status'] = $newQuantity > 0 ? 'in_stock' : 'out_of_stock';
                        }
                    }
                }
                
                if ($manageStockIndex !== false && isset($row[$manageStockIndex])) {
                    $manageStock = strtolower(trim($row[$manageStockIndex]));
                    $updateData['manage_stock'] = in_array($manageStock, ['yes', '1', 'true', 'y']);
                }
                
                if ($lowStockThresholdIndex !== false && isset($row[$lowStockThresholdIndex])) {
                    $threshold = trim($row[$lowStockThresholdIndex]);
                    if ($threshold !== '' && is_numeric($threshold)) {
                        $updateData['low_stock_threshold'] = (int) $threshold;
                    }
                }
                
                if (!empty($updateData)) {
                    $variant->update($updateData);
                    // Refresh variant to get updated data
                    $variant->refresh();
                    $updated++;
                } else {
                    // No data to update, but still count as processed
                    $skipped++;
                }
                
                $processed++;
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

        return response()->json([
            'success' => true,
            'data' => $breakdown,
            'total_stock' => $variant->total_stock_quantity ?? 0,
            'available_stock' => $variant->available_stock ?? 0,
        ]);
    }
}

