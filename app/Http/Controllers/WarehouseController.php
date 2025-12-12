<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.warehouses.index');
    }

    /**
     * Get warehouses data for DataTables or simple list
     */
    public function getData(Request $request)
    {
        $query = Warehouse::withCount('locations');

        // Search functionality
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%')
                  ->orWhere('city', 'like', '%' . $search . '%')
                  ->orWhere('state', 'like', '%' . $search . '%')
                  ->orWhere('country', 'like', '%' . $search . '%');
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Check if this is a DataTables request
        $isDataTableRequest = $request->has('draw');
        
        if ($isDataTableRequest) {
            // DataTables request - apply pagination
            $totalRecords = $query->count();
            $start = intval($request->start ?? 0);
            $length = intval($request->length ?? 10);
            
            $warehouses = $query->orderBy('name')
                           ->skip($start)
                           ->take($length)
                           ->get();

            $data = $warehouses->map(function ($warehouse) {
                return [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                    'code' => $warehouse->code,
                    'address' => $warehouse->address,
                    'city' => $warehouse->city,
                    'state' => $warehouse->state,
                    'country' => $warehouse->country,
                    'status' => $warehouse->status,
                    'is_default' => $warehouse->is_default,
                    'locations_count' => $warehouse->locations_count,
                    'created_at' => $warehouse->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => Warehouse::count(),
                'recordsFiltered' => $totalRecords,
                'data' => $data
            ]);
        } else {
            // Simple request - return all warehouses (for dropdowns, etc.)
            $warehouses = $query->orderBy('name')->get();

            $data = $warehouses->map(function ($warehouse) {
                return [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                    'code' => $warehouse->code,
                    'status' => $warehouse->status,
                ];
            });

            return response()->json($data);
        }
    }

    /**
     * Get a single warehouse for editing
     */
    public function edit($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'code' => $warehouse->code,
                'address' => $warehouse->address,
                'city' => $warehouse->city,
                'state' => $warehouse->state,
                'country' => $warehouse->country,
                'status' => $warehouse->status,
                'is_default' => $warehouse->is_default,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'status' => 'nullable|in:active,inactive',
            'is_default' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['status'] = $request->status ?? 'active';
        $data['is_default'] = $request->has('is_default') ? (bool)$request->is_default : false;

        // If setting this as default, unset all other defaults
        if ($data['is_default']) {
            Warehouse::where('is_default', true)->update(['is_default' => false]);
        }

        $warehouse = Warehouse::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Warehouse created successfully',
            'data' => $warehouse
        ]);
    }

    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code,' . $warehouse->id,
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'status' => 'nullable|in:active,inactive',
            'is_default' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['status'] = $request->status ?? $warehouse->status;
        
        // Handle is_default: if setting this as default, unset all other defaults
        if ($request->has('is_default')) {
            $isDefault = (bool)$request->is_default;
            $data['is_default'] = $isDefault;
            
            // If setting this warehouse as default, unset all other defaults
            if ($isDefault) {
                Warehouse::where('is_default', true)
                    ->where('id', '!=', $warehouse->id)
                    ->update(['is_default' => false]);
            }
        } else {
            // If not provided, keep existing value
            $data['is_default'] = $warehouse->is_default;
        }

        $warehouse->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Warehouse updated successfully',
            'data' => $warehouse
        ]);
    }

    public function destroy($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        
        // Check if warehouse has locations
        if ($warehouse->locations()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete warehouse. It has ' . $warehouse->locations()->count() . ' location(s). Please delete locations first.'
            ], 422);
        }

        $warehouse->delete();

        return response()->json([
            'success' => true,
            'message' => 'Warehouse deleted successfully'
        ]);
    }

    /**
     * Bulk delete warehouses (POST AJAX JSON).
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:warehouses,id'
        ]);

        $ids = $request->ids;
        $deleted = 0;
        $failed = 0;
        $errors = [];

        foreach ($ids as $id) {
            $warehouse = Warehouse::find($id);
            if (!$warehouse) {
                $failed++;
                continue;
            }

            // Check if warehouse has locations
            if ($warehouse->locations()->count() > 0) {
                $failed++;
                $errors[] = "Warehouse '{$warehouse->name}' cannot be deleted because it has {$warehouse->locations()->count()} location(s)";
                continue;
            }

            $warehouse->delete();
            $deleted++;
        }

        $message = "Deleted {$deleted} warehouse(s)";
        if ($failed > 0) {
            $message .= ". {$failed} warehouse(s) could not be deleted.";
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
