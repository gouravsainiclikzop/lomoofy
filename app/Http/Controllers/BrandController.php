<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.brands.index');
    }

    /**
     * Get brands data for DataTables or simple list
     */
    public function getData(Request $request)
    {
        $query = Brand::query();

        // Search functionality
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status);
        }

        // Check if this is a DataTables request
        $isDataTableRequest = $request->has('draw');
        
        if ($isDataTableRequest) {
            // DataTables request - apply pagination
            $totalRecords = $query->count();
            $start = intval($request->start ?? 0);
            $length = intval($request->length ?? 10);
            
            $brands = $query->orderBy('sort_order')
                           ->orderBy('name')
                           ->skip($start)
                           ->take($length)
                           ->get();

            $data = $brands->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                    'description' => $brand->description,
                    'logo' => $brand->logo,
                    'website' => $brand->website,
                    'is_active' => $brand->is_active,
                    'sort_order' => $brand->sort_order,
                    'created_at' => $brand->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => Brand::count(),
                'recordsFiltered' => $totalRecords,
                'data' => $data
            ]);
        } else {
            // Simple request - return all brands (for dropdowns, etc.)
            $brands = $query->orderBy('sort_order')
                           ->orderBy('name')
                           ->get();

            $data = $brands->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                    'description' => $brand->description,
                    'logo' => $brand->logo,
                    'website' => $brand->website,
                    'is_active' => $brand->is_active,
                    'sort_order' => $brand->sort_order,
                ];
            });

            return response()->json($data);
        }
    }

    /**
     * Get a single brand for editing
     */
    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'description' => $brand->description,
                'logo' => $brand->logo,
                'website' => $brand->website,
                'is_active' => $brand->is_active,
                'sort_order' => $brand->sort_order,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:brands,name',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'website' => 'nullable|url',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Convert checkbox to boolean
        $data['is_active'] = $request->has('is_active') ? true : false;

        $brand = Brand::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Brand created successfully',
            'data' => $brand
        ]);
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'website' => 'nullable|url',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Convert checkbox to boolean
        $data['is_active'] = $request->has('is_active') ? true : false;

        $brand->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Brand updated successfully',
            'data' => $brand
        ]);
    }

    public function destroy(Brand $brand)
    {
        // Delete logo file
        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }

        $brand->delete();

        return response()->json([
            'success' => true,
            'message' => 'Brand deleted successfully'
        ]);
    }

    /**
     * Bulk delete brands (POST AJAX JSON).
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:brands,id'
        ]);

        $ids = $request->ids;
        $deleted = 0;
        $failed = 0;
        $errors = [];

        foreach ($ids as $id) {
            $brand = Brand::find($id);
            if (!$brand) {
                $failed++;
                continue;
            }

            // Delete logo file
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }

            $brand->delete();
            $deleted++;
        }

        $message = "Deleted {$deleted} brand(s)";
        if ($failed > 0) {
            $message .= ". {$failed} brand(s) could not be deleted.";
        }

        return response()->json([
            'success' => $deleted > 0,
            'message' => $message,
            'deleted' => $deleted,
            'failed' => $failed,
            'errors' => $errors
        ]);
    }
}
