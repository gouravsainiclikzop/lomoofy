<?php

namespace App\Http\Controllers;

use App\Models\OurCollection;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class OurCollectionController extends Controller
{
    /**
     * Display collections page.
     */
    public function index()
    {
        return view('admin.our-collections.index');
    }

    /**
     * Get all collections data (AJAX JSON).
     */
    public function getData(Request $request)
    {
        $query = OurCollection::with('category')->orderBy('sort_order');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
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

        $collections = $query->get();

        $data = $collections->map(function ($collection) {
            return [
                'id' => $collection->id,
                'category_id' => $collection->category_id,
                'category_name' => $collection->category ? $collection->category->name : 'N/A',
                'title' => $collection->title,
                'featured_image' => $collection->featured_image ? asset('storage/' . $collection->featured_image) : null,
                'is_active' => (bool)$collection->is_active,
                'sort_order' => (int)$collection->sort_order,
                'created_at' => $collection->created_at ? $collection->created_at->format('Y-m-d H:i:s') : 'N/A',
                'updated_at' => $collection->updated_at ? $collection->updated_at->format('Y-m-d H:i:s') : 'N/A',
            ];
        })->values(); // Ensure it's a proper array

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $collections->count()
        ]);
    }

    /**
     * Store a newly created collection.
     */
    public function store(Request $request)
    {
        // Normalize is_active before validation
        $isActive = $request->has('is_active') && (
            $request->is_active === true || 
            $request->is_active === '1' || 
            $request->is_active === 1 ||
            $request->is_active === 'on'
        );
        $request->merge(['is_active' => $isActive]);

        $validator = Validator::make($request->all(), [
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'required|string|max:255',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['category_id', 'title', 'is_active']);
        $data['is_active'] = (bool)($request->is_active ?? true);

        // Handle image upload
        if ($request->hasFile('featured_image')) {
            $image = $request->file('featured_image');
            $imagePath = $image->store('our-collections', 'public');
            $data['featured_image'] = $imagePath;
        }

        // Get max sort_order and add 1
        $maxSortOrder = OurCollection::max('sort_order') ?? 0;
        $data['sort_order'] = $maxSortOrder + 1;

        $collection = OurCollection::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Collection created successfully',
            'data' => [
                'id' => $collection->id,
                'category_id' => $collection->category_id,
                'category_name' => $collection->category ? $collection->category->name : 'N/A',
                'title' => $collection->title,
                'featured_image' => $collection->featured_image ? asset('storage/' . $collection->featured_image) : null,
                'is_active' => $collection->is_active,
                'sort_order' => $collection->sort_order,
            ]
        ]);
    }

    /**
     * Update the specified collection.
     */
    public function update(Request $request, $id)
    {
        $collection = OurCollection::findOrFail($id);

        // Normalize is_active before validation
        $isActive = $request->has('is_active') && (
            $request->is_active === true || 
            $request->is_active === '1' || 
            $request->is_active === 1 ||
            $request->is_active === 'on'
        );
        $request->merge(['is_active' => $isActive]);

        $validator = Validator::make($request->all(), [
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'required|string|max:255',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['category_id', 'title', 'is_active']);
        $data['is_active'] = (bool)($request->is_active ?? $collection->is_active);

        // Handle image upload
        if ($request->hasFile('featured_image')) {
            // Delete old image
            if ($collection->featured_image && Storage::disk('public')->exists($collection->featured_image)) {
                Storage::disk('public')->delete($collection->featured_image);
            }
            
            $image = $request->file('featured_image');
            $imagePath = $image->store('our-collections', 'public');
            $data['featured_image'] = $imagePath;
        }

        $collection->update($data);
        $collection->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Collection updated successfully',
            'data' => [
                'id' => $collection->id,
                'category_id' => $collection->category_id,
                'category_name' => $collection->category ? $collection->category->name : 'N/A',
                'title' => $collection->title,
                'featured_image' => $collection->featured_image ? asset('storage/' . $collection->featured_image) : null,
                'is_active' => $collection->is_active,
                'sort_order' => $collection->sort_order,
            ]
        ]);
    }

    /**
     * Remove the specified collection.
     */
    public function destroy($id)
    {
        $collection = OurCollection::findOrFail($id);

        // Delete image if exists
        if ($collection->featured_image && Storage::disk('public')->exists($collection->featured_image)) {
            Storage::disk('public')->delete($collection->featured_image);
        }

        $collection->delete();

        return response()->json([
            'success' => true,
            'message' => 'Collection deleted successfully'
        ]);
    }

    /**
     * Update sort order (drag and drop).
     */
    public function updateSortOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.id' => 'required|exists:our_collections,id',
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
            OurCollection::where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sort order updated successfully'
        ]);
    }

    /**
     * Get collection by ID for editing.
     */
    public function show($id)
    {
        $collection = OurCollection::with('category')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $collection->id,
                'category_id' => $collection->category_id,
                'category_name' => $collection->category ? $collection->category->name : null,
                'category_full_path_name' => $collection->category ? $collection->category->getFullPathName() : null,
                'title' => $collection->title,
                'featured_image' => $collection->featured_image ? asset('storage/' . $collection->featured_image) : null,
                'is_active' => $collection->is_active,
                'sort_order' => $collection->sort_order,
            ]
        ]);
    }
}
