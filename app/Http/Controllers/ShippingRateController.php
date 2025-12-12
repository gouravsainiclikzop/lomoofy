<?php

namespace App\Http\Controllers;

use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShippingRateController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.shipping.rates.index');
    }

    /**
     * Get shipping rates data for DataTables
     */
    public function getData(Request $request)
    {
        $query = ShippingRate::with(['zone', 'method']);

        // Search functionality
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->whereHas('zone', function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('code', 'like', '%' . $search . '%');
                })
                ->orWhereHas('method', function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('code', 'like', '%' . $search . '%');
                });
            });
        }

        // Filter by zone
        if ($request->has('zone_id') && $request->zone_id !== '') {
            $query->where('shipping_zone_id', $request->zone_id);
        }

        // Filter by method
        if ($request->has('method_id') && $request->method_id !== '') {
            $query->where('shipping_method_id', $request->method_id);
        }

        // Filter by rate type
        if ($request->has('rate_type') && $request->rate_type !== '') {
            $query->where('rate_type', $request->rate_type);
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
            
            $rates = $query->orderBy('shipping_zone_id')
                          ->orderBy('shipping_method_id')
                          ->skip($start)
                          ->take($length)
                          ->get();

            $data = $rates->map(function ($rate) {
                return [
                    'id' => $rate->id,
                    'zone_name' => $rate->zone->name ?? 'N/A',
                    'zone_code' => $rate->zone->code ?? 'N/A',
                    'method_name' => $rate->method->name ?? 'N/A',
                    'method_code' => $rate->method->code ?? 'N/A',
                    'rate_type' => $rate->rate_type,
                    'rate' => number_format($rate->rate, 2),
                    'rate_per_kg' => $rate->rate_per_kg ? number_format($rate->rate_per_kg, 2) : '-',
                    'rate_percentage' => $rate->rate_percentage ? number_format($rate->rate_percentage, 2) . '%' : '-',
                    'free_shipping_threshold' => $rate->free_shipping_threshold ? '₹' . number_format($rate->free_shipping_threshold, 2) : '-',
                    'status' => $rate->status,
                    'created_at' => $rate->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'draw' => intval($request->draw ?? 1),
                'recordsTotal' => ShippingRate::count(),
                'recordsFiltered' => $totalRecords,
                'data' => $data,
            ]);
        } else {
            // Simple list request
            $rates = $query->orderBy('shipping_zone_id')->orderBy('shipping_method_id')->get();
            return response()->json([
                'success' => true,
                'data' => $rates,
            ]);
        }
    }

    /**
     * Show the form for editing the specified rate.
     */
    public function edit($id)
    {
        $rate = ShippingRate::with(['zone', 'method'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $rate->id,
                'shipping_zone_id' => $rate->shipping_zone_id,
                'shipping_method_id' => $rate->shipping_method_id,
                'rate_type' => $rate->rate_type,
                'rate' => $rate->rate,
                'rate_per_kg' => $rate->rate_per_kg,
                'rate_percentage' => $rate->rate_percentage,
                'min_value' => $rate->min_value,
                'max_value' => $rate->max_value,
                'fragile_surcharge' => $rate->fragile_surcharge,
                'oversized_surcharge' => $rate->oversized_surcharge,
                'hazardous_surcharge' => $rate->hazardous_surcharge,
                'express_surcharge' => $rate->express_surcharge,
                'free_shipping_threshold' => $rate->free_shipping_threshold,
                'status' => $rate->status,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shipping_zone_id' => 'required|exists:shipping_zones,id',
            'shipping_method_id' => 'required|exists:shipping_methods,id',
            'rate_type' => 'required|in:flat_rate,weight_based,price_based,distance_based',
            'rate' => 'required|numeric|min:0',
            'rate_per_kg' => 'nullable|required_if:rate_type,weight_based|numeric|min:0',
            'rate_percentage' => 'nullable|required_if:rate_type,price_based|numeric|min:0|max:100',
            'min_value' => 'nullable|numeric|min:0',
            'max_value' => 'nullable|numeric|min:0|gte:min_value',
            'fragile_surcharge' => 'nullable|numeric|min:0',
            'oversized_surcharge' => 'nullable|numeric|min:0',
            'hazardous_surcharge' => 'nullable|numeric|min:0',
            'express_surcharge' => 'nullable|numeric|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check for duplicate zone + method + rate_type combination
        $exists = ShippingRate::where('shipping_zone_id', $request->shipping_zone_id)
            ->where('shipping_method_id', $request->shipping_method_id)
            ->where('rate_type', $request->rate_type)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'rate' => ['A rate with this zone, method, and rate type already exists.']
                ]
            ], 422);
        }

        $data = $request->all();
        $data['status'] = $request->status ?? 'active';
        
        // Set null for unused fields based on rate_type
        if ($data['rate_type'] !== 'weight_based') {
            $data['rate_per_kg'] = null;
        }
        if ($data['rate_type'] !== 'price_based') {
            $data['rate_percentage'] = null;
        }

        $rate = ShippingRate::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Shipping rate created successfully',
            'data' => $rate
        ]);
    }

    public function update(Request $request, $id)
    {
        $rate = ShippingRate::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'shipping_zone_id' => 'required|exists:shipping_zones,id',
            'shipping_method_id' => 'required|exists:shipping_methods,id',
            'rate_type' => 'required|in:flat_rate,weight_based,price_based,distance_based',
            'rate' => 'required|numeric|min:0',
            'rate_per_kg' => 'nullable|required_if:rate_type,weight_based|numeric|min:0',
            'rate_percentage' => 'nullable|required_if:rate_type,price_based|numeric|min:0|max:100',
            'min_value' => 'nullable|numeric|min:0',
            'max_value' => 'nullable|numeric|min:0|gte:min_value',
            'fragile_surcharge' => 'nullable|numeric|min:0',
            'oversized_surcharge' => 'nullable|numeric|min:0',
            'hazardous_surcharge' => 'nullable|numeric|min:0',
            'express_surcharge' => 'nullable|numeric|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check for duplicate zone + method + rate_type combination (excluding current)
        $exists = ShippingRate::where('shipping_zone_id', $request->shipping_zone_id)
            ->where('shipping_method_id', $request->shipping_method_id)
            ->where('rate_type', $request->rate_type)
            ->where('id', '!=', $rate->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'rate' => ['A rate with this zone, method, and rate type already exists.']
                ]
            ], 422);
        }

        $data = $request->all();
        $data['status'] = $request->status ?? $rate->status;
        
        // Set null for unused fields based on rate_type
        if ($data['rate_type'] !== 'weight_based') {
            $data['rate_per_kg'] = null;
        }
        if ($data['rate_type'] !== 'price_based') {
            $data['rate_percentage'] = null;
        }

        $rate->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Shipping rate updated successfully',
            'data' => $rate
        ]);
    }

    public function destroy($id)
    {
        $rate = ShippingRate::findOrFail($id);
        $rate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shipping rate deleted successfully'
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:shipping_rates,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        ShippingRate::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Selected shipping rates deleted successfully'
        ]);
    }
}
