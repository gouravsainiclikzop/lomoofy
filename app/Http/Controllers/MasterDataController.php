<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ProductAttribute;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    /**
     * Get all master data for API.
     */
    public function getAll()
    {
        $data = [
            'categories' => Category::active()->ordered()->get(),
            'attributes' => ProductAttribute::visible()->ordered()->get(),
            'attribute_types' => [
                ['value' => 'text', 'label' => 'Text Input'],
                ['value' => 'select', 'label' => 'Dropdown Select'],
                ['value' => 'color', 'label' => 'Color Picker'],
                ['value' => 'number', 'label' => 'Number Input'],
                ['value' => 'date', 'label' => 'Date Picker'],
                ['value' => 'boolean', 'label' => 'Yes/No'],
            ],
            'product_types' => [
                ['value' => 'simple', 'label' => 'Simple Product'],
                ['value' => 'variable', 'label' => 'Variable Product'],
                ['value' => 'digital', 'label' => 'Digital Product'],
                ['value' => 'service', 'label' => 'Service'],
                ['value' => 'bundle', 'label' => 'Bundle Product'],
                ['value' => 'subscription', 'label' => 'Subscription Product'],
            ],
            'stock_statuses' => [
                ['value' => 'in_stock', 'label' => 'In Stock'],
                ['value' => 'out_of_stock', 'label' => 'Out of Stock'],
                ['value' => 'on_backorder', 'label' => 'On Backorder'],
            ],
            'product_statuses' => [
                ['value' => 'published', 'label' => 'Published'],
                ['value' => 'hidden', 'label' => 'Hidden'],
            ],
        ];

        return response()->json($data);
    }

    /**
     * Export master data.
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'all');
        
        switch ($type) {
            case 'categories':
                $data = Category::with('products')->get();
                break;
            case 'attributes':
                $data = ProductAttribute::with('values')->get();
                break;
            case 'all':
                $data = [
                    'categories' => Category::with('products')->get(),
                    'attributes' => ProductAttribute::with('values')->get(),
                    'exported_at' => now()->toISOString(),
                ];
                break;
            default:
                return response()->json(['error' => 'Invalid export type'], 400);
        }

        return response()->json($data);
    }

    /**
     * Import master data.
     */
    public function import(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'data' => 'required|array',
            'type' => 'required|in:categories,attributes,all',
            'overwrite' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            \DB::beginTransaction();

            $imported = 0;
            $updated = 0;
            $errors = [];

            if ($request->type === 'categories' || $request->type === 'all') {
                foreach ($request->data['categories'] ?? [] as $categoryData) {
                    try {
                        $category = Category::updateOrCreate(
                            ['slug' => $categoryData['slug']],
                            $categoryData
                        );
                        $request->overwrite ? $updated++ : $imported++;
                    } catch (\Exception $e) {
                        $errors[] = "Category {$categoryData['name']}: " . $e->getMessage();
                    }
                }
            }

            if ($request->type === 'attributes' || $request->type === 'all') {
                foreach ($request->data['attributes'] ?? [] as $attributeData) {
                    try {
                        $attribute = ProductAttribute::updateOrCreate(
                            ['slug' => $attributeData['slug']],
                            collect($attributeData)->except('values')->toArray()
                        );

                        if (isset($attributeData['values'])) {
                            foreach ($attributeData['values'] as $valueData) {
                                $attribute->values()->updateOrCreate(
                                    ['value' => $valueData['value']],
                                    $valueData
                                );
                            }
                        }

                        $request->overwrite ? $updated++ : $imported++;
                    } catch (\Exception $e) {
                        $errors[] = "Attribute {$attributeData['name']}: " . $e->getMessage();
                    }
                }
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Import completed successfully',
                'imported' => $imported,
                'updated' => $updated,
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
