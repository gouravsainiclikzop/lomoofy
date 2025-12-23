<?php

namespace App\Http\Controllers;

use App\Models\HomeSlider;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class HomeSliderController extends Controller
{
    /**
     * Display home sliders page.
     */
    public function index()
    {
        return view('admin.home-sliders.index');
    }

    /**
     * Get all home sliders data (AJAX JSON).
     */
    public function getData(Request $request)
    {
        $query = HomeSlider::with('category')->orderBy('sort_order');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('tagline', 'like', '%' . $search . '%')
                  ->orWhereHas('category', function($catQuery) use ($search) {
                      $catQuery->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '' && $request->status !== null) {
            $statusValue = $request->status == '1' || $request->status === 1 || $request->status === true || $request->status === 'true';
            $query->where('is_active', $statusValue);
        }

        $sliders = $query->get();

        $data = $sliders->map(function ($slider) {
            return [
                'id' => $slider->id,
                'category_id' => $slider->category_id,
                'category_name' => $slider->category ? $slider->category->name : 'N/A',
                'tagline' => $slider->tagline,
                'title' => $slider->title,
                'image' => $slider->image ? asset('storage/' . $slider->image) : null,
                'is_active' => (bool)$slider->is_active,
                'sort_order' => (int)$slider->sort_order,
                'created_at' => $slider->created_at ? $slider->created_at->format('Y-m-d H:i:s') : 'N/A',
                'updated_at' => $slider->updated_at ? $slider->updated_at->format('Y-m-d H:i:s') : 'N/A',
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $sliders->count()
        ]);
    }

    /**
     * Store a newly created home slider.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'nullable|exists:categories,id',
            'tagline' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['category_id', 'tagline', 'title']);
        $data['is_active'] = true; // Default active

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('home-sliders', 'public');
            $data['image'] = $imagePath;
        }

        // Get max sort_order and add 1
        $maxSortOrder = HomeSlider::max('sort_order') ?? 0;
        $data['sort_order'] = $maxSortOrder + 1;

        $slider = HomeSlider::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Home slider created successfully',
            'data' => [
                'id' => $slider->id,
                'category_id' => $slider->category_id,
                'category_name' => $slider->category ? $slider->category->name : 'N/A',
                'tagline' => $slider->tagline,
                'title' => $slider->title,
                'image' => $slider->image ? asset('storage/' . $slider->image) : null,
                'is_active' => $slider->is_active,
                'sort_order' => $slider->sort_order,
            ]
        ]);
    }

    /**
     * Update the specified home slider.
     */
    public function update(Request $request, $id)
    {
        $slider = HomeSlider::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'category_id' => 'nullable|exists:categories,id',
            'tagline' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['category_id', 'tagline', 'title']);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($slider->image && Storage::disk('public')->exists($slider->image)) {
                Storage::disk('public')->delete($slider->image);
            }
            
            $image = $request->file('image');
            $imagePath = $image->store('home-sliders', 'public');
            $data['image'] = $imagePath;
        }

        $slider->update($data);
        $slider->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Home slider updated successfully',
            'data' => [
                'id' => $slider->id,
                'category_id' => $slider->category_id,
                'category_name' => $slider->category ? $slider->category->name : 'N/A',
                'tagline' => $slider->tagline,
                'title' => $slider->title,
                'image' => $slider->image ? asset('storage/' . $slider->image) : null,
                'is_active' => $slider->is_active,
                'sort_order' => $slider->sort_order,
            ]
        ]);
    }

    /**
     * Update status (active/inactive).
     */
    public function updateStatus(Request $request, $id)
    {
        $slider = HomeSlider::findOrFail($id);

        // Normalize is_active before validation
        $isActive = $request->has('is_active') && (
            $request->is_active === true || 
            $request->is_active === '1' || 
            $request->is_active === 1 ||
            $request->is_active === 'true' ||
            $request->is_active === 'on'
        );
        $request->merge(['is_active' => $isActive]);

        $validator = Validator::make($request->all(), [
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $slider->update(['is_active' => $isActive]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => [
                'id' => $slider->id,
                'is_active' => $slider->is_active,
            ]
        ]);
    }

    /**
     * Remove the specified home slider.
     */
    public function destroy($id)
    {
        $slider = HomeSlider::findOrFail($id);

        // Delete image if exists
        if ($slider->image && Storage::disk('public')->exists($slider->image)) {
            Storage::disk('public')->delete($slider->image);
        }

        $slider->delete();

        return response()->json([
            'success' => true,
            'message' => 'Home slider deleted successfully'
        ]);
    }

    /**
     * Update sort order (drag and drop).
     */
    public function updateSortOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.id' => 'required|exists:home_sliders,id',
            'items.*.sort_order' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->items as $item) {
            HomeSlider::where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sort order updated successfully'
        ]);
    }

    /**
     * Get home slider by ID for editing.
     */
    public function show($id)
    {
        $slider = HomeSlider::with('category')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $slider->id,
                'category_id' => $slider->category_id,
                'category_name' => $slider->category ? $slider->category->name : null,
                'category_full_path_name' => $slider->category ? $slider->category->getFullPathName() : null,
                'tagline' => $slider->tagline,
                'title' => $slider->title,
                'image' => $slider->image ? asset('storage/' . $slider->image) : null,
                'is_active' => $slider->is_active,
                'sort_order' => $slider->sort_order,
            ]
        ]);
    }
}
