<?php

namespace App\Http\Controllers;

use App\Models\AboutUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AboutUsController extends Controller
{
    /**
     * Display the about us page.
     */
    public function index()
    {
        $aboutUs = AboutUs::getInstance();
        return view('admin.about-us.index', compact('aboutUs'));
    }

    /**
     * Update the about us.
     */
    public function update(Request $request)
    {
        $aboutUs = AboutUs::getInstance();

        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['description']);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($aboutUs->image && Storage::disk('public')->exists($aboutUs->image)) {
                Storage::disk('public')->delete($aboutUs->image);
            }
            
            $image = $request->file('image');
            $imagePath = $image->store('about-us', 'public');
            $data['image'] = $imagePath;
        }

        $aboutUs->update($data);
        $aboutUs->refresh();

        return response()->json([
            'success' => true,
            'message' => 'About Us updated successfully',
            'data' => [
                'description' => $aboutUs->description,
                'image' => $aboutUs->image ? asset('storage/' . $aboutUs->image) : null,
            ]
        ]);
    }
}
