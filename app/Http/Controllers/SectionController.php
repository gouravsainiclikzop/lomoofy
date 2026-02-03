<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SectionController extends Controller
{
    /**
     * Display sections management page.
     */
    public function index()
{
    // Canonical definition of home sections (order + status controlled here)
    $sections = [
        ['section_id' => 'istopbar-v1', 'title' => 'Top Bar', 'sort_order' => 1, 'is_active' => 0,'showSortingBtn'=> true],
        ['section_id' => 'isnavbar-v1', 'title' => 'Navbar V1', 'sort_order' => 2, 'is_active' => 1,'showSortingBtn'=> true],
        ['section_id' => 'isnavbar-v2', 'title' => 'Navbar V2', 'sort_order' => 3, 'is_active' => 0,'showSortingBtn'=> true],
        ['section_id' => 'issliderbanner-v1', 'title' => 'Slider Banner V1', 'sort_order' => 4, 'is_active' => 0,'showSortingBtn'=> true],
        ['section_id' => 'issliderbanner-v2', 'title' => 'Slider Banner V2', 'sort_order' => 5, 'is_active' => 0,'showSortingBtn'=> true],
        ['section_id' => 'isfeaturedcategory-v1', 'title' => 'Featured Category V1', 'sort_order' => 6, 'is_active' => 0,'showSortingBtn'=> true],
        ['section_id' => 'isfeaturedcategory-v2', 'title' => 'Featured Category V2', 'sort_order' => 7, 'is_active' => 0,'showSortingBtn'=> true],
        ['section_id' => 'isfeaturedcategory-v3', 'title' => 'Featured Category V3', 'sort_order' => 8, 'is_active' => 0,'showSortingBtn'=> true],
        ['section_id' => 'isfeaturedcategory-v4', 'title' => 'Featured Category V4', 'sort_order' => 9, 'is_active' => 0,'showSortingBtn'=> true],
        ['section_id' => 'isfeaturedcategory-v5', 'title' => 'Featured Category V5', 'sort_order' => 10, 'is_active' => 0,'showSortingBtn'=> true],
        ['section_id' => 'isfeaturedcategory-v6', 'title' => 'Featured Category V6', 'sort_order' => 11, 'is_active' => 0,'showSortingBtn'=> true],
        ['section_id' => 'isdealsoftheday-v1', 'title' => 'Deals of the Day', 'sort_order' => 12, 'is_active' => 0,'showSortingBtn'=> true],
        ['section_id' => 'isproductwithcategorytabs-v1', 'title' => 'Products with Category Tabs', 'sort_order' => 13, 'is_active' => 0,'showSortingBtn'=> true],
        ['section_id' => 'isbestseller-v1', 'title' => 'Best Seller', 'sort_order' => 14, 'is_active' => 0,'showSortingBtn'=> true],
        ['section_id' => 'istrendingcategories-v1', 'title' => 'Trending Categories', 'sort_order' => 15, 'is_active' => 0,'showSortingBtn'=> true],
        ['section_id' => 'isourcollection-v1', 'title' => 'Our Collection V1', 'sort_order' => 16, 'is_active' => 0,'showSortingBtn'=> true],
        ['section_id' => 'isourcollection-v2', 'title' => 'Our Collection V2', 'sort_order' => 17, 'is_active' => 0,'showSortingBtn'=> true],
        ['section_id' => 'isnewarrivals-v1', 'title' => 'New Arrivals', 'sort_order' => 18, 'is_active' => 0,'showSortingBtn'=> true],
        ['section_id' => 'isparentcategoriescards-v1', 'title' => 'Parent Category Cards', 'sort_order' => 19, 'is_active' => 0,'showSortingBtn'=> true],
        ['section_id' => 'isrecentlyviewed-v1', 'title' => 'Recently Viewed', 'sort_order' => 20, 'is_active' => 0,'showSortingBtn'=> true],
        ['section_id' => 'istestimonials-v1', 'title' => 'Testimonials', 'sort_order' => 21, 'is_active' => 0,'showSortingBtn'=> true],
        ['section_id' => 'isblog-v1', 'title' => 'Blog', 'sort_order' => 22, 'is_active' => 0,'showSortingBtn'=> true],
        ['section_id' => 'isinstagram-v1', 'title' => 'Instagram Feed', 'sort_order' => 23, 'is_active' => 0,'showSortingBtn'=> true],
        ['section_id' => 'ishighlights-v1', 'title' => 'Service Highlights', 'sort_order' => 24, 'is_active' => 0,'showSortingBtn'=> true],
    ];
      
    // foreach ($sections as $section) {
    //     Section::updateOrCreate(
    //         ['section_id' => $section['section_id']],
    //         [
    //             'title'      => $section['title'],
    //             'sort_order' => $section['sort_order'],
    //             'is_active'  => $section['is_active'],
    //         ]
    //     );
    // }

    // Section IDs that must not show the sort/drag handle (showSortingBtn = false)
    $noSortSectionIds = array_column(array_filter($sections, function ($s) {
        return isset($s['showSortingBtn']) && $s['showSortingBtn'] === false;
    }), 'section_id');

    $allSections = Section::orderBy('sort_order')->get();
    $groupedSections = $this->groupSectionsByBaseName($allSections, $noSortSectionIds);
    
    $companySettings = \App\Models\CompanySetting::getSettings();
    
    // Always sync color themes with latest definitions
    $latestThemes = $this->getColorThemes();
    
    // Preserve active theme if it still exists in new themes, otherwise reset it
    $activeTheme = $companySettings->active_color_theme;
    if ($activeTheme && !isset($latestThemes[$activeTheme])) {
        $activeTheme = null;
    }
    
    // Update themes and active theme
    $companySettings->color_themes = $latestThemes;
    if ($activeTheme !== $companySettings->active_color_theme) {
        $companySettings->active_color_theme = $activeTheme;
    }
    $companySettings->save();

    return view('admin.sections.index', compact('groupedSections', 'companySettings'));
}


 
    private function groupSectionsByBaseName($sections, array $noSortSectionIds = [])
    {
        $grouped = [];
        
        foreach ($sections as $section) {
            // Extract base name (remove -v1, -v2, etc.)
            $baseName = preg_replace('/-v\d+$/', '', $section->section_id);
            
            if (!isset($grouped[$baseName])) {
                $grouped[$baseName] = [
                    'base_name' => $baseName,
                    'display_name' => $this->getDisplayName($baseName),
                    'variants' => []
                ];
            }
            
            $grouped[$baseName]['variants'][] = $section;
        }
        
        // Sort variants within each group by sort_order; set show_sorting_btn per group
        foreach ($grouped as &$group) {
            usort($group['variants'], function($a, $b) {
                return $a->sort_order <=> $b->sort_order;
            });
            // Show sort handle only if at least one variant has showSortingBtn = true
            $variantSectionIds = array_column($group['variants'], 'section_id');
            $group['show_sorting_btn'] = !empty(array_diff($variantSectionIds, $noSortSectionIds));
        }
        
        return $grouped;
    }

    /**
     * Get display name from base section name.
     */
    private function getDisplayName($baseName)
    { 
        $name = preg_replace('/^is/', '', $baseName);
        $name = str_replace(['-', '_'], ' ', $name);
        return ucwords($name);
    }
 
   
    private function getColorThemes()
{
    return [

        "jewellery_luxury" => [
            "backgrounds" => ["#FFFBF5", "#FDF6E3", "#F5E6C8"],
            "text" => ["#2A1E14"],
            "muted_text" => ["#8A7968"],
            "anchors" => ["#2A1E14"],
            "hover" => ["#B45309"],
            "span" => ["#D97706"],
            "borders" => ["#E7D3A3"]
        ],

        "furniture_earth" => [
            "backgrounds" => ["#FFFFFF", "#F7F3EF", "#EFE7DE"],
            "text" => ["#3F2E1B"],
            "muted_text" => ["#7C6F63"],
            "anchors" => ["#3F2E1B"],
            "hover" => ["#8B4513"],
            "span" => ["#A16207"],
            "borders" => ["#DDD3C6"]
        ],

        "clean_dynamic" => [
            "backgrounds" => ["#FFFFFF", "#F9FAFB", "#EEF2FF"],
            "text" => ["#111827"],
            "muted_text" => ["#6B7280"],
            "anchors" => ["#111827"],
            "hover" => ["#DC2626"],
            "span" => ["#DC2626"],
            "borders" => ["#E5E7EB"]
        ],

        "lifestyle_soft" => [
            "backgrounds" => ["#FFFFFF", "#F0FDF4", "#ECFEFF"],
            "text" => ["#064E3B"],
            "muted_text" => ["#6B7280"],
            "anchors" => ["#064E3B"],
            "hover" => ["#0F766E"],
            "span" => ["#DC2626"],
            "borders" => ["#D1FAE5"]
        ],

        "warm_neutral" => [
            "backgrounds" => ["#FFFFFF", "#FAF7F2", "#F1ECE4"],
            "text" => ["#2F2A25"],
            "muted_text" => ["#7A7268"],
            "anchors" => ["#2F2A25"],
            "hover" => ["#9A3412"],
            "span" => ["#DC2626"],
            "borders" => ["#E4DDD4"]
        ],

        "soft_grey_modern" => [
            "backgrounds" => ["#FFFFFF", "#F4F4F5", "#E5E7EB"],
            "text" => ["#18181B"],
            "muted_text" => ["#71717A"],
            "anchors" => ["#18181B"],
            "hover" => ["#DC2626"],
            "span" => ["#DC2626"],
            "borders" => ["#D4D4D8"]
        ],

        "calm_blue" => [
            "backgrounds" => ["#FFFFFF", "#F0F9FF", "#E0F2FE"],
            "text" => ["#0F172A"],
            "muted_text" => ["#64748B"],
            "anchors" => ["#0F172A"],
            "hover" => ["#2563EB"],
            "span" => ["#DC2626"],
            "borders" => ["#CBD5E1"]
        ],

        "soft_beige" => [
            "backgrounds" => ["#FFFFFF", "#FBF5EE", "#F3EDE5"],
            "text" => ["#3A2F27"],
            "muted_text" => ["#8B8177"],
            "anchors" => ["#3A2F27"],
            "hover" => ["#B45309"],
            "span" => ["#DC2626"],
            "borders" => ["#E6DDD3"]
        ],

        // "stone_luxury" => [
        //     "backgrounds" => ["#F7F6F3", "#D6D3D1", "#A8A29E"],
        //     "text" => ["#1C1917"],
        //     "muted_text" => ["#78716C"],
        //     "anchors" => ["#1C1917"],
        //     "hover" => ["#92400E"],
        //     "span" => ["#DC2626"],
        //     "borders" => ["#A8A29E"]
        // ],

        "espresso_gold_soft_dark" => [
            "backgrounds" => ["#F6EFE7", "#EADFCC", "#D8C9B6"],
            "text" => ["#2B211B"],
            "muted_text" => ["#7A6F66"],
            "anchors" => ["#2B211B"],
            "hover" => ["#B45309"],
            "span" => ["#D97706"],
            "borders" => ["#CDBEAF"]
        ],  
    ];
}


    
    public function toggleVariant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'section_id' => 'required|exists:sections,id',
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $section = Section::findOrFail($request->section_id);
        $section->is_active = $request->is_active;
        $section->save();

        // If activating a variant, deactivate other variants of the same base
        // if ($request->is_active) {
        //     $baseName = preg_replace('/-v\d+$/', '', $section->section_id);
        //     Section::where('id', '!=', $section->id)
        //         ->where('section_id', 'like', $baseName . '-v%')
        //         ->update(['is_active' => false]);
        // }

        return response()->json([
            'success' => true,
            'message' => 'Section status updated successfully'
        ]);
    }

