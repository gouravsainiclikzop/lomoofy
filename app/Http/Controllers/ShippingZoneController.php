<?php

namespace App\Http\Controllers;

use App\Models\ShippingZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ShippingZoneController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.shipping.zones.index');
    }

    /**
     * Get shipping zones data for DataTables
     */
    public function getData(Request $request)
    {
        $query = ShippingZone::withCount('rates');

        // Search functionality
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('country', 'like', '%' . $search . '%');
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        // Check if this is a DataTables request
        $isDataTableRequest = $request->has('draw');
        
        if ($isDataTableRequest) {
            // DataTables request - apply pagination
            $totalRecords = $query->count();
            $start = intval($request->start ?? 0);
            $length = intval($request->length ?? 10);
            
            $zones = $query->ordered()
                          ->skip($start)
                          ->take($length)
                          ->get();

            $data = $zones->map(function ($zone) {
                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'code' => $zone->code,
                    'type' => $zone->type,
                    'description' => $zone->description,
                    'coverage' => $zone->coverage_description,
                    'country' => $zone->country,
                    'status' => $zone->status,
                    'rates_count' => $zone->rates_count,
                    'sort_order' => $zone->sort_order,
                    'created_at' => $zone->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'draw' => intval($request->draw ?? 1),
                'recordsTotal' => ShippingZone::count(),
                'recordsFiltered' => $totalRecords,
                'data' => $data,
            ]);
        } else {
            // Simple list request
            $zones = $query->ordered()->get();
            return response()->json([
                'success' => true,
                'data' => $zones,
            ]);
        }
    }

    /**
     * Show the form for editing the specified zone.
     */
    public function edit($id)
    {
        $zone = ShippingZone::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $zone->id,
                'name' => $zone->name,
                'code' => $zone->code,
                'type' => $zone->type,
                'description' => $zone->description,
                'pincodes' => $zone->pincodes ?? [],
                'states' => $zone->states ?? [],
                'cities' => $zone->cities ?? [],
                'country' => $zone->country,
                'status' => $zone->status,
                'sort_order' => $zone->sort_order,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:shipping_zones,code',
            'type' => 'required|in:pincode,state,city,country',
            'description' => 'nullable|string',
            'pincodes' => 'nullable|array',
            'pincodes.*' => 'nullable|string|max:10',
            'states' => 'nullable|array',
            'states.*' => 'nullable|string|max:100',
            'cities' => 'nullable|array',
            'cities.*' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
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
        
        // Clean up empty arrays
        if (empty($data['pincodes'])) $data['pincodes'] = null;
        if (empty($data['states'])) $data['states'] = null;
        if (empty($data['cities'])) $data['cities'] = null;

        $zone = ShippingZone::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Shipping zone created successfully',
            'data' => $zone
        ]);
    }

    public function update(Request $request, $id)
    {
        $zone = ShippingZone::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:shipping_zones,code,' . $zone->id,
            'type' => 'required|in:pincode,state,city,country',
            'description' => 'nullable|string',
            'pincodes' => 'nullable|array',
            'pincodes.*' => 'nullable|string|max:10',
            'states' => 'nullable|array',
            'states.*' => 'nullable|string|max:100',
            'cities' => 'nullable|array',
            'cities.*' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
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
        $data['status'] = $request->status ?? $zone->status;
        $data['sort_order'] = $request->sort_order ?? $zone->sort_order;
        
        // Clean up empty arrays
        if (empty($data['pincodes'])) $data['pincodes'] = null;
        if (empty($data['states'])) $data['states'] = null;
        if (empty($data['cities'])) $data['cities'] = null;

        $zone->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Shipping zone updated successfully',
            'data' => $zone
        ]);
    }

    public function destroy($id)
    {
        $zone = ShippingZone::findOrFail($id);
        
        // Check if zone has rates
        if ($zone->rates()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete zone with existing shipping rates. Please delete rates first.'
            ], 422);
        }
        
        $zone->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shipping zone deleted successfully'
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:shipping_zones,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if any zone has rates
        $zonesWithRates = ShippingZone::whereIn('id', $request->ids)
            ->has('rates')
            ->pluck('name')
            ->toArray();

        if (!empty($zonesWithRates)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete zones with existing rates: ' . implode(', ', $zonesWithRates)
            ], 422);
        }

        ShippingZone::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Selected shipping zones deleted successfully'
        ]);
    }
}
