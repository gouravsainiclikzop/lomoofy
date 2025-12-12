<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SectionsApiController extends Controller
{
    /**
     * Get all active sections for frontend display
     * 
     * Query parameters:
     * - page_url: Filter by page URL (e.g., '/', '/about') - defaults to home page
     * 
     * Returns sections grouped by base_name with active variant already identified.
     * Easy to loop through in frontend.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getSections(Request $request): JsonResponse
    {
        // Find page
        if ($request->has('page_url')) {
            $page = Page::where('url', $request->page_url)->first();
            if (!$page) {
                return response()->json([
                    'success' => false,
                    'message' => 'Page not found',
                    'sections' => [],
                ], 404);
            }
        } else {
            // Default to home page
            $page = Page::where('url', '/')
                ->orWhere('name', 'like', '%home%')
                ->orWhere('name', 'like', '%Home%')
                ->first();
            
            if (!$page) {
                return response()->json([
                    'success' => false,
                    'message' => 'Home page not found',
                    'sections' => [],
                ], 404);
            }
        }

        // Get only active sections ordered by sort_order
        $sections = Section::where('page_id', $page->id)
            ->where('is_active', true)
            ->ordered()
            ->get();

        // Group sections by base_name
        $groupedSections = [];
        foreach ($sections as $section) {
            $baseName = $section->base_section_name;
            
            if (!isset($groupedSections[$baseName])) {
                $groupedSections[$baseName] = [
                    'base_name' => $baseName,
                    'display_name' => ucwords(str_replace(['_', '-'], ' ', $baseName)),
                    'sort_order' => $section->sort_order,
                    'active_variant' => null, // Will be set below
                    'variants' => []
                ];
            }
            
            $variantData = [
                'id' => $section->id,
                'section_id' => $section->section_id,
                'variation_number' => $section->variation_number,
                'is_active' => $section->is_active,
                'image_url' => $section->image_url,
                'image' => $section->image_url,
                'content' => $section->content,
            ];
            
            $groupedSections[$baseName]['variants'][] = $variantData;
            
            // Set active variant (first active one found)
            if ($section->is_active && !$groupedSections[$baseName]['active_variant']) {
                $groupedSections[$baseName]['active_variant'] = $variantData;
            }
        }

        // Sort variants within each group by variation number
        foreach ($groupedSections as &$group) {
            usort($group['variants'], function($a, $b) {
                $aNum = $a['variation_number'] ?? 999;
                $bNum = $b['variation_number'] ?? 999;
                return $aNum <=> $bNum;
            });
        }

        // Sort groups by sort_order and convert to array
        uasort($groupedSections, function($a, $b) {
            return $a['sort_order'] <=> $b['sort_order'];
        });

        $sectionsArray = array_values($groupedSections);

        return response()->json([
            'success' => true,
            'page' => [
                'id' => $page->id,
                'name' => $page->name,
                'url' => $page->url,
            ],
            'sections' => $sectionsArray,
            'count' => count($sectionsArray),
        ]);
    }
}
