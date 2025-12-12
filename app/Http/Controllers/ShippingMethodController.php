<?php

namespace App\Http\Controllers;

use App\Models\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShippingMethodController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.shipping.methods.index');
    }

    /**
     * Get shipping methods data for DataTables
     */
    public function getData(Request $request)
    {
        $query = ShippingMethod::withCount('rates');

        // Search functionality
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
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
            
            $methods = $query->ordered()
                           ->skip($start)
                           ->take($length)
                           ->get();

            $data = $methods->map(function ($method) {
                return [
                    'id' => $method->id,
                    'name' => $method->name,
                    'code' => $method->code,
                    'description' => $method->description,
                    'estimated_delivery' => $method->estimated_delivery,
                    'estimated_days_min' => $method->estimated_days_min,
                    'estimated_days_max' => $method->estimated_days_max,
                    'status' => $method->status,
                    'rates_count' => $method->rates_count,
                    'sort_order' => $method->sort_order,
                    'created_at' => $method->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'draw' => intval($request->draw ?? 1),
                'recordsTotal' => ShippingMethod::count(),
                'recordsFiltered' => $totalRecords,
                'data' => $data,
            ]);
        } else {
            // Simple list request
            $methods = $query->ordered()->get();
            return response()->json([
                'success' => true,
                'data' => $methods,
            ]);
        }
    }

    /**
     * Show the form for editing the specified method.
     */
    public function edit($id)
    {
        $method = ShippingMethod::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $method->id,
                'name' => $method->name,
                'code' => $method->code,
                'description' => $method->description,
                'estimated_days_min' => $method->estimated_days_min,
                'estimated_days_max' => $method->estimated_days_max,
                'status' => $method->status,
                'sort_order' => $method->sort_order,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:shipping_methods,code',
            'description' => 'nullable|string',
            'estimated_days_min' => 'nullable|integer|min:0',
            'estimated_days_max' => 'nullable|integer|min:0|gte:estimated_days_min',
            'status' => 'nullable|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['status'] = $request->status ?? 'active';
        $data['sort_order'] = $request->sort_order ?? 0;

        $method = ShippingMethod::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Shipping method created successfully',
            'data' => $method
        ]);
    }

    public function update(Request $request, $id)
    {
        $method = ShippingMethod::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:shipping_methods,code,' . $method->id,
            'description' => 'nullable|string',
            'estimated_days_min' => 'nullable|integer|min:0',
            'estimated_days_max' => 'nullable|integer|min:0|gte:estimated_days_min',
            'status' => 'nullable|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['status'] = $request->status ?? $method->status;
        $data['sort_order'] = $request->sort_order ?? $method->sort_order;

        $method->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Shipping method updated successfully',
            'data' => $method
        ]);
    }

    public function destroy($id)
    {
        $method = ShippingMethod::findOrFail($id);
        
        // Check if method has rates
        if ($method->rates()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete method with existing shipping rates. Please delete rates first.'
            ], 422);
        }
        
        $method->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shipping method deleted successfully'
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:shipping_methods,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if any method has rates
        $methodsWithRates = ShippingMethod::whereIn('id', $request->ids)
            ->has('rates')
            ->pluck('name')
            ->toArray();

        if (!empty($methodsWithRates)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete methods with existing rates: ' . implode(', ', $methodsWithRates)
            ], 422);
        }

        ShippingMethod::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Selected shipping methods deleted successfully'
        ]);
    }
}
