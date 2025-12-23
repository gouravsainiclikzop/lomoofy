<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TestimonialController extends Controller
{
    /**
     * Display testimonials page.
     */
    public function index()
    {
        return view('admin.testimonials.index');
    }

    /**
     * Get all testimonials data (AJAX JSON).
     */
    public function getData(Request $request)
    {
        $query = Testimonial::orderBy('sort_order');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $testimonials = $query->get();

        $data = $testimonials->map(function ($testimonial) {
            return [
                'id' => $testimonial->id,
                'name' => $testimonial->name,
                'title' => $testimonial->title,
                'description' => $testimonial->description,
                'image' => $testimonial->image ? asset('storage/' . $testimonial->image) : null,
                'sort_order' => (int)$testimonial->sort_order,
                'created_at' => $testimonial->created_at ? $testimonial->created_at->format('Y-m-d H:i:s') : 'N/A',
                'updated_at' => $testimonial->updated_at ? $testimonial->updated_at->format('Y-m-d H:i:s') : 'N/A',
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $testimonials->count()
        ]);
    }

    /**
     * Store a newly created testimonial.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['name', 'title', 'description']);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('testimonials', 'public');
            $data['image'] = $imagePath;
        }

        // Get max sort_order and add 1
        $maxSortOrder = Testimonial::max('sort_order') ?? 0;
        $data['sort_order'] = $maxSortOrder + 1;

        $testimonial = Testimonial::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Testimonial created successfully',
            'data' => [
                'id' => $testimonial->id,
                'name' => $testimonial->name,
                'title' => $testimonial->title,
                'description' => $testimonial->description,
                'image' => $testimonial->image ? asset('storage/' . $testimonial->image) : null,
                'sort_order' => $testimonial->sort_order,
            ]
        ]);
    }

    /**
     * Update the specified testimonial.
     */
    public function update(Request $request, $id)
    {
        $testimonial = Testimonial::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['name', 'title', 'description']);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($testimonial->image && Storage::disk('public')->exists($testimonial->image)) {
                Storage::disk('public')->delete($testimonial->image);
            }
            
            $image = $request->file('image');
            $imagePath = $image->store('testimonials', 'public');
            $data['image'] = $imagePath;
        }

        $testimonial->update($data);
        $testimonial->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Testimonial updated successfully',
            'data' => [
                'id' => $testimonial->id,
                'name' => $testimonial->name,
                'title' => $testimonial->title,
                'description' => $testimonial->description,
                'image' => $testimonial->image ? asset('storage/' . $testimonial->image) : null,
                'sort_order' => $testimonial->sort_order,
            ]
        ]);
    }

    /**
     * Remove the specified testimonial.
     */
    public function destroy($id)
    {
        $testimonial = Testimonial::findOrFail($id);

        // Delete image if exists
        if ($testimonial->image && Storage::disk('public')->exists($testimonial->image)) {
            Storage::disk('public')->delete($testimonial->image);
        }

        $testimonial->delete();

        return response()->json([
            'success' => true,
            'message' => 'Testimonial deleted successfully'
        ]);
    }

    /**
     * Update sort order (drag and drop).
     */
    public function updateSortOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.id' => 'required|exists:testimonials,id',
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
            Testimonial::where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sort order updated successfully'
        ]);
    }

    /**
     * Get testimonial by ID for editing.
     */
    public function show($id)
    {
        $testimonial = Testimonial::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $testimonial->id,
                'name' => $testimonial->name,
                'title' => $testimonial->title,
                'description' => $testimonial->description,
                'image' => $testimonial->image ? asset('storage/' . $testimonial->image) : null,
                'sort_order' => $testimonial->sort_order,
            ]
        ]);
    }
}
