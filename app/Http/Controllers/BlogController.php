<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    /**
     * Display a listing of the blogs.
     */
    public function index()
    {
        return view('admin.blogs.index');
    }

    /**
     * Get blogs data for DataTables.
     */
    public function getData(Request $request)
    {
        $query = Blog::query();

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('added_by', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortColumn = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        $blogs = $query->get();

        return response()->json([
            'success' => true,
            'data' => $blogs->map(function($blog) {
                return [
                    'id' => $blog->id,
                    'thumbnail_image' => $blog->thumbnail_url,
                    'title' => $blog->title,
                    'slug' => $blog->slug,
                    'added_by' => $blog->added_by,
                    'published_date' => $blog->published_date ? $blog->published_date->format('Y-m-d') : null,
                    'created_at' => $blog->created_at->format('Y-m-d H:i:s'),
                ];
            })
        ]);
    }

    /**
     * Show the form for creating a new blog.
     */
    public function create()
    {
        return view('admin.blogs.create');
    }

    /**
     * Store a newly created blog in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blogs,slug',
            'description' => 'nullable|string',
            'added_by' => 'nullable|string|max:255',
            'published_date' => 'nullable|date',
            'thumbnail_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = [
            'title' => $request->title,
            'slug' => $request->slug ?: Str::slug($request->title),
            'description' => $request->description,
            'added_by' => $request->added_by,
            'published_date' => $request->published_date ?: now()->toDateString(),
        ];

        // Handle thumbnail image upload
        if ($request->hasFile('thumbnail_image')) {
            $image = $request->file('thumbnail_image');
            $imagePath = $image->store('blogs/thumbnails', 'public');
            $data['thumbnail_image'] = $imagePath;
        }

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $image = $request->file('featured_image');
            $imagePath = $image->store('blogs/featured', 'public');
            $data['featured_image'] = $imagePath;
        }

        $blog = Blog::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Blog created successfully',
            'data' => $blog
        ]);
    }

    /**
     * Display the specified blog.
     */
    public function show($id)
    {
        $blog = Blog::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $blog->id,
                'thumbnail_image' => $blog->thumbnail_url,
                'featured_image' => $blog->featured_image_url,
                'title' => $blog->title,
                'slug' => $blog->slug,
                'description' => $blog->description,
                'added_by' => $blog->added_by,
                'published_date' => $blog->published_date ? $blog->published_date->format('Y-m-d') : null,
            ]
        ]);
    }

    /**
     * Show the form for editing the specified blog.
     */
    public function edit($id)
    {
        $blog = Blog::findOrFail($id);
        return view('admin.blogs.edit', compact('blog'));
    }

    /**
     * Update the specified blog in storage.
     */
    public function update(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blogs,slug,' . $id,
            'description' => 'nullable|string',
            'added_by' => 'nullable|string|max:255',
            'published_date' => 'nullable|date',
            'thumbnail_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = [
            'title' => $request->title,
            'slug' => $request->slug ?: Str::slug($request->title),
            'description' => $request->description,
            'added_by' => $request->added_by,
            'published_date' => $request->published_date,
        ];

        // Handle thumbnail image upload
        if ($request->hasFile('thumbnail_image')) {
            // Delete old image if exists
            if ($blog->thumbnail_image && Storage::disk('public')->exists($blog->thumbnail_image)) {
                Storage::disk('public')->delete($blog->thumbnail_image);
            }
            
            $image = $request->file('thumbnail_image');
            $imagePath = $image->store('blogs/thumbnails', 'public');
            $data['thumbnail_image'] = $imagePath;
        }

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            // Delete old image if exists
            if ($blog->featured_image && Storage::disk('public')->exists($blog->featured_image)) {
                Storage::disk('public')->delete($blog->featured_image);
            }
            
            $image = $request->file('featured_image');
            $imagePath = $image->store('blogs/featured', 'public');
            $data['featured_image'] = $imagePath;
        }

        $blog->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Blog updated successfully',
            'data' => $blog
        ]);
    }

    /**
     * Remove the specified blog from storage.
     */
    public function destroy($id)
    {
        $blog = Blog::findOrFail($id);

        // Delete images if they exist
        if ($blog->thumbnail_image && Storage::disk('public')->exists($blog->thumbnail_image)) {
            Storage::disk('public')->delete($blog->thumbnail_image);
        }
        
        if ($blog->featured_image && Storage::disk('public')->exists($blog->featured_image)) {
            Storage::disk('public')->delete($blog->featured_image);
        }

        $blog->delete();

        return response()->json([
            'success' => true,
            'message' => 'Blog deleted successfully'
        ]);
    }
}
