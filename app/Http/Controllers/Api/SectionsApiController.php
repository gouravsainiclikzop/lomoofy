<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;

class SectionsApiController extends Controller
{
    /**
     * Get sections for frontend
     * GET /api/sections?page_url=/
     */ 
    public function getSections(Request $request)
    {
        $sections = Section::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function($section) {
                return [
                    'id' => $section->id,
                    'section_id' => $section->section_id,
                    'sort_order' => $section->sort_order,
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $sections,
        ]);
    }
}
