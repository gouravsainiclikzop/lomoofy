<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeadMasterController extends Controller
{
    /**
     * Display the lead masters management page.
     */
    public function index()
    {
        return view('admin.lead-masters.index');
    }

    /**
     * Get all master data (AJAX).
     */
    public function getData()
    {
        $statuses = DB::table('lead_statuses')->orderBy('sort_order')->get();
        $sources = DB::table('lead_sources')->orderBy('sort_order')->get();
        $priorities = DB::table('lead_priorities')->orderBy('sort_order')->get();
        $tags = DB::table('lead_tags')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'statuses' => $statuses,
                'sources' => $sources,
                'priorities' => $priorities,
                'tags' => $tags,
            ]
        ]);
    }

    /**
     * Update a status master.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        DB::table('lead_statuses')
            ->where('id', $id)
            ->update([
                'name' => $request->name,
                'is_active' => $request->is_active ?? true,
                'sort_order' => $request->sort_order ?? 0,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    }

    /**
     * Update a source master.
     */
    public function updateSource(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        DB::table('lead_sources')
            ->where('id', $id)
            ->update([
                'name' => $request->name,
                'is_active' => $request->is_active ?? true,
                'sort_order' => $request->sort_order ?? 0,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Source updated successfully'
        ]);
    }

    /**
     * Update a priority master.
     */
    public function updatePriority(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        DB::table('lead_priorities')
            ->where('id', $id)
            ->update([
                'name' => $request->name,
                'is_active' => $request->is_active ?? true,
                'sort_order' => $request->sort_order ?? 0,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Priority updated successfully'
        ]);
    }

    /**
     * Store a new status master.
     */
    public function storeStatus(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $slug = Str::slug($request->name);
        
        // Check if slug already exists
        $exists = DB::table('lead_statuses')->where('slug', $slug)->exists();
        if ($exists) {
            $slug = $slug . '-' . time();
        }

        $id = DB::table('lead_statuses')->insertGetId([
            'name' => $request->name,
            'slug' => $slug,
            'is_active' => $request->is_active ?? true,
            'sort_order' => $request->sort_order ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status created successfully',
            'data' => DB::table('lead_statuses')->where('id', $id)->first()
        ]);
    }

    /**
     * Store a new source master.
     */
    public function storeSource(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $slug = Str::slug($request->name);
        
        // Check if slug already exists
        $exists = DB::table('lead_sources')->where('slug', $slug)->exists();
        if ($exists) {
            $slug = $slug . '-' . time();
        }

        $id = DB::table('lead_sources')->insertGetId([
            'name' => $request->name,
            'slug' => $slug,
            'is_active' => $request->is_active ?? true,
            'sort_order' => $request->sort_order ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Source created successfully',
            'data' => DB::table('lead_sources')->where('id', $id)->first()
        ]);
    }

    /**
     * Store a new priority master.
     */
    public function storePriority(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $slug = Str::slug($request->name);
        
        // Check if slug already exists
        $exists = DB::table('lead_priorities')->where('slug', $slug)->exists();
        if ($exists) {
            $slug = $slug . '-' . time();
        }

        $id = DB::table('lead_priorities')->insertGetId([
            'name' => $request->name,
            'slug' => $slug,
            'is_active' => $request->is_active ?? true,
            'sort_order' => $request->sort_order ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Priority created successfully',
            'data' => DB::table('lead_priorities')->where('id', $id)->first()
        ]);
    }

    /**
     * Delete a status master.
     */
    public function deleteStatus($id)
    {
        // Check if status is being used by any leads
        $used = DB::table('leads')->where('status_id', $id)->exists();
        if ($used) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete status. It is being used by one or more leads.'
            ], 422);
        }

        DB::table('lead_statuses')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Status deleted successfully'
        ]);
    }

    /**
     * Delete a source master.
     */
    public function deleteSource($id)
    {
        // Check if source is being used by any leads
        $used = DB::table('leads')->where('source_id', $id)->exists();
        if ($used) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete source. It is being used by one or more leads.'
            ], 422);
        }

        DB::table('lead_sources')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Source deleted successfully'
        ]);
    }

    /**
     * Delete a priority master.
     */
    public function deletePriority($id)
    {
        // Get the priority to check its name/slug
        $priority = DB::table('lead_priorities')->where('id', $id)->first();
        
        if (!$priority) {
            return response()->json([
                'success' => false,
                'message' => 'Priority not found.'
            ], 404);
        }

        // Check if priority is being used by any leads
        // Priority is stored as lowercase string in leads table (e.g., "low", "medium", "high")
        // So we check by converting the priority name to lowercase
        $priorityValue = strtolower($priority->name);
        $used = DB::table('leads')
            ->whereRaw('LOWER(priority) = ?', [$priorityValue])
            ->exists();
            
        if ($used) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete priority. It is being used by one or more leads.'
            ], 422);
        }

        DB::table('lead_priorities')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Priority deleted successfully'
        ]);
    }

    /**
     * Update a tag master.
     */
    public function updateTag(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $slug = Str::slug($request->name);
        
        // Check if slug already exists (excluding current tag)
        $exists = DB::table('lead_tags')->where('slug', $slug)->where('id', '!=', $id)->exists();
        if ($exists) {
            $slug = $slug . '-' . time();
        }

        DB::table('lead_tags')
            ->where('id', $id)
            ->update([
                'name' => $request->name,
                'slug' => $slug,
                'is_active' => $request->is_active ?? true,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Tag updated successfully'
        ]);
    }

    /**
     * Store a new tag master.
     */
    public function storeTag(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $slug = Str::slug($request->name);
        
        // Check if slug already exists
        $exists = DB::table('lead_tags')->where('slug', $slug)->exists();
        if ($exists) {
            $slug = $slug . '-' . time();
        }

        $id = DB::table('lead_tags')->insertGetId([
            'name' => $request->name,
            'slug' => $slug,
            'is_active' => $request->is_active ?? true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tag created successfully',
            'data' => DB::table('lead_tags')->where('id', $id)->first()
        ]);
    }

    /**
     * Delete a tag master.
     */
    public function deleteTag($id)
    {
        // Check if tag is being used by any leads
        $used = DB::table('lead_tag')
            ->where('lead_tag_id', $id)
            ->exists();
            
        if ($used) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete tag. It is being used by one or more leads.'
            ], 422);
        }

        DB::table('lead_tags')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tag deleted successfully'
        ]);
    }
}
