<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Page;
use Illuminate\Http\Request;

class SectionsApiController extends Controller
{
    /**
     * Get sections for frontend
     * GET /api/sections?page_url=/
     */
    public function getSections(Request $request)
    {
        $pageUrl = $request->input('page_url', '/');
        
        // Find page by URL
        $page = Page::where('url', $pageUrl)->first();
        
        if (!$page) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }
        
        $sections = Section::where('page_id', $page->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function($section) {
                return [
                    'id' => $section->id,
                    'section_id' => $section->section_id,
                    'content' => $section->content,
                    'image_url' => $section->image ? asset('storage/' . $section->image) : null,
                    'sort_order' => $section->sort_order,
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $sections,
        ]);
    }
}
