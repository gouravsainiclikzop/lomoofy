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
        return view('admin.sections.index');
    }

    /**
     * Get all pages.
     */
    public function getPages()
    {
        $pages = Page::ordered()->get();
        
        return response()->json([
            'success' => true,
            'pages' => $pages
        ]);
    }

    /**
     * Store a new page.
     */
    public function storePage(Request $request)
    {
        // Convert is_active to proper boolean format
        $isActive = filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'url' => 'required|string|max:255|unique:pages,url',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|in:0,1,true,false,on,off,yes,no',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Get next sort order if not provided
        $sortOrder = $request->input('sort_order');
        if ($sortOrder === null) {
            $maxOrder = Page::max('sort_order');
            $sortOrder = ($maxOrder ?? -1) + 1;
        }

        $page = Page::create([
            'name' => $request->name,
            'url' => $request->url,
            'description' => $request->description,
            'sort_order' => $sortOrder,
            'is_active' => $isActive,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Page created successfully',
            'page' => $page
        ]);
    }

    /**
     * Update a page.
     */
    public function updatePage(Request $request)
    {
        $page = Page::findOrFail($request->id);

        // Convert is_active to proper boolean format
        $isActive = $request->has('is_active') 
            ? filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN)
            : $page->is_active;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'url' => 'required|string|max:255|unique:pages,url,' . $page->id,
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|in:0,1,true,false,on,off,yes,no',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $page->update([
            'name' => $request->name,
            'url' => $request->url,
            'description' => $request->description,
            'sort_order' => $request->input('sort_order', $page->sort_order),
            'is_active' => $isActive,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Page updated successfully',
            'page' => $page
        ]);
    }

    /**
     * Delete a page.
     */
    public function deletePage(Request $request)
    {
        $page = Page::findOrFail($request->id);

        // Delete all sections and their images
        foreach ($page->sections as $section) {
            if ($section->image && Storage::disk('public')->exists($section->image)) {
                Storage::disk('public')->delete($section->image);
            }
        }

        $page->delete();

        return response()->json([
            'success' => true,
            'message' => 'Page and all its sections deleted successfully'
        ]);
    }

    /**
     * Get single page for editing.
     */
    public function editPage(Request $request)
    {
        $page = Page::findOrFail($request->id);
        
        return response()->json([
            'success' => true,
            'page' => $page
        ]);
    }

    /**
     * Get sections for a specific page.
     */
    public function getSections(Request $request)
    {
        $pageId = $request->input('page_id');
        
        $sections = Section::forPage($pageId)
            ->ordered()
            ->get()
            ->map(function($section) {
                return [
                    'id' => $section->id,
                    'page_id' => $section->page_id,
                    'section_id' => $section->section_id,
                    'content' => $section->content,
                    'image' => $section->image,
                    'image_url' => $section->image_url,
                    'sort_order' => $section->sort_order,
                    'is_active' => $section->is_active,
                    'base_section_name' => $section->base_section_name,
                    'variation_number' => $section->variation_number,
                    'created_at' => $section->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'success' => true,
            'sections' => $sections
        ]);
    }

    /**
     * Get sections grouped by base name for a specific page.
     */
    public function getPageSections(Request $request)
    {
        $pageId = $request->input('page_id');
        
        // If no page_id provided, try to find home page
        if (!$pageId) {
            $page = Page::where('url', '/')
                ->orWhere('name', 'like', '%home%')
                ->orWhere('name', 'like', '%Home%')
                ->first();
        } else {
            $page = Page::find($pageId);
        }

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found'
            ], 404);
        }

        $sections = Section::forPage($page->id)
            ->ordered()
            ->get();

        // Group sections by base name
        $groupedSections = [];
        foreach ($sections as $section) {
            // Get base name - if it's a variation, extract base name, otherwise use section_id as base
            $baseName = $section->base_section_name;
            // If base_name is same as section_id, it means no variation pattern, so use section_id
            if ($baseName === $section->section_id && !$section->isVariation()) {
                $baseName = $section->section_id;
            }
            
            if (!isset($groupedSections[$baseName])) {
                $groupedSections[$baseName] = [
                    'base_name' => $baseName,
                    'display_name' => ucwords(str_replace(['_', '-'], ' ', $baseName)),
                    'sort_order' => $section->sort_order,
                    'variants' => []
                ];
            }
            
            $groupedSections[$baseName]['variants'][] = [
                'id' => $section->id,
                'section_id' => $section->section_id,
                'variation_number' => $section->variation_number,
                'is_active' => $section->is_active,
                'sort_order' => $section->sort_order,
                'image_url' => $section->image_url,
                'content' => $section->content,
            ];
        }

        // Sort variants within each group by variation number
        foreach ($groupedSections as &$group) {
            usort($group['variants'], function($a, $b) {
                $aNum = $a['variation_number'] ?? 999;
                $bNum = $b['variation_number'] ?? 999;
                return $aNum <=> $bNum;
            });
        }

        // Sort groups by sort_order
        uasort($groupedSections, function($a, $b) {
            return $a['sort_order'] <=> $b['sort_order'];
        });

        return response()->json([
            'success' => true,
            'page' => [
                'id' => $page->id,
                'name' => $page->name,
                'url' => $page->url
            ],
            'sections' => array_values($groupedSections)
        ]);
    }

    /**
     * Get sections grouped by base name for home page (backward compatibility).
     */
    public function getHomePageSections()
    {
        return $this->getPageSections(new Request());
    }

    /**
     * Toggle variant active status.
     */
    public function toggleVariant(Request $request)
    {
        $section = Section::findOrFail($request->id);
        $isActive = $request->input('is_active', !$section->is_active);

        // If activating, deactivate other variations of the same base section
        if ($isActive) {
            $baseName = $section->base_section_name;
            // Check if there are other sections with the same base name
            $otherSections = Section::where('page_id', $section->page_id)
                ->where(function($query) use ($baseName, $section) {
                    // Match variations of the same base
                    $query->where('section_id', 'like', $baseName . '_variation_%')
                          // Or match the base section itself (if this is a variation)
                          ->orWhere('section_id', $baseName);
                })
                ->where('id', '!=', $section->id)
                ->get();
            
            // Only deactivate if there are other variants
            if ($otherSections->count() > 0) {
                Section::where('page_id', $section->page_id)
                    ->where(function($query) use ($baseName) {
                        $query->where('section_id', 'like', $baseName . '_variation_%')
                              ->orWhere('section_id', $baseName);
                    })
                    ->where('id', '!=', $section->id)
                    ->update(['is_active' => false]);
            }
        }

        $section->update(['is_active' => $isActive]);

        return response()->json([
            'success' => true,
            'message' => 'Variant status updated successfully',
            'section' => [
                'id' => $section->id,
                'is_active' => $section->is_active
            ]
        ]);
    }

    /**
     * Update variant image.
     */
    public function updateVariantImage(Request $request)
    {
        $section = Section::findOrFail($request->id);

        // Handle image removal
        if ($request->input('remove_image', false)) {
            if ($section->image && Storage::disk('public')->exists($section->image)) {
                Storage::disk('public')->delete($section->image);
            }
            $section->image = null;
            $section->save();

            return response()->json([
                'success' => true,
                'message' => 'Image removed successfully',
                'section' => [
                    'id' => $section->id,
                    'image_url' => null
                ]
            ]);
        }

        // Handle image upload
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('image')) {
            // Delete old image
            if ($section->image && Storage::disk('public')->exists($section->image)) {
                Storage::disk('public')->delete($section->image);
            }
            $section->image = $request->file('image')->store('sections', 'public');
            $section->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Image updated successfully',
            'section' => [
                'id' => $section->id,
                'image_url' => $section->image_url
            ]
        ]);
    }

    /**
     * Get single section for editing.
     */
    public function edit(Request $request)
    {
        $section = Section::findOrFail($request->id);
        
        return response()->json([
            'success' => true,
            'section' => [
                'id' => $section->id,
                'page_id' => $section->page_id,
                'section_id' => $section->section_id,
                'content' => $section->content,
                'image' => $section->image,
                'image_url' => $section->image_url,
                'sort_order' => $section->sort_order,
                'is_active' => $section->is_active,
            ]
        ]);
    }

    /**
     * Store a new section.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page_id' => 'required|exists:pages,id',
            'section_id' => 'required|string|max:255',
            'content' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('sections', 'public');
        }

        // Get next sort order if not provided
        $sortOrder = $request->input('sort_order');
        if ($sortOrder === null) {
            $maxOrder = Section::forPage($request->page_id)->max('sort_order');
            $sortOrder = ($maxOrder ?? -1) + 1;
        }

        $section = Section::create([
            'page_id' => $request->page_id,
            'section_id' => $request->section_id,
            'content' => $request->content,
            'image' => $imagePath,
            'sort_order' => $sortOrder,
            'is_active' => $request->input('is_active', true),
        ]);

        // Deactivate other variations if this section is active and is a variation
        $section->deactivateOtherVariations();

        return response()->json([
            'success' => true,
            'message' => 'Section created successfully',
            'section' => [
                'id' => $section->id,
                'page_id' => $section->page_id,
                'section_id' => $section->section_id,
                'content' => $section->content,
                'image' => $section->image,
                'image_url' => $section->image_url,
                'sort_order' => $section->sort_order,
                'is_active' => $section->is_active,
            ]
        ]);
    }

    /**
     * Update an existing section.
     */
    public function update(Request $request)
    {
        $section = Section::findOrFail($request->id);

        $validator = Validator::make($request->all(), [
            'page_id' => 'required|exists:pages,id',
            'section_id' => 'required|string|max:255',
            'content' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'remove_image' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle image removal
        if ($request->input('remove_image', false)) {
            if ($section->image && Storage::disk('public')->exists($section->image)) {
                Storage::disk('public')->delete($section->image);
            }
            $section->image = null;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($section->image && Storage::disk('public')->exists($section->image)) {
                Storage::disk('public')->delete($section->image);
            }
            $section->image = $request->file('image')->store('sections', 'public');
        }

        $section->update([
            'page_id' => $request->page_id,
            'section_id' => $request->section_id,
            'content' => $request->content,
            'sort_order' => $request->input('sort_order', $section->sort_order),
            'is_active' => $request->input('is_active', $section->is_active),
        ]);

        // Deactivate other variations if this section is active and is a variation
        $section->deactivateOtherVariations();

        return response()->json([
            'success' => true,
            'message' => 'Section updated successfully',
            'section' => [
                'id' => $section->id,
                'page_id' => $section->page_id,
                'section_id' => $section->section_id,
                'content' => $section->content,
                'image' => $section->image,
                'image_url' => $section->image_url,
                'sort_order' => $section->sort_order,
                'is_active' => $section->is_active,
            ]
        ]);
    }

    /**
     * Delete a section.
     */
    public function delete(Request $request)
    {
        $section = Section::findOrFail($request->id);

        // Delete image if exists
        if ($section->image && Storage::disk('public')->exists($section->image)) {
            Storage::disk('public')->delete($section->image);
        }

        $section->delete();

        return response()->json([
            'success' => true,
            'message' => 'Section deleted successfully'
        ]);
    }

    /**
     * Bulk update sort order (for drag-and-drop).
     */
    public function updateSortOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sections' => 'required|array',
            'sections.*.id' => 'required|exists:sections,id',
            'sections.*.sort_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->sections as $sectionData) {
            Section::where('id', $sectionData['id'])
                ->update(['sort_order' => $sectionData['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sort order updated successfully'
        ]);
    }

    /**
     * Bulk update pages sort order.
     */
    public function updatePagesSortOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pages' => 'required|array',
            'pages.*.id' => 'required|exists:pages,id',
            'pages.*.sort_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->pages as $pageData) {
            Page::where('id', $pageData['id'])
                ->update(['sort_order' => $pageData['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pages sort order updated successfully'
        ]);
    }

    /**
     * Initialize default sections for home page.
     * Uses hardcoded data - can be run multiple times safely.
     * If sections exist, they will be skipped unless force=true.
     */
    public function initializeHomePageSections(Request $request)
    {
        // Find home page
        $homePage = Page::where('url', '/')
            ->orWhere('name', 'like', '%home%')
            ->orWhere('name', 'like', '%Home%')
            ->first();

        if (!$homePage) {
            return response()->json([
                'success' => false,
                'message' => 'Home page not found. Please create a home page first.'
            ], 404);
        }

        $force = $request->input('force', false);
        
        // If force is true, delete existing sections for this page first
        if ($force) {
            $existingSections = Section::where('page_id', $homePage->id)->get();
            foreach ($existingSections as $section) {
                // Delete associated images
                if ($section->image && Storage::disk('public')->exists($section->image)) {
                    Storage::disk('public')->delete($section->image);
                }
            }
            Section::where('page_id', $homePage->id)->delete();
        }

        // Get the maximum sort_order for this page to continue from there
        $maxSortOrder = Section::where('page_id', $homePage->id)->max('sort_order') ?? -1;
        $startSortOrder = $maxSortOrder + 1;

        // Define sections with their variants (hardcoded data)
        $sectionsToCreate = [
            // Section 1: Banner and Slider (3 variants)
            [
                'base_name' => 'banner_slider',
                'display_name' => 'Banner & Slider',
                'sort_order' => $startSortOrder,
                'variants' => [
                    ['section_id' => 'banner_slider_variation_1', 'variation_number' => 1],
                    ['section_id' => 'banner_slider_variation_2', 'variation_number' => 2],
                    ['section_id' => 'banner_slider_variation_3', 'variation_number' => 3],
                ]
            ],
            // Section 2: Popular Products (2 variants)
            [
                'base_name' => 'popular_products',
                'display_name' => 'Popular Products',
                'sort_order' => $startSortOrder + 1,
                'variants' => [
                    ['section_id' => 'popular_products_variation_1', 'variation_number' => 1],
                    ['section_id' => 'popular_products_variation_2', 'variation_number' => 2],
                ]
            ],
            // Section 3: New Arrivals (2 variants)
            [
                'base_name' => 'new_arrivals',
                'display_name' => 'New Arrivals',
                'sort_order' => $startSortOrder + 2,
                'variants' => [
                    ['section_id' => 'new_arrivals_variation_1', 'variation_number' => 1],
                    ['section_id' => 'new_arrivals_variation_2', 'variation_number' => 2],
                ]
            ],
            // Section 4: Discount (2 variants)
            [
                'base_name' => 'discount',
                'display_name' => 'Discount',
                'sort_order' => $startSortOrder + 3,
                'variants' => [
                    ['section_id' => 'discount_variation_1', 'variation_number' => 1],
                    ['section_id' => 'discount_variation_2', 'variation_number' => 2],
                ]
            ],
            // Section 5: Brand Logos (1 variant)
            [
                'base_name' => 'brand_logos',
                'display_name' => 'Brand Logos',
                'sort_order' => $startSortOrder + 4,
                'variants' => [
                    ['section_id' => 'brand_logos', 'variation_number' => null],
                ]
            ],
            // Section 6: Blogs (1 variant)
            [
                'base_name' => 'blogs',
                'display_name' => 'Blogs',
                'sort_order' => $startSortOrder + 5,
                'variants' => [
                    ['section_id' => 'blogs', 'variation_number' => null],
                ]
            ],
            // Section 7: Service Highlight (1 variant)
            [
                'base_name' => 'service_highlight',
                'display_name' => 'Service Highlight',
                'sort_order' => $startSortOrder + 6,
                'variants' => [
                    ['section_id' => 'service_highlight', 'variation_number' => null],
                ]
            ],
        ];

        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($sectionsToCreate as $sectionData) {
            foreach ($sectionData['variants'] as $variant) {
                // Check if section already exists
                $existing = Section::where('page_id', $homePage->id)
                    ->where('section_id', $variant['section_id'])
                    ->first();

                if ($existing) {
                    $skipped++;
                    continue;
                }

                try {
                    Section::create([
                        'page_id' => $homePage->id,
                        'section_id' => $variant['section_id'],
                        'content' => null,
                        'image' => null,
                        'sort_order' => $sectionData['sort_order'],
                        'is_active' => false, // All variants inactive by default
                    ]);
                    $created++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to create {$variant['section_id']}: " . $e->getMessage();
                }
            }
        }

        $message = "Initialization complete. Created: {$created}, Skipped: {$skipped}";
        if ($force) {
            $message = "All sections recreated successfully. Created: {$created}";
        }
        if (!empty($errors)) {
            $message .= ". Errors: " . implode(', ', $errors);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
            'force' => $force
        ]);
    }
}
