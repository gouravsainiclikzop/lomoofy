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
     * Display the our collection page.
     */
    public function index()
    {
        $ourCollection = OurCollection::getInstance();
        return view('admin.our-collection.index', compact('ourCollection'));
    }

    /**
     * Update the our collection.
     */
    public function update(Request $request)
    {
        $ourCollection = OurCollection::getInstance();

        $validator = Validator::make($request->all(), [
            'heading' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['heading', 'description', 'category_id']);

        // Handle background image upload
        if ($request->hasFile('background_image')) {
            // Delete old image if exists
            if ($ourCollection->background_image && Storage::disk('public')->exists($ourCollection->background_image)) {
                Storage::disk('public')->delete($ourCollection->background_image);
            }
            
            $image = $request->file('background_image');
            $imagePath = $image->store('our-collection', 'public');
            $data['background_image'] = $imagePath;
        }

        $ourCollection->update($data);
        $ourCollection->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Our Collection updated successfully',
            'data' => [
                'id' => $ourCollection->id,
                'heading' => $ourCollection->heading,
                'description' => $ourCollection->description,
                'category_id' => $ourCollection->category_id,
                'category_name' => $ourCollection->category ? $ourCollection->category->name : null,
                'category_full_path_name' => $ourCollection->category ? $ourCollection->category->getFullPathName() : null,
                'background_image' => $ourCollection->background_image ? asset('storage/' . $ourCollection->background_image) : null,
            ]
        ]);
    }
}
