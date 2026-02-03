<?php

namespace App\Http\Controllers;

use App\Models\InstagramGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class InstagramGalleryController extends Controller
{
    /**
     * Display Instagram Gallery management page.
     */
    public function index()
    {
        return view('admin.instagram-gallery.index');
    }

    /**
     * Get all gallery items (AJAX JSON).
     */
    public function getData(Request $request)
    {
        $query = InstagramGallery::orderBy('sort_order');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('instagram_link', 'like', '%' . $search . '%');
            });
        }

        $items = $query->get();

        $data = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'thumbnail_image' => $item->thumbnail_image ? asset('storage/' . $item->thumbnail_image) : null,
                'instagram_link' => $item->instagram_link,
                'is_active' => (bool) $item->is_active,
                'sort_order' => (int) $item->sort_order,
                'created_at' => $item->created_at ? $item->created_at->format('Y-m-d H:i') : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $items->count(),
        ]);
    }

    /**
     * Store a single gallery item.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'thumbnail_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'instagram_link' => 'nullable|url|max:500',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = [
            'instagram_link' => $request->instagram_link,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->hasFile('thumbnail_image')) {
            $data['thumbnail_image'] = $request->file('thumbnail_image')->store('instagram-gallery', 'public');
        }

        $maxSort = InstagramGallery::max('sort_order') ?? 0;
        $data['sort_order'] = $maxSort + 1;

        $item = InstagramGallery::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Gallery item created successfully',
            'data' => $this->itemToArray($item),
        ]);
    }

    /**
     * Store multiple gallery items in one request (Add More).
     */
    public function storeBulk(Request $request)
    {
        $fileItems = $request->file('items', []);
        if (! is_array($fileItems)) {
            $fileItems = [];
        }

        $maxSort = (int) InstagramGallery::max('sort_order');
        $created = [];
        $index = 0;

        foreach ($fileItems as $key => $row) {
            $file = is_array($row) ? ($row['thumbnail_image'] ?? null) : null;
            if (! $file || ! $file->isValid()) {
                continue;
            }

            $validator = Validator::make(
                ['thumbnail_image' => $file, 'instagram_link' => $request->input('items.' . $key . '.instagram_link')],
                [
                    'thumbnail_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                    'instagram_link' => 'nullable|url|max:500',
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $imagePath = $file->store('instagram-gallery', 'public');
            $link = $request->input('items.' . $key . '.instagram_link');
            $isActive = $request->boolean('items.' . $key . '.is_active');

            $item = InstagramGallery::create([
                'thumbnail_image' => $imagePath,
                'instagram_link' => $link ?: null,
                'is_active' => $isActive,
                'sort_order' => $maxSort + $index + 1,
            ]);
            $created[] = $this->itemToArray($item);
            $index++;
        }

        if (empty($created)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid items to save. Please add at least one image.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => count($created) . ' gallery item(s) created successfully',
            'data' => $created,
        ]);
    }

    /**
     * Get a single gallery item for editing.
     */
    public function show($id)
    {
        $item = InstagramGallery::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $this->itemToArray($item),
        ]);
    }

    /**
     * Update the specified gallery item.
     */
    public function update(Request $request, $id)
    {
        $item = InstagramGallery::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'thumbnail_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'instagram_link' => 'nullable|url|max:500',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = [
            'instagram_link' => $request->instagram_link,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->hasFile('thumbnail_image')) {
            if ($item->thumbnail_image && Storage::disk('public')->exists($item->thumbnail_image)) {
                Storage::disk('public')->delete($item->thumbnail_image);
            }
            $data['thumbnail_image'] = $request->file('thumbnail_image')->store('instagram-gallery', 'public');
        }

        $item->update($data);
        $item->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Gallery item updated successfully',
            'data' => $this->itemToArray($item),
        ]);
    }

    /**
     * Toggle active status.
     */
    public function updateStatus(Request $request, $id)
    {
        $item = InstagramGallery::findOrFail($id);
        $item->is_active = $request->boolean('is_active');
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated',
            'data' => $this->itemToArray($item),
        ]);
    }

    /**
     * Remove the specified gallery item.
     */
    public function destroy($id)
    {
        $item = InstagramGallery::findOrFail($id);
        if ($item->thumbnail_image && Storage::disk('public')->exists($item->thumbnail_image)) {
            Storage::disk('public')->delete($item->thumbnail_image);
        }
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gallery item deleted successfully',
        ]);
    }

    /**
     * Update sort order.
     */
    public function updateSortOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.id' => 'required|exists:instagram_gallery,id',
            'items.*.sort_order' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        foreach ($request->items as $row) {
            InstagramGallery::where('id', $row['id'])->update(['sort_order' => $row['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sort order updated successfully',
        ]);
    }

    private function itemToArray(InstagramGallery $item): array
    {
        return [
            'id' => $item->id,
            'thumbnail_image' => $item->thumbnail_image ? asset('storage/' . $item->thumbnail_image) : null,
            'instagram_link' => $item->instagram_link,
            'is_active' => (bool) $item->is_active,
            'sort_order' => (int) $item->sort_order,
        ];
    }
}
