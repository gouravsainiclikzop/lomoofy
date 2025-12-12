<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WarehouseLocationController extends Controller
{
    /**
     * Get locations for a specific warehouse
     */
    public function getData(Request $request, $warehouseId = null)
    {
        $query = WarehouseLocation::with('warehouse');

        // Filter by warehouse if provided
        $warehouseIdParam = $warehouseId ?? $request->input('warehouse_id');
        if ($warehouseIdParam) {
            $query->where('warehouse_id', $warehouseIdParam);
        }

        // Search functionality
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('rack', 'like', '%' . $search . '%')
                  ->orWhere('shelf', 'like', '%' . $search . '%')
                  ->orWhere('bin', 'like', '%' . $search . '%')
                  ->orWhereHas('warehouse', function($wq) use ($search) {
                      $wq->where('name', 'like', '%' . $search . '%')
                         ->orWhere('code', 'like', '%' . $search . '%');
                  });
            });
        }

        // Filter by status (only if status is provided and not empty)
        // Note: status is stored as ENUM in DB ('active'/'inactive') but cast as boolean in model
        // So we need to handle both 'active'/'inactive' strings and boolean values
        $statusParam = $request->input('status');
        if (!empty($statusParam) && $statusParam !== '') {
            $statusValue = $statusParam;
            // Convert to what's stored in database (ENUM: 'active' or 'inactive')
            if ($statusValue === true || $statusValue === 1 || $statusValue === '1' || $statusValue === 'active') {
                $query->where('status', 'active');
            } elseif ($statusValue === false || $statusValue === 0 || $statusValue === '0' || $statusValue === 'inactive') {
                $query->where('status', 'inactive');
            }
        }

        // Check if this is a DataTables request
        $isDataTableRequest = $request->has('draw');
        
        if ($isDataTableRequest) {
            // DataTables request - apply pagination
            $totalRecords = $query->count();
            $start = intval($request->start ?? 0);
            $length = intval($request->length ?? 10);
            
            $locations = $query->orderBy('warehouse_id')
                           ->orderBy('rack')
                           ->orderBy('shelf')
                           ->orderBy('bin')
                           ->skip($start)
                           ->take($length)
                           ->get();

            $data = $locations->map(function ($location) {
                // Convert boolean status to string for frontend
                $status = $location->status;
                if (is_bool($status)) {
                    $status = $status ? 'active' : 'inactive';
                }
                
                return [
                    'id' => $location->id,
                    'warehouse_id' => $location->warehouse_id,
                    'warehouse_name' => $location->warehouse->name ?? 'N/A',
                    'warehouse_code' => $location->warehouse->code ?? 'N/A',
                    'rack' => $location->rack,
                    'shelf' => $location->shelf,
                    'bin' => $location->bin,
                    'location_code' => $location->location_code,
                    'status' => $status,
                    'created_at' => $location->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => WarehouseLocation::count(),
                'recordsFiltered' => $totalRecords,
                'data' => $data
            ]);
        } else {
            // Simple request - return all locations
            $locations = $query->orderBy('warehouse_id')
                           ->orderBy('rack')
                           ->orderBy('shelf')
                           ->orderBy('bin')
                           ->get();

            $data = $locations->map(function ($location) {
                // Convert boolean status to string for frontend
                $status = $location->status;
                if (is_bool($status)) {
                    $status = $status ? 'active' : 'inactive';
                }
                
                return [
                    'id' => $location->id,
                    'warehouse_id' => $location->warehouse_id,
                    'warehouse_name' => $location->warehouse->name ?? 'N/A',
                    'rack' => $location->rack,
                    'shelf' => $location->shelf,
                    'bin' => $location->bin,
                    'location_code' => $location->location_code,
                    'status' => $status,
                ];
            });

            return response()->json($data);
        }
    }

    /**
     * Get a single location for editing
     */
    public function edit($id)
    {
        $location = WarehouseLocation::with('warehouse')->findOrFail($id);
        
        // Convert boolean status to string for frontend
        $status = $location->status;
        if (is_bool($status)) {
            $status = $status ? 'active' : 'inactive';
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $location->id,
                'warehouse_id' => $location->warehouse_id,
                'warehouse_name' => $location->warehouse->name ?? 'N/A',
                'rack' => $location->rack,
                'shelf' => $location->shelf,
                'bin' => $location->bin,
                'status' => $status,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'required|exists:warehouses,id',
            'rack' => 'nullable|string|max:50',
            'shelf' => 'nullable|string|max:50',
            'bin' => 'nullable|string|max:50',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        // Convert status string to boolean (model expects boolean)
        $status = $request->status ?? 'active';
        $data['status'] = ($status === 'active' || $status === true || $status === 1);

        $location = WarehouseLocation::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Warehouse location created successfully',
            'data' => $location->load('warehouse')
        ]);
    }

    public function update(Request $request, $id)
    {
        $location = WarehouseLocation::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'required|exists:warehouses,id',
            'rack' => 'nullable|string|max:50',
            'shelf' => 'nullable|string|max:50',
            'bin' => 'nullable|string|max:50',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        // Convert status string to boolean (model expects boolean)
        if ($request->has('status')) {
            $status = $request->status;
            $data['status'] = ($status === 'active' || $status === true || $status === 1);
        } else {
            // Keep existing status if not provided
            $data['status'] = $location->status;
        }

        $location->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Warehouse location updated successfully',
            'data' => $location->load('warehouse')
        ]);
    }

    public function destroy($id)
    {
        $location = WarehouseLocation::findOrFail($id);
        $location->delete();

        return response()->json([
            'success' => true,
            'message' => 'Warehouse location deleted successfully'
        ]);
    }

    /**
     * Bulk delete locations (POST AJAX JSON).
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:warehouse_locations,id'
        ]);

        $ids = $request->ids;
        $deleted = 0;
        $failed = 0;
        $errors = [];

        foreach ($ids as $id) {
            $location = WarehouseLocation::find($id);
            if (!$location) {
                $failed++;
                continue;
            }

            $location->delete();
            $deleted++;
        }

        $message = "Deleted {$deleted} location(s)";
        if ($failed > 0) {
            $message .= ". {$failed} location(s) could not be deleted.";
        }

        return response()->json([
            'success' => $deleted > 0,
            'message' => $message,
            'deleted' => $deleted,
            'failed' => $failed,
            'errors' => $errors
        ]);
    }
}