// update the sort order of the sections
    public function updateSortOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.id' => 'required|exists:sections,id',
            'items.*.sort_order' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->items as $item) {
            Section::where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sort order updated successfully'
        ]);
    }

    /**
     * Update font family setting.
     */
    public function updateFontFamily(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'font_family' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $settings = \App\Models\CompanySetting::getSettings();
        $settings->font_family = $request->font_family;
        $settings->save();

        return response()->json([
            'success' => true,
            'message' => 'Font family updated successfully',
            'font_family' => $settings->font_family
        ]);
    }

    /**
     * Update active color theme.
     */
    public function updateColorTheme(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'theme_name' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $settings = \App\Models\CompanySetting::getSettings();
        
        // Initialize themes if not set
        if (empty($settings->color_themes)) {
            $settings->color_themes = $this->getColorThemes();
        }
        
        // Validate theme name exists in themes
        $themeName = $request->theme_name;
        if ($themeName && !isset($settings->color_themes[$themeName])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid theme name',
            ], 422);
        }
        
        $settings->active_color_theme = $themeName ?: null;
        $settings->save();

        return response()->json([
            'success' => true,
            'message' => $themeName ? 'Color theme activated successfully' : 'Color theme deactivated successfully',
            'active_theme' => $settings->active_color_theme
        ]);
    }
 
}
