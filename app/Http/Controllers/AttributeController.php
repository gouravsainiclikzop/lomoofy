<?php

namespace App\Http\Controllers;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttributeController extends Controller
{
    /**
     * Display a listing of attributes.
     */
    public function index()
    {
        $attributes = ProductAttribute::with('values')->ordered()->get();
        return view('admin.master-data.attributes.index', compact('attributes'));
    }

    /**
     * Show the form for creating a new attribute.
     */
    public function create()
    {
        return view('admin.master-data.attributes.create');
    }

    /**
     * Store a newly created attribute.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:product_attributes,slug',
            'type' => 'required|in:text,select,color,number,date,boolean,image',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Auto-generate slug from name if not provided
        $data = $request->all();
        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
            // Ensure uniqueness
            $originalSlug = $data['slug'];
            $counter = 1;
            while (ProductAttribute::where('slug', $data['slug'])->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }
        
        // Set default values for removed fields
        $data['is_variation'] = false;
        $data['is_visible'] = true;
        $data['is_required'] = false;
        
        // Set default sort_order if not provided
        if (!array_key_exists('sort_order', $data) || $data['sort_order'] === null || $data['sort_order'] === '') {
            $maxSortOrder = ProductAttribute::max('sort_order');
            $data['sort_order'] = ($maxSortOrder !== null) ? $maxSortOrder + 1 : 0;
        }

        $attribute = ProductAttribute::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Attribute created successfully',
            'attribute' => $attribute->load('values')
        ]);
    }

    /**
     * Display the specified attribute.
     */
    public function show(ProductAttribute $attribute)
    {
        $attribute->load('values');
        return response()->json($attribute);
    }

    /**
     * Show the form for editing the specified attribute.
     */
    public function edit(ProductAttribute $attribute)
    {
        $attribute->load('values');
        return view('admin.master-data.attributes.edit', compact('attribute'));
    }

    /**
     * Update the specified attribute.
     */
    public function update(Request $request, ProductAttribute $attribute)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:product_attributes,slug,' . $attribute->id,
            'type' => 'required|in:text,select,color,number,date,boolean,image',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Auto-generate slug from name if not provided or if name changed
        $data = $request->all();
        if (empty($data['slug']) || $data['name'] !== $attribute->name) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
            // Ensure uniqueness (excluding current attribute)
            $originalSlug = $data['slug'];
            $counter = 1;
            while (ProductAttribute::where('slug', $data['slug'])->where('id', '!=', $attribute->id)->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }
        
        // Remove sort_order from update if it's null or empty (keep existing value)
        if (array_key_exists('sort_order', $data) && ($data['sort_order'] === null || $data['sort_order'] === '')) {
            unset($data['sort_order']);
        }

