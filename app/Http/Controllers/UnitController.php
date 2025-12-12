<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unit;
use Illuminate\Support\Str;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Unit::query();
        
        // Filter by type if provided
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        $units = $query->ordered()->get(['id', 'name', 'symbol', 'type', 'is_active']);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $units
            ]);
        }
        
        return view('admin.units.index');
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'type' => 'required|string|max:50',
            'is_active' => 'nullable|in:0,1,true,false'
        ]);

        $unit = Unit::create([
            'name' => $request->name,
            'symbol' => $request->symbol,
            'type' => $request->type,
            'is_active' => filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Unit created successfully!',
                'unit' => $unit
            ]);
        }

        return redirect()->route('units.index')
            ->with('success', 'Unit created successfully!');
    }

    /**
     * Display the specified resource (AJAX only).
     */
    public function show(Request $request, Unit $unit)
    {
        return response()->json([
            'success' => true,
            'unit' => $unit
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Unit $unit)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'type' => 'required|string|max:50',
            'is_active' => 'nullable|in:0,1,true,false'
        ]);

        $unit->update([
            'name' => $request->name,
            'symbol' => $request->symbol,
            'type' => $request->type,
            'is_active' => filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Unit updated successfully!',
                'unit' => $unit
            ]);
        }

        return redirect()->route('units.index')
            ->with('success', 'Unit updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    
    public function destroy(Request $request, Unit $unit)
    {
        // Check if unit is being used by any products
        if ($unit->products()->count() > 0) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete unit. It is being used by ' . $unit->products()->count() . ' product(s).'
                ], 422);
            }
            
            return redirect()->route('units.index')
                ->with('error', 'Cannot delete unit. It is being used by ' . $unit->products()->count() . ' product(s).');
        }

        $unit->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Unit deleted successfully!'
            ]);
        }

        return redirect()->route('units.index')
            ->with('success', 'Unit deleted successfully!');
    }

    /**
     * Get units by type (AJAX)
     */
    public function getByType(Request $request)
    {
        $type = $request->get('type');
        
        if (!$type) {
            return response()->json(['success' => false, 'message' => 'Type parameter required']);
        }

        $units = Unit::where('type', $type)
            ->active()
            ->ordered()
            ->get(['id', 'name', 'symbol']);

        return response()->json([
            'success' => true,
            'units' => $units
        ]);
    }

    /**
     * Toggle unit status (AJAX)
     */
    public function toggleStatus(Request $request, Unit $unit)
    {
        $unit->update(['is_active' => !$unit->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $unit->is_active,
            'message' => 'Unit status updated successfully'
        ]);
    }

    /**
     * Bulk delete units (POST AJAX JSON).
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:units,id'
        ]);

        $ids = $request->ids;
        $deleted = 0;
        $failed = 0;
        $errors = [];

        foreach ($ids as $id) {
            $unit = Unit::find($id);
            if (!$unit) {
                $failed++;
                continue;
            }

            // Check if unit is being used by any products
            if ($unit->products()->count() > 0) {
                $failed++;
                $errors[] = "Unit '{$unit->name}' cannot be deleted because it is being used by {$unit->products()->count()} product(s)";
                continue;
            }

            $unit->delete();
            $deleted++;
        }

        $message = "Deleted {$deleted} unit(s)";
        if ($failed > 0) {
            $message .= ". {$failed} unit(s) could not be deleted.";
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