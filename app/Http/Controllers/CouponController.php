<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return view('admin.coupons.index');
    }

    /**
     * Get coupons data for DataTables
     */
    public function getData(Request $request)
    {
        try {
            
            
            // Build query with filters
            $query = Coupon::query(); 

            // Search
            if ($request->has('search') && is_array($request->search) && !empty($request->search['value'])) {
                $search = $request->search['value'];
                \Illuminate\Support\Facades\Log::info('Applying search filter: ' . $search);
                $query->where(function($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%");
                });
            }

            // Filter by status - only if explicitly set and not null/empty
            if ($request->filled('status')) {
                $statusValue = $request->status == '1' ? 1 : 0; 
                $query->where('status', $statusValue);
            }

            // Filter by discount type - only if explicitly set and not null/empty
            if ($request->filled('discount_type')) { 
                $query->where('discount_type', $request->discount_type);
            }

            // Get total records (all coupons, no filters)
            $totalRecords = Coupon::count(); 
            
            // Get filtered count BEFORE adding orderBy/skip/take
            // Rebuild the query for counting to avoid query builder state issues
            $countQuery = Coupon::query();
            
            // Apply same filters to count query
            if ($request->has('search') && is_array($request->search) && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $countQuery->where('code', 'like', "%{$search}%");
            }
            if ($request->filled('status')) {
                $statusValue = $request->status == '1' ? 1 : 0;
                $countQuery->where('status', $statusValue);
            }
            if ($request->filled('discount_type')) {
                $countQuery->where('discount_type', $request->discount_type);
            }
            
            $filteredRecords = $countQuery->count(); 

            // Pagination
            $start = $request->get('start', 0);
            $length = $request->get('length', 10); 
            
            $coupons = $query->orderBy('created_at', 'desc')
                ->skip($start)
                ->take($length)
                ->get();
             

            $data = $coupons->map(function($coupon) {
                try {
                    return [
                        'id' => $coupon->id,
                        'code' => $coupon->code,
                        'discount_type' => ucfirst($coupon->discount_type),
                        'discount_value' => $coupon->discount_type === 'percentage' 
                            ? number_format($coupon->discount_value, 2) . '%' 
                            : '₹' . number_format($coupon->discount_value, 2),
                        'min_order_amount' => $coupon->min_order_amount 
                            ? '₹' . number_format($coupon->min_order_amount, 2) 
                            : 'No minimum',
                        'max_uses' => $coupon->max_uses ?? 'Unlimited',
                        'uses' => $coupon->uses,
                        'start_date' => $coupon->start_date ? $coupon->start_date->format('M d, Y') : 'N/A',
                        'end_date' => $coupon->end_date ? $coupon->end_date->format('M d, Y') : 'N/A',
                        'status' => (bool)$coupon->status,
                        'is_expired' => $coupon->end_date ? $coupon->end_date < now() : false,
                        'can_be_used' => method_exists($coupon, 'canBeUsed') ? $coupon->canBeUsed() : true,
                    ];
                } catch (\Exception $e) {
                   
                    return [
                        'id' => $coupon->id,
                        'code' => $coupon->code,
                        'discount_type' => ucfirst($coupon->discount_type),
                        'discount_value' => $coupon->discount_type === 'percentage' 
                            ? number_format($coupon->discount_value, 2) . '%' 
                            : '₹' . number_format($coupon->discount_value, 2),
                        'min_order_amount' => $coupon->min_order_amount 
                            ? '₹' . number_format($coupon->min_order_amount, 2) 
                            : 'No minimum',
                        'max_uses' => $coupon->max_uses ?? 'Unlimited',
                        'uses' => $coupon->uses,
                        'start_date' => $coupon->start_date ? $coupon->start_date->format('M d, Y') : 'N/A',
                        'end_date' => $coupon->end_date ? $coupon->end_date->format('M d, Y') : 'N/A',
                        'status' => (bool)$coupon->status,
                        'is_expired' => $coupon->end_date ? $coupon->end_date < now() : false,
                        'can_be_used' => true,
                    ];
                }
            });
 
            
            return response()->json([
                'draw' => intval($request->get('draw', 1)),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
          
            return response()->json([
                'draw' => intval($request->get('draw', 1)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred while loading data.',
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Prepare status value before validation
        $request->merge([
            'status' => filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true
        ]);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'boolean',
        ], [
            'code.unique' => 'This coupon code already exists.',
            'discount_value.min' => 'Discount value must be at least 0.01.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
        ]);

        // Additional validation for percentage discount
        if ($validated['discount_type'] === 'percentage' && $validated['discount_value'] > 100) {
            return response()->json([
                'success' => false,
                'message' => 'Percentage discount cannot exceed 100%.',
                'errors' => ['discount_value' => ['Percentage discount cannot exceed 100%.']]
            ], 422);
        }

        $coupon = Coupon::create([
            'code' => strtoupper($validated['code']),
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'min_order_amount' => $validated['min_order_amount'] ?? null,
            'max_uses' => $validated['max_uses'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'] ?? true,
            'uses' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Coupon created successfully.',
            'coupon' => $coupon,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $coupon = Coupon::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
                'min_order_amount' => $coupon->min_order_amount,
                'max_uses' => $coupon->max_uses,
                'start_date' => $coupon->start_date->format('Y-m-d'),
                'end_date' => $coupon->end_date->format('Y-m-d'),
                'status' => $coupon->status,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        // Prepare status value before validation
        $request->merge([
            'status' => filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true
        ]);

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('coupons', 'code')->ignore($coupon->id),
            ],
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'boolean',
        ], [
            'code.unique' => 'This coupon code already exists.',
            'discount_value.min' => 'Discount value must be at least 0.01.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
        ]);

        // Additional validation for percentage discount
        if ($validated['discount_type'] === 'percentage' && $validated['discount_value'] > 100) {
            return response()->json([
                'success' => false,
                'message' => 'Percentage discount cannot exceed 100%.',
                'errors' => ['discount_value' => ['Percentage discount cannot exceed 100%.']]
            ], 422);
        }

        $coupon->update([
            'code' => strtoupper($validated['code']),
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'min_order_amount' => $validated['min_order_amount'] ?? null,
            'max_uses' => $validated['max_uses'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Coupon updated successfully.',
            'coupon' => $coupon,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Coupon deleted successfully.',
        ]);
    }

    /**
     * Toggle coupon status
     */
    public function toggleStatus($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->status = !$coupon->status;
        $coupon->save();

        return response()->json([
            'success' => true,
            'message' => $coupon->status ? 'Coupon activated.' : 'Coupon deactivated.',
            'status' => $coupon->status,
        ]);
    }

    /**
     * Generate unique coupon code
     */
    public function generateCode()
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Coupon::where('code', $code)->exists());

        return response()->json([
            'success' => true,
            'code' => $code,
        ]);
    }

    /**
     * Validate coupon code uniqueness
     */
    public function validateCode(Request $request)
    {
        $code = strtoupper($request->code);
        $exists = Coupon::where('code', $code)
            ->when($request->has('id'), function($query) use ($request) {
                $query->where('id', '!=', $request->id);
            })
            ->exists();

        return response()->json([
            'valid' => !$exists,
            'message' => $exists ? 'This coupon code already exists.' : 'Code is available.',
        ]);
    }
}