        $attribute->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Attribute updated successfully',
            'attribute' => $attribute->load('values')
        ]);
    }

    /**
     * Remove the specified attribute.
     */
    public function destroy(ProductAttribute $attribute)
    {
        // Check if attribute is used in any products (excluding soft-deleted products)
        // Check via ProductStaticAttribute (static attributes)
        $isUsedInStatic = \App\Models\ProductStaticAttribute::where('attribute_id', $attribute->id)
            ->whereHas('product', function($query) {
                $query->whereNull('deleted_at');
            })
            ->exists();
        
        // Check via ProductVariant (variant attributes stored in JSON)
        // The attributes column stores JSON like: {"Color": "Red", "Size": "Large"}
        // We need to check if the attribute name exists as a key in the JSON
        $attributeName = $attribute->name;
        // Use JSON_EXTRACT to check if the key exists, and exclude soft-deleted products
        $isUsedInVariants = \App\Models\ProductVariant::whereRaw('JSON_EXTRACT(attributes, ?) IS NOT NULL', ['$."' . $attributeName . '"'])
            ->whereHas('product', function($query) {
                $query->whereNull('deleted_at');
            })
            ->exists();
        
        if ($isUsedInStatic || $isUsedInVariants) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete attribute that is being used in products'
            ], 422);
        }

        $attribute->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attribute deleted successfully'
        ]);
    }

    /**
     * Bulk delete attributes (POST AJAX JSON).
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:product_attributes,id'
        ]);

        $ids = $request->ids;
        $deleted = 0;
        $failed = 0;
        $errors = [];

        foreach ($ids as $id) {
            $attribute = ProductAttribute::find($id);
            if (!$attribute) {
                $failed++;
                continue;
            }

            // Check if attribute is used in any products (excluding soft-deleted products)
            // Check via ProductStaticAttribute (static attributes)
            $isUsedInStatic = \App\Models\ProductStaticAttribute::where('attribute_id', $attribute->id)
                ->whereHas('product', function($query) {
                    $query->whereNull('deleted_at');
                })
                ->exists();
            
            // Check via ProductVariant (variant attributes stored in JSON)
            // The attributes column stores JSON like: {"Color": "Red", "Size": "Large"}
            // We need to check if the attribute name exists as a key in the JSON
            $attributeName = $attribute->name;
            // Use JSON_EXTRACT to check if the key exists, and exclude soft-deleted products
            $isUsedInVariants = \App\Models\ProductVariant::whereRaw('JSON_EXTRACT(attributes, ?) IS NOT NULL', ['$."' . $attributeName . '"'])
                ->whereHas('product', function($query) {
                    $query->whereNull('deleted_at');
                })
                ->exists();
            
            if ($isUsedInStatic || $isUsedInVariants) {
                $failed++;
                $errors[] = "Attribute '{$attribute->name}' cannot be deleted because it is being used in products";
                continue;
            }

            $attribute->delete();
            $deleted++;
        }

        // Simplified message
        if ($deleted > 0 && $failed > 0) {
            $message = "{$deleted} attribute(s) deleted. {$failed} attribute(s) could not be deleted (in use).";
        } elseif ($deleted > 0) {
            $message = "{$deleted} attribute(s) deleted successfully.";
        } elseif ($failed > 0) {
            $message = "{$failed} attribute(s) could not be deleted because they are being used in products.";
        } else {
            $message = "No attributes were deleted.";
        }

        return response()->json([
            'success' => $deleted > 0,
            'message' => $message,
            'deleted' => $deleted,
            'failed' => $failed,
            'errors' => $errors
        ]);
    }

    /**
     * Get attribute values for a specific attribute.
     */
    public function getValues(ProductAttribute $attribute)
    {
        $values = $attribute->values()->ordered()->get();
        return response()->json($values);
    }

    /**
     * Store a new attribute value.
     */
    public function storeValue(Request $request, ProductAttribute $attribute)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|string|max:255',
            'color_code' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $payload = $request->all();
        if (!array_key_exists('sort_order', $payload) || $payload['sort_order'] === null || $payload['sort_order'] === '') {
            $payload['sort_order'] = $attribute->values()->max('sort_order') + 1;
            if ($payload['sort_order'] === null || $payload['sort_order'] === false) {
                $payload['sort_order'] = 0;
            }
        }

        $value = $attribute->values()->create($payload);

        return response()->json([
            'success' => true,
            'message' => 'Attribute value created successfully',
            'value' => $value
        ]);
    }

    /**
     * Update an attribute value.
     */
    public function updateValue(Request $request, ProductAttributeValue $value)
    {
        // Only validate fields that are being updated
        $rules = [];
        
        if ($request->has('value')) {
            $rules['value'] = 'required|string|max:255';
        }
        
        if ($request->has('color_code')) {
            $rules['color_code'] = 'nullable|string|max:7';
        }
        
        if ($request->has('sort_order')) {
            $rules['sort_order'] = 'nullable|integer|min:0';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only(['value', 'color_code', 'sort_order']);
        if ($request->has('sort_order') && ($updateData['sort_order'] === null || $updateData['sort_order'] === '')) {
            $updateData['sort_order'] = 0;
        }

        $value->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Attribute value updated successfully',
            'value' => $value
        ]);
    }

    /**
     * Delete an attribute value.
     */
    public function destroyValue(ProductAttributeValue $value)
    {
        // Check if value is used in any products
        $isUsed = $value->isUsedInProducts();
        
        if ($isUsed) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete attribute value that is being used in products'
            ], 422);
        }

        $value->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attribute value deleted successfully'
        ]);
    }

    /**
     * Update sort order for attributes.
     */
    public function updateSortOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'attributes' => 'required|array',
            'attributes.*.id' => 'required|integer|exists:product_attributes,id',
            'attributes.*.sort_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->attributes as $item) {
            ProductAttribute::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sort order updated successfully'
        ]);
    }

    /**
     * Get numeric attributes for measurements (AJAX JSON).
     */
    public function getNumericAttributes(Request $request)
    {
        $attributes = ProductAttribute::where('type', 'number')
            ->where('is_visible', true)
            ->ordered()
            ->get(['id', 'name', 'slug', 'description']);

        return response()->json([
            'success' => true,
            'data' => $attributes->map(function ($attribute) {
                return [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'slug' => $attribute->slug,
                    'description' => $attribute->description,
                ];
            })->values()
        ]);
    }
}
