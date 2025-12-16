<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductStaticAttribute;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\VariantHeadingSuggestion;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

const OTHER_BRAND_SLUG = 'other';

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index()
    {
        return view('admin.products.index');
    }

    /**
     * Get SEO data for a product.
     */
    public function getSeo(Product $product)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'meta_title' => $product->meta_title,
                'meta_description' => $product->meta_description,
                'metadata' => $product->metadata,
                'json_ld' => $product->json_ld,
            ]
        ]);
    }

    /**
     * Get heading suggestions for variant highlights & details.
     */
    public function getHeadingSuggestions()
    {
        try {
            $suggestions = VariantHeadingSuggestion::getSuggestions(20);
            return response()->json([
                'success' => true,
                'suggestions' => $suggestions
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching heading suggestions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'suggestions' => []
            ], 500);
        }
    }

    /**
     * Save a heading suggestion.
     */
    public function saveHeadingSuggestion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'heading_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $heading = VariantHeadingSuggestion::findOrCreate($request->heading_name);
            return response()->json([
                'success' => true,
                'message' => 'Heading suggestion saved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving heading suggestion: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save heading suggestion'
            ], 500);
        }
    }

    /**
     * Update SEO data for a product.
     */
    public function updateSeo(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'metadata' => 'nullable|string',
            'json_ld' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $jsonLd = null;
            if ($request->has('json_ld') && !empty($request->input('json_ld'))) {
                $decoded = json_decode($request->input('json_ld'), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $jsonLd = $decoded;
                }
            }

            $product->update([
                'meta_title' => $request->input('meta_title'),
                'meta_description' => $request->input('meta_description'),
                'metadata' => $request->input('metadata'),
                'json_ld' => $jsonLd,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'SEO settings updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating SEO: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update SEO settings'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $otherBrand = $this->ensureOtherBrand();

        $brands = Brand::active()->ordered()->get();
        $brands = collect([$otherBrand])->merge(
            $brands->reject(fn ($brand) => $brand->id === $otherBrand->id)
        )->values();
        $categories = Category::active()->ordered()->get();
        $attributes = ProductAttribute::visible()->ordered()->get();
        $units = Unit::active()->ordered()->get();
        $product = new Product(); // Empty product for form defaults

        $otherBrandId = $otherBrand->id;

        return view('admin.products.create', compact('brands', 'categories', 'attributes', 'units', 'product', 'otherBrandId'));
    }

    /**
     * Show the quick create form for products.
     */
    public function quickCreate()
    {
        $otherBrand = $this->ensureOtherBrand();
        $brands = Brand::active()->ordered()->get();
        $brands = collect([$otherBrand])->merge(
            $brands->reject(fn ($brand) => $brand->id === $otherBrand->id)
        )->values();
        $categories = Category::active()->ordered()->get();
        $attributes = ProductAttribute::visible()->ordered()->get();
        $units = Unit::active()->ordered()->get();
        $product = new Product();

        $otherBrandId = $otherBrand->id;
        $isQuickCreate = true;

        return view('admin.products.quick-create', compact(
            'brands',
            'categories',
            'attributes',
            'units',
            'product',
            'otherBrandId',
            'isQuickCreate'
        ));
    }

    /**
     * Store a quickly created product.
     */
    public function quickStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'brand_ids' => 'nullable|array',
            'brand_ids.*' => 'exists:brands,id',
            'type' => 'required|in:simple,variable,digital,service,bundle,subscription',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $otherBrand = $this->ensureOtherBrand();
            $brandIds = $request->brand_ids ?? [];

            if (empty($brandIds)) {
                $brandIds = [$otherBrand->id];
            }

            // Create the product with basic information
            $product = Product::create([
                'name' => $request->name,
                'sku' => $request->sku,
                'price' => $request->price,
                'type' => $request->type,
                'brand_id' => $brandIds[0],
                'status' => 'hidden',
                'stock_status' => 'in_stock',
                'manage_stock' => true,
                'stock_quantity' => 0,
                'requires_shipping' => $request->type !== 'digital' && $request->type !== 'service',
            ]);

            // Attach brands
            $brandData = [];
            foreach ($brandIds as $index => $brandId) {
                $brandData[$brandId] = [
                    'is_primary' => $index === 0, // First brand is primary
                    'sort_order' => $index,
                ];
            }
            $product->brands()->attach($brandData);

            // Ensure a default variant exists for quick create
            $defaultVariant = $this->buildDefaultVariantPayload($request, $product);
            $this->synchronizeVariants($product, [$defaultVariant]);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully!',
                'product' => $product,
                'redirect_url' => route('products.edit', $product)
            ]);

        } catch (QueryException $e) {
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'products_slug_unique')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please correct the errors below and try again.',
                    'errors' => [
                        'slug' => ['This Product Slug is already taken. Please choose a different one.']
                    ]
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error creating product: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $this->prepareProductRequestDefaults($request);
        $resolvedType = $request->input('type');

        Log::info('ProductController@store - Incoming request data', [
            'payload' => $request->except(['images', 'variants']),
            'has_images' => $request->hasFile('images'),
            'has_variants' => $request->has('variants'),
        ]);
        
        // Calculate the slug that will be saved (for validation)
        $slugToValidate = $request->filled('seo_url_slug')
            ? Str::slug($request->seo_url_slug)
            : Str::slug($request->name);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'type' => 'required|in:simple,variable,digital,service,bundle,subscription',
            'brand_ids' => 'nullable|array',
            'brand_ids.*' => 'exists:brands,id',
            'seo_url_slug' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($slugToValidate) { 
                    if (empty($slugToValidate)) {
                        return;
                    }
                     
                    $exists = Product::where('slug', $slugToValidate)->exists(); 
                    if ($exists) {
                        $fail('The seo url slug has already been taken.');
                    }
                },
            ],
            'requires_shipping' => 'nullable|in:on,1,true',
            'free_shipping' => 'nullable|in:on,1,true',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'tags' => 'nullable|string|max:1000',
            'status' => 'required|in:published,hidden',
            'visibility' => 'nullable|in:published,hidden',
            'published_at' => 'nullable|date',
            'featured' => 'nullable|in:on,1,true',
            'gst_type' => 'nullable|in:0,1',
            'gst_percentage' => 'nullable|in:0,3,5,12,18,28',
            'category_id' => 'nullable|exists:categories,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'image_alt_texts' => 'nullable|array',
            'image_alt_texts.*' => 'nullable|string|max:255',
            'image_sort_orders' => 'nullable|array',
            'image_sort_orders.*' => 'nullable|integer|min:0',
            'primary_image_index' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== null && $value !== '') {
                    // Accept integer or string in format "new_X" where X is a number
                    if (!is_numeric($value) && !preg_match('/^new_\d+$/', $value)) {
                        $fail('The primary image index must be an integer or in the format "new_X".');
                    }
                }
            }],
            'variants' => 'nullable|array',
            'variants.*.sku' => 'required|string|max:255',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0',
            'variants.*.cost_price' => 'nullable|numeric|min:0',
            'variants.*.low_stock_threshold' => 'nullable|integer|min:0',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.length' => 'nullable|numeric|min:0',
            'variants.*.width' => 'nullable|numeric|min:0',
            'variants.*.height' => 'nullable|numeric|min:0',
            'variants.*.is_active' => 'nullable|boolean',
            'variants.*.images' => 'nullable|array',
            'variants.*.images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $validator->after(function ($validator) use ($request) {
            // Ensure at least one variant is provided
            $variants = $request->input('variants', []);
            if (empty($variants) || !is_array($variants) || count(array_filter($variants)) === 0) {
                $validator->errors()->add('variants', 'At least one variant is required. Please add a variant in the Variants section.');
            }
        });

        if ($validator->fails()) {
            // Custom error messages for better user experience
            $customErrors = [];
            $errors = $validator->errors();

            $fieldMappings = [
                'name' => 'Product Name',
                'sku' => 'SKU',
                'price' => 'Regular Price',
                'brand_ids' => 'Brand Selection',
                'brand_ids.*' => 'Brand Selection',
                'stock_quantity' => 'Stock Quantity',
                'stock_status' => 'Stock Status',
                'type' => 'Product Type',
                'status' => 'Product Status'
            ];

            foreach ($errors->messages() as $field => $messages) {
                $fieldName = $fieldMappings[$field] ?? Str::of($field)
                    ->replace(['_', '.'], ' ')
                    ->title()
                    ->value();

                $customErrors[$field] = array_map(function ($message) use ($fieldName) {
                    if (str_contains($message, 'required')) {
                        return "The {$fieldName} field is required.";
                    }
                    if (str_contains($message, 'unique')) {
                        return "This {$fieldName} is already taken. Please choose a different one.";
                    }
                    if (str_contains($message, 'numeric')) {
                        return "The {$fieldName} must be a valid number.";
                    }
                    if (str_contains($message, 'min:')) {
                        return "The {$fieldName} must be at least 0.";
                    }
                    if (str_contains($message, 'array')) {
                        return "Please select at least one {$fieldName}.";
                    }
                    return $message;
                }, $messages);
            }

            return response()->json([
                'success' => false,
                'message' => 'Please correct the errors below and try again.',
                'errors' => $customErrors
            ], 422);
        }

        DB::beginTransaction();

        try {
            $otherBrand = $this->ensureOtherBrand();
            $brandIds = $request->brand_ids ?? [];
            if (empty($brandIds)) {
                $brandIds = [$otherBrand->id];
            }

            $slug = $request->filled('seo_url_slug')
                ? Str::slug($request->seo_url_slug)
                : Str::slug($request->name);
            
            $product = Product::create([
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'short_description' => $request->short_description,
                'brand_id' => $brandIds[0], // Keep for backward compatibility
                'requires_shipping' => $request->has('requires_shipping'),
                'free_shipping' => $request->has('free_shipping'),
                'gst_type' => $request->has('gst_type') ? ($request->gst_type == '1' || $request->gst_type == 1) : true,
                'gst_percentage' => $request->gst_percentage,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'meta_keywords' => $request->meta_keywords,
                'tags' => $request->tags,
                'status' => $request->visibility ?? $request->status,
                'published_at' => $request->published_at,
                'featured' => $request->has('featured'),
                'category_id' => $request->category_ids && count($request->category_ids) > 0 ? $request->category_ids[0] : $request->category_id,
            ]);

            // Handle categories
            if ($request->category_ids && count($request->category_ids) > 0) {
                $categoryData = [];
                foreach ($request->category_ids as $index => $categoryId) {
                    $categoryData[$categoryId] = [
                        'is_primary' => $index === 0, // First category is primary
                    ];
                }
                $product->categories()->sync($categoryData);
            } elseif ($request->category_id) {
                // Fallback: handle single category_id for backward compatibility
                $product->categories()->sync([
                    $request->category_id => [
                        'is_primary' => true,
                    ],
                ]);
            }

            // Handle brands
            if ($request->brand_ids) {
                $brandData = [];
                foreach ($brandIds as $index => $brandId) {
                    $brandData[$brandId] = [
                        'is_primary' => $index === 0, // First brand is primary
                        'sort_order' => $index,
                    ];
                }
                $product->brands()->attach($brandData);
            } else {
                $product->brands()->attach([
                    $brandIds[0] => [
                        'is_primary' => true,
                        'sort_order' => 0,
                    ],
                ]);
            }

            // Handle images
            if ($request->hasFile('images')) {
                $this->handleImageUploads($product, $request);
            }

            // Always create variants - auto-generate default variant if none provided
            $variantsPayload = $this->filterProvidedVariants($request->input('variants', []));
            $variantsPayload = $this->mergeVariantFileUploads($variantsPayload, $request->file('variants', []));

            Log::info('ProductController@store - Filtered variant payload', [
                'product_id' => $product->id,
                'variants' => $this->sanitizeVariantsForLog($variantsPayload),
            ]);

            // If no variants provided, create a default variant
            if (empty($variantsPayload)) {
                $variantsPayload = [$this->buildDefaultVariantPayload($request, $product)];
            }

            $this->synchronizeVariants($product, $variantsPayload);

            // Handle static attributes
            $this->synchronizeStaticAttributes($product, $request);

            $product->load(['variants.images', 'category.parent']);
            Log::info('ProductController@store - Persisted product snapshot', [
                'product_id' => $product->id,
                'variants' => $product->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'price' => $variant->price,
                        'sale_price' => $variant->sale_price,
                        'discount_type' => $variant->discount_type,
                        'discount_value' => $variant->discount_value,
                        'discount_active' => $variant->discount_active,
                        'measurements' => $variant->measurements,
                        'images' => $variant->images->pluck('image_path'),
                    ];
                }),
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'path' => $product->category_path,
                ] : null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'product' => $product->load(['images', 'category.parent', 'variants'])
            ]);

        } catch (QueryException $e) {
            DB::rollBack();
            if ($e->getCode() === '23000') {
                if (str_contains($e->getMessage(), 'products_slug_unique')) {
                    $message = 'This Product Slug is already taken. Please choose a different one.';

                    return response()->json([
                        'success' => false,
                        'message' => 'Please correct the errors below and try again.',
                        'errors' => [
                            'slug' => [$message],
                            'seo_url_slug' => [$message],
                        ]
                    ], 422);
                }

                if (str_contains($e->getMessage(), 'product_variants_sku_unique')) {
                    $variants = $request->input('variants', []);
                    $errorMessage = 'This Variant SKU is already taken. Please choose a different one.';
                    $errors = [];

                    if (is_array($variants)) {
                        foreach ($variants as $index => $variant) {
                            if (!empty($variant['sku'])) {
                                $errors["variants[$index][sku]"] = [$errorMessage];
                                $errors["variants.$index.sku"] = [$errorMessage];
                            }
                        }
                    }

                    if (empty($errors)) {
                        $errors = [
                            'variants' => ['A variant SKU in this request is already taken. Please ensure each SKU is unique.']
                        ];
                    }

                    return response()->json([
                        'success' => false,
                        'message' => 'Please correct the errors below and try again.',
                        'errors' => $errors
                    ], 422);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Error creating product: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load([
            'images',
            'category.parent',
            'categories.parent',
            'variants' => function($query) {
                $query->orderBy('sort_order');
            },
            'variants.images',
            'brands',
            'unit'
        ]);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $otherBrand = $this->ensureOtherBrand();
        $brands = Brand::active()->ordered()->get();
        $brands = collect([$otherBrand])->merge(
            $brands->reject(fn ($brand) => $brand->id === $otherBrand->id)
        )->values();
        $categories = Category::active()->ordered()->get();
        $attributes = ProductAttribute::visible()->ordered()->get();
        $units = Unit::active()->ordered()->get();
        
        $product->load(['images', 'category.parent', 'variants', 'brands', 'unit', 'defaultWarehouse']);

        $otherBrandId = $otherBrand->id;

        return view('admin.products.edit', compact(
            'brands', 
            'categories', 
            'attributes', 
            'units', 
            'product', 
            'otherBrandId'
        ));
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product)
    {
        $this->prepareProductRequestDefaults($request, $product->type);
        $primaryImageIndex = $request->input('primary_image_index');

        Log::info('ProductController@update - Incoming request data', [
            'product_id' => $product->id,
            'payload' => $request->except(['images', 'variants', 'remove_images']),
            'has_images' => $request->hasFile('images'),
            'has_variants' => $request->has('variants'),
            'remove_images' => $request->input('remove_images', []),
        ]);
        
        // Calculate the slug that will be saved (for validation)
        $slugToValidate = $request->filled('seo_url_slug')
            ? Str::slug($request->seo_url_slug)
            : Str::slug($request->name);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'type' => 'required|in:simple,variable,digital,service,bundle,subscription',
            'brand_ids' => 'nullable|array',
            'brand_ids.*' => 'exists:brands,id',
            'seo_url_slug' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($slugToValidate, $product) {
                    // Skip validation if slug is empty
                    if (empty($slugToValidate)) {
                        return;
                    }
                    
                    // Check uniqueness using the slugified value, ignoring current product
                    $exists = Product::where('slug', $slugToValidate)
                        ->where('id', '!=', $product->id)
                        ->exists();
                        
                    if ($exists) {
                        $fail('The seo url slug has already been taken.');
                    }
                },
            ],
            'requires_shipping' => 'nullable|in:on,1,true',
            'free_shipping' => 'nullable|in:on,1,true',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'tags' => 'nullable|string|max:1000',
            'status' => 'required|in:published,hidden',
            'visibility' => 'nullable|in:published,hidden',
            'published_at' => 'nullable|date',
            'featured' => 'nullable|in:on,1,true',
            'gst_type' => 'nullable|in:0,1',
            'gst_percentage' => 'nullable|in:0,3,5,12,18,28',
            'category_id' => 'nullable|exists:categories,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'image_alt_texts' => 'nullable|array',
            'image_alt_texts.*' => 'nullable|string|max:255',
            'image_sort_orders' => 'nullable|array',
            'image_sort_orders.*' => 'nullable|integer|min:0',
            'primary_image_index' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== null && $value !== '') {
                    // Accept integer or string in format "new_X" where X is a number
                    if (!is_numeric($value) && !preg_match('/^new_\d+$/', $value)) {
                        $fail('The primary image index must be an integer or in the format "new_X".');
                    }
                }
            }],
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'integer|exists:product_images,id',
            'variants' => 'nullable|array',
            'variants.*.sku' => 'required|string|max:255',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0',
            'variants.*.cost_price' => 'nullable|numeric|min:0',
            'variants.*.low_stock_threshold' => 'nullable|integer|min:0',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.length' => 'nullable|numeric|min:0',
            'variants.*.width' => 'nullable|numeric|min:0',
            'variants.*.height' => 'nullable|numeric|min:0',
            'variants.*.is_active' => 'nullable|boolean',
            'variants.*.images' => 'nullable|array',
            'variants.*.images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $validator->after(function ($validator) use ($request, $product) {
            // Ensure at least one variant is provided
            $variants = $request->input('variants', []);
            if (empty($variants) || !is_array($variants) || count(array_filter($variants)) === 0) {
                // Only enforce if no existing variants exist
                if ($product->variants()->count() === 0) {
                    $validator->errors()->add('variants', 'At least one variant is required. Please add a variant in the Variants section.');
                }
            }
        });

        if ($validator->fails()) {
            // Custom error messages for better user experience
            $customErrors = [];
            $errors = $validator->errors();

            $fieldMappings = [
                'name' => 'Product Name',
                'sku' => 'SKU',
                'price' => 'Regular Price',
                'brand_ids' => 'Brand Selection',
                'brand_ids.*' => 'Brand Selection',
                'stock_quantity' => 'Stock Quantity',
                'stock_status' => 'Stock Status',
                'type' => 'Product Type',
                'status' => 'Product Status'
            ];

            foreach ($errors->messages() as $field => $messages) {
                $fieldName = $fieldMappings[$field] ?? Str::of($field)
                    ->replace(['_', '.'], ' ')
                    ->title()
                    ->value();

                $customErrors[$field] = array_map(function ($message) use ($fieldName) {
                    if (str_contains($message, 'required')) {
                        return "The {$fieldName} field is required.";
                    }
                    if (str_contains($message, 'unique')) {
                        return "This {$fieldName} is already taken. Please choose a different one.";
                    }
                    if (str_contains($message, 'numeric')) {
                        return "The {$fieldName} must be a valid number.";
                    }
                    if (str_contains($message, 'min:')) {
                        return "The {$fieldName} must be at least 0.";
                    }
                    if (str_contains($message, 'array')) {
                        return "Please select at least one {$fieldName}.";
                    }
                    return $message;
                }, $messages);
            }

            return response()->json([
                'success' => false,
                'message' => 'Please correct the errors below and try again.',
                'errors' => $customErrors
            ], 422);
        }

        DB::beginTransaction();

        try {
            $otherBrand = $this->ensureOtherBrand();
            $brandIds = $request->brand_ids ?? [];
            if (empty($brandIds)) {
                $brandIds = [$otherBrand->id];
            }

            $slug = $request->filled('seo_url_slug')
                ? Str::slug($request->seo_url_slug)
                : Str::slug($request->name);
            
            $product->update([
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'short_description' => $request->short_description,
                'requires_shipping' => $request->has('requires_shipping'),
                'free_shipping' => $request->has('free_shipping'),
                'gst_type' => $request->has('gst_type') ? ($request->gst_type == '1' || $request->gst_type == 1) : ($product->gst_type ?? true),
                'gst_percentage' => $request->has('gst_percentage') ? $request->gst_percentage : $product->gst_percentage,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'meta_keywords' => $request->meta_keywords,
                'tags' => $request->tags,
                'status' => $request->visibility ?? $request->status,
                'published_at' => $request->published_at,
                'featured' => $request->has('featured'),
                'category_id' => $request->category_ids && count($request->category_ids) > 0 ? $request->category_ids[0] : $request->category_id,
                // default_warehouse_id not updated from form - warehouse management moved to Inventory module
            ]);

            // Handle categories
            if ($request->category_ids && count($request->category_ids) > 0) {
                $categoryData = [];
                foreach ($request->category_ids as $index => $categoryId) {
                    $categoryData[$categoryId] = [
                        'is_primary' => $index === 0, // First category is primary
                    ];
                }
                $product->categories()->sync($categoryData);
            } elseif ($request->category_id) {
                // Fallback: handle single category_id for backward compatibility
                $product->categories()->sync([
                    $request->category_id => [
                        'is_primary' => true,
                    ],
                ]);
            }

            // Handle brands
            if (!empty($brandIds)) {
                $brandData = [];
                foreach ($brandIds as $index => $brandId) {
                    $brandData[$brandId] = [
                        'is_primary' => $index === 0, // First brand is primary
                        'sort_order' => $index,
                    ];
                }
                $product->brands()->sync($brandData);
            } else {
                $product->brands()->sync([
                    $brandIds[0] => [
                        'is_primary' => true,
                        'sort_order' => 0,
                    ],
                ]);
            }

            // Handle image removal
            if ($request->remove_images) {
                $this->removeImages($product, $request->remove_images);
            }

            // Handle new image uploads
            if ($request->hasFile('images')) {
                $this->handleImageUploads($product, $request);
            } else {
                $this->applyPrimaryImageSelection($product, $primaryImageIndex);
            }

            // Always use variant payload - auto-generate default variant if none provided
            $variantsPayload = $this->filterProvidedVariants($request->input('variants', []));
            $variantsPayload = $this->mergeVariantFileUploads($variantsPayload, $request->file('variants', []));

            Log::info('ProductController@update - Filtered variant payload', [
                'product_id' => $product->id,
                'variants' => $this->sanitizeVariantsForLog($variantsPayload),
            ]);

            // If no variants provided and no existing variants, create a default variant
            if (empty($variantsPayload) && $product->variants()->count() === 0) {
                $variantsPayload = [$this->buildDefaultVariantPayload($request, $product)];
            }

            if (!empty($variantsPayload)) {
                $this->synchronizeVariants($product, $variantsPayload);
            }

            // Handle static attributes
            $this->synchronizeStaticAttributes($product, $request);

            $product->load(['variants.images', 'category.parent']);
            Log::info('ProductController@update - Persisted product snapshot', [
                'product_id' => $product->id,
                'variants' => $product->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'price' => $variant->price,
                        'sale_price' => $variant->sale_price,
                        'discount_type' => $variant->discount_type,
                        'discount_value' => $variant->discount_value,
                        'discount_active' => $variant->discount_active,
                        'measurements' => $variant->measurements,
                        'highlights_details' => $variant->highlights_details,
                        'images' => $variant->images->pluck('image_path'),
                    ];
                }),
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'path' => $product->category_path,
                ] : null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'product' => $product->load(['images', 'category.parent', 'variants'])
            ]);

        } catch (QueryException $e) {
            DB::rollBack();
            if ($e->getCode() === '23000') {
                if (str_contains($e->getMessage(), 'products_slug_unique')) {
                    $message = 'This Product Slug is already taken. Please choose a different one.';

                    return response()->json([
                        'success' => false,
                        'message' => 'Please correct the errors below and try again.',
                        'errors' => [
                            'slug' => [$message],
                            'seo_url_slug' => [$message],
                        ]
                    ], 422);
                }

                if (str_contains($e->getMessage(), 'product_variants_sku_unique')) {
                    $variants = $request->input('variants', []);
                    $errorMessage = 'This Variant SKU is already taken. Please choose a different one.';
                    $errors = [];

                    if (is_array($variants)) {
                        foreach ($variants as $index => $variant) {
                            if (!empty($variant['sku'])) {
                                $errors["variants[$index][sku]"] = [$errorMessage];
                                $errors["variants.$index.sku"] = [$errorMessage];
                            }
                        }
                    }

                    if (empty($errors)) {
                        $errors = [
                            'variants' => ['A variant SKU in this request is already taken. Please ensure each SKU is unique.']
                        ];
                    }

                    return response()->json([
                        'success' => false,
                        'message' => 'Please correct the errors below and try again.',
                        'errors' => $errors
                    ], 422);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Error updating product: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product from storage.
     */
    /**
     * Bulk delete products (POST AJAX JSON).
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id'
        ]);

        $ids = $request->ids;
        $deleted = 0;
        $failed = 0;

        foreach ($ids as $id) {
            try {
                $product = Product::find($id);
                if ($product) {
                    // Delete associated images
                    foreach ($product->images as $image) {
                        if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                            Storage::disk('public')->delete($image->image_path);
                        }
                    }
                    $product->delete();
                    $deleted++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
            }
        }

        $message = "Deleted {$deleted} product(s)";
        if ($failed > 0) {
            $message .= ". {$failed} product(s) could not be deleted.";
        }

        return response()->json([
            'success' => $deleted > 0,
            'message' => $message,
            'deleted' => $deleted,
            'failed' => $failed
        ]);
    }

    public function destroy(Product $product)
    {
        try {
            // Delete associated images
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products data for DataTable.
     */
    public function getData(Request $request)
    {
        // Optimize: Only load necessary relationships with specific columns
        // Note: Soft-deleted products are automatically excluded (Product uses SoftDeletes)
        $query = Product::with([
            'primaryImage:id,product_id,image_path,is_primary',
            'category:id,name,parent_id',
            'category.parent:id,name',
            'categories' => function($query) {
                $query->select('categories.id', 'categories.name', 'categories.parent_id')
                      ->with(['parent' => function($q) {
                          $q->select('id', 'name', 'parent_id');
                      }]);
            },
            'brands:id,name',
            'variants' => function($q) {
                // Variants are automatically from non-deleted products since Product uses SoftDeletes
                $q->select('id', 'product_id', 'sku', 'price', 'sale_price', 'stock_quantity', 'stock_status', 'manage_stock', 'measurements', 'discount_active', 'discount_type', 'discount_value')
                  ->with(['images' => function($imgQ) {
                      $imgQ->select('id', 'product_variant_id', 'image_path', 'is_primary', 'sort_order')
                           ->orderBy('sort_order')
                           ->orderBy('id');
                  }]);
            },
            'unit:id,symbol,name'
        ]);

        // Search
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('variants', function($variantQuery) use ($search) {
                      $variantQuery->where('sku', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by brand
        if ($request->has('brand_id') && $request->brand_id) {
            $query->whereHas('brands', function($q) use ($request) {
                $q->where('brand_id', $request->brand_id);
            });
        }

        // Filter by category (includes all descendants for unlimited nesting support)
        if ($request->has('category_id') && $request->category_id) {
            $category = Category::find($request->category_id);
            if ($category) {
                $categoryIds = $category->getDescendantIds();
                $categoryIds[] = $category->id; // Include the category itself
                $query->whereIn('category_id', $categoryIds);
            } else {
                $query->where('category_id', $request->category_id);
            }
        }

        // Optimize: Use select to only get needed columns
        $query->select('products.*');
        
        $totalRecords = $query->count();
        $products = $query->orderBy('created_at', 'desc')
                         ->skip($request->start ?? 0)
                         ->take($request->length ?? 25)
                         ->get();

        $data = $products->map(function($product) {
            $variants = $product->variants ?? collect();
            $variantCount = $variants->count();

            // Calculate current price for each variant (considering sale_price)
            $priceValues = $variants->map(function($variant) {
                // Use sale_price if on sale, otherwise use price
                $currentPrice = null;
                
                if ($variant->isOnSale() && $variant->sale_price) {
                    $currentPrice = (float) $variant->sale_price;
                } elseif ($variant->price !== null && $variant->price !== '') {
                    $currentPrice = (float) $variant->price;
                }
                
                return $currentPrice;
            })->filter(fn($value) => $value !== null);

            $minPrice = $priceValues->isNotEmpty() ? $priceValues->min() : null;
            $maxPrice = $priceValues->isNotEmpty() ? $priceValues->max() : null;

            $priceRangeDisplay = '—';
            if (!is_null($minPrice)) {
                if (!is_null($maxPrice) && $minPrice !== $maxPrice) {
                    $priceRangeDisplay = sprintf('₹%s - ₹%s', number_format($minPrice, 2), number_format($maxPrice, 2));
                } else {
                    $priceRangeDisplay = sprintf('₹%s', number_format($minPrice, 2));
                }
            }

            $unitSymbols = $variants->flatMap(function ($variant) {
                $measurements = $variant->measurements;
                if (is_string($measurements)) {
                    $decoded = json_decode($measurements, true);
                    $measurements = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
                }

                if (!is_array($measurements)) {
                    return collect();
                }

                return collect($measurements)->map(function ($measurement) {
                    if (!is_array($measurement)) {
                        return null;
                    }
                    return $measurement['unit_symbol']
                        ?? $measurement['unit_name']
                        ?? $measurement['unit']
                        ?? null;
                });
            })->filter()->unique()->values();

            $unitDisplay = $unitSymbols->isNotEmpty()
                ? $unitSymbols->implode(', ')
                : ($product->unit_display ?: ($product->unit->symbol ?? null));

            $stockTotal = $variants->sum(function ($variant) {
                return (int) ($variant->stock_quantity ?? 0);
            });

            // Stock is always calculated from variants
            $stockStatusCode = $stockTotal > 0 ? 'in_stock' : 'out_of_stock';
            $stockStatusLabel = $stockTotal > 0 ? 'In Stock' : 'Out of Stock';

            $tags = $product->tags
                ? array_values(array_filter(array_map('trim', explode(',', $product->tags))))
                : [];

            return [
                'id' => $product->id,
                'name' => $product->name,
                'brands' => $product->brands->map(function($brand) {
                    return [
                        'id' => $brand->id,
                        'name' => $brand->name,
                        'is_primary' => $brand->pivot->is_primary
                    ];
                }),
                'sku' => $variants->isNotEmpty() ? $variants->pluck('sku')->filter()->implode(', ') : '—',
                'variant_count' => $variantCount,
                'has_variants' => $variantCount > 0,
                'variant_price_min' => $minPrice,
                'variant_price_max' => $maxPrice,
                'variant_price_range' => $priceRangeDisplay,
                'variant_units' => $unitSymbols,
                'variant_units_display' => $unitDisplay ?: '—',
                'variant_stock_total' => $stockTotal,
                'variant_stock_status' => $stockStatusCode,
                'variant_stock_label' => $stockStatusLabel,
                'stock_quantity' => $stockTotal,
                'variants' => $variants->map(function($variant) use ($product) {
                    // Get variant image or fallback to product image
                    $variantImage = $variant->images->first();
                    $imageUrl = $variantImage 
                        ? asset('storage/' . $variantImage->image_path)
                        : ($product->image_url ?? asset('assets/images/placeholder.jpg'));
                    
                    return [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'sku' => $variant->sku,
                        'stock_quantity' => (int) ($variant->stock_quantity ?? 0),
                        'stock_status' => $variant->stock_status,
                        'manage_stock' => $variant->manage_stock,
                        'low_stock_threshold' => (int) ($variant->low_stock_threshold ?? 0),
                        'image_url' => $imageUrl,
                    ];
                }),
                'status' => ucfirst($product->status),
                'featured' => $product->featured ? 'Yes' : 'No',
                'image_url' => $product->image_url,
                'thumbnail_url' => $product->primaryImage
                    ? asset('storage/' . $product->primaryImage->image_path)
                    : ($product->images->first()
                        ? asset('storage/' . $product->images->first()->image_path)
                        : asset('assets/images/placeholder.jpg')),
                'tags' => $tags,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'path' => $product->category_path,
                ] : null,
                'categories' => $product->categories->map(function($category) {
                    // Build category path by traversing parent chain
                    $path = [];
                    $current = $category;
                    $maxDepth = 20;
                    $depth = 0;
                    
                    // Traverse up the parent chain to build full path
                    while ($current && $depth < $maxDepth) {
                        array_unshift($path, $current->name);
                        
                        // Load parent if not already loaded
                        if ($current->parent_id) {
                            if (!$current->relationLoaded('parent')) {
                                $current->load('parent');
                            }
                            $current = $current->parent;
                        } else {
                            $current = null;
                        }
                        $depth++;
                    }
                    
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'path' => implode(' > ', $path),
                        'is_primary' => $category->pivot->is_primary ?? false
                    ];
                }),
                'created_at' => $product->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ]);
    }

    /**
     * Handle image uploads for a product.
     */
    private function handleImageUploads(Product $product, Request $request)
    {
        $images = $request->file('images');
        $altTexts = $request->input('image_alt_texts', []);
        $sortOrders = $request->input('image_sort_orders', []);
        $primaryImageIndex = $request->input('primary_image_index');

        $existingMaxSortOrder = $product->images()->max('sort_order');
        $existingCount = $product->images()->count();
        $baseSortOrder = is_null($existingMaxSortOrder)
            ? $existingCount
            : (int) $existingMaxSortOrder + 1;

        $newlyCreatedImageIds = [];
        foreach ($images as $index => $image) {
            $imagePath = $image->store('products', 'public');
            
            $imageData = [
                'product_id' => $product->id,
                'product_variant_id' => null,
                'image_path' => $imagePath,
                'alt_text' => $altTexts[$index] ?? null,
                'sort_order' => $sortOrders[$index] ?? ($baseSortOrder + $index),
                'is_primary' => false,
            ];

            $createdImage = ProductImage::create($imageData);
            $newlyCreatedImageIds[] = $createdImage->id;
        }

        $this->applyPrimaryImageSelection($product, $primaryImageIndex, $newlyCreatedImageIds);
    }

    private function applyPrimaryImageSelection(Product $product, $primaryImageIndex, array $newlyCreatedImageIds = []): void
    {
        if ($primaryImageIndex === null || $primaryImageIndex === '') {
            return;
        }

        $targetId = null;

        // Handle "new_X" format for newly uploaded images
        if (is_string($primaryImageIndex) && preg_match('/^new_(\d+)$/', $primaryImageIndex, $matches)) {
            $newIndex = (int) $matches[1];
            if (isset($newlyCreatedImageIds[$newIndex])) {
                $targetId = $newlyCreatedImageIds[$newIndex];
            }
        } else {
            // Handle integer index for existing images
            $index = (int) $primaryImageIndex;
            $product->load('images');
            $images = $product->images->values();

            if (isset($images[$index])) {
                $targetId = $images[$index]->id;
            }
        }

        if ($targetId === null) {
            return;
        }

        $product->images()->where('is_primary', true)->update(['is_primary' => false]);
        $product->images()->where('id', $targetId)->update(['is_primary' => true]);
    }

    /**
     * Remove specified images.
     */
    private function removeImages(Product $product, array $imageIds)
    {
        $images = $product->images()->whereIn('id', $imageIds)->get();
        
        foreach ($images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }
    }

    /**
     * Get all product attributes (both variant and static).
     * Returns all attributes regardless of visibility - filtering is done on frontend.
     */
    public function getAttributes()
    {
        $attributes = ProductAttribute::ordered()
            ->with('values')
            ->get();

        // Log for debugging
        Log::info('getAttributes - Returning all attributes', [
            'total' => $attributes->count(),
            'attributes' => $attributes->map(function($attr) {
                return [
                    'id' => $attr->id,
                    'name' => $attr->name,
                    'is_variation' => $attr->is_variation,
                    'is_variation_type' => gettype($attr->is_variation),
                    'is_visible' => $attr->is_visible,
                ];
            })->toArray()
        ]);

        return response()->json([
            'success' => true,
            'attributes' => $attributes
        ]);
    }

    /**
     * Get attributes for a specific category (AJAX JSON).
     * Returns variant and static attributes assigned to the category and its ancestors.
     */
    public function getAttributesByCategory(Request $request)
    {
        try {
            $categoryId = $request->get('category_id');
            
            if (!$categoryId) {
                return response()->json([
                    'success' => true,
                    'variant_attributes' => [],
                    'static_attributes' => []
                ]);
            }
            
            // Handle array input (take first category if multiple provided)
            if (is_array($categoryId)) {
                $categoryId = !empty($categoryId) ? $categoryId[0] : null;
            }
            
            // Ensure categoryId is an integer
            $categoryId = (int) $categoryId;
            
            if (!$categoryId) {
                return response()->json([
                    'success' => true,
                    'variant_attributes' => [],
                    'static_attributes' => []
                ]);
            }
            
            // Use where()->first() to ensure we get a single model instance, not a collection
            $category = Category::where('id', $categoryId)->first();
            
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }
            
            // Ensure we have a Category model instance, not a collection
            if (!$category instanceof Category) {
                Log::error('Category is not an instance of Category model', [
                    'category_id' => $categoryId,
                    'category_type' => get_class($category)
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid category data'
                ], 400);
            }
            
            // Verify the method exists before calling it
            if (!method_exists($category, 'getAllProductAttributes')) {
                Log::error('getAllProductAttributes method not found on Category', [
                    'category_id' => $categoryId,
                    'category_class' => get_class($category)
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Category model does not have getAllProductAttributes method'
                ], 500);
            }
            
            // Get all attributes for this category (including inherited)
            // Values are already eager loaded in getAllProductAttributes()
            $allAttributes = $category->getAllProductAttributes();
            
            // Log for debugging
            Log::info('getAttributesByCategory - Category ID: ' . $categoryId, [
                'category_name' => $category->name,
                'total_attributes' => $allAttributes->count(),
                'attributes' => $allAttributes->map(function($attr) {
                    return [
                        'id' => $attr->id,
                        'name' => $attr->name,
                        'is_variation' => $attr->is_variation,
                        'is_variation_type' => gettype($attr->is_variation)
                    ];
                })->toArray()
            ]);
            
            // Ensure we got a collection back
            if (!$allAttributes instanceof \Illuminate\Support\Collection && !$allAttributes instanceof \Illuminate\Database\Eloquent\Collection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid attributes data returned'
                ], 500);
            }
            
            // Show ALL visible attributes in variant attributes (not just is_variation = true)
            // This allows users to use any attribute for variants
            $variantAttributes = $allAttributes->filter(function($attr) {
                $isVisible = $attr->is_visible !== false && $attr->is_visible !== 0 && $attr->is_visible !== '0';
                return $isVisible; // Show all visible attributes, regardless of is_variation flag
            })->map(function($attr) {
                return [
                    'id' => $attr->id,
                    'name' => $attr->name ?? '',
                    'slug' => $attr->slug ?? '',
                    'type' => $attr->type ?? 'text',
                    'description' => $attr->description ?? '',
                    'is_required' => $attr->is_required ?? false,
                    'is_visible' => $attr->is_visible ?? true,
                    'sort_order' => $attr->sort_order ?? 0,
                    'values' => ($attr->values ?? collect())->map(function($value) {
                        return [
                            'id' => $value->id ?? null,
                            'value' => $value->value ?? '',
                            'color_code' => $value->color_code ?? null,
                            'image_path' => $value->image_path ?? null,
                            'sort_order' => $value->sort_order ?? 0,
                        ];
                    })->values()
                ];
            })->values();
            
            $staticAttributes = $allAttributes->filter(function($attr) {
                $isStatic = $attr->is_variation === false || $attr->is_variation === 0 || $attr->is_variation === '0';
                $isVisible = $attr->is_visible !== false && $attr->is_visible !== 0 && $attr->is_visible !== '0';
                return $isStatic && $isVisible;
            })->map(function($attr) {
                return [
                    'id' => $attr->id,
                    'name' => $attr->name ?? '',
                    'slug' => $attr->slug ?? '',
                    'type' => $attr->type ?? 'text',
                    'description' => $attr->description ?? '',
                    'is_required' => $attr->is_required ?? false,
                    'is_visible' => $attr->is_visible ?? true,
                    'sort_order' => $attr->sort_order ?? 0,
                    'values' => ($attr->values ?? collect())->map(function($value) {
                        return [
                            'id' => $value->id ?? null,
                            'value' => $value->value ?? '',
                            'color_code' => $value->color_code ?? null,
                            'image_path' => $value->image_path ?? null,
                            'sort_order' => $value->sort_order ?? 0,
                        ];
                    })->values()
                ];
            })->values();
            
            // Log filtered results for debugging
            Log::info('getAttributesByCategory - Filtered Results', [
                'category_id' => $categoryId,
                'total_attributes' => $allAttributes->count(),
                'variant_attributes_count' => $variantAttributes->count(),
                'static_attributes_count' => $staticAttributes->count(),
                'variant_attribute_ids' => $variantAttributes->pluck('id')->toArray(),
                'static_attribute_ids' => $staticAttributes->pluck('id')->toArray(),
            ]);
            
            return response()->json([
                'success' => true,
                'variant_attributes' => $variantAttributes,
                'static_attributes' => $staticAttributes,
                'debug' => [
                    'category_id' => $categoryId,
                    'total_attributes_found' => $allAttributes->count(),
                    'variant_attributes_count' => $variantAttributes->count(),
                    'static_attributes_count' => $staticAttributes->count(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getAttributesByCategory', [
                'category_id' => $request->get('category_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading attributes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all categories (AJAX JSON).
     * Categories are now independent of brands, so this returns all active categories.
     */
    public function getCategoriesByBrand(Request $request)
    {
        Log::info('=== getCategoriesByBrand DEBUG ===');
        Log::info('Request data:', $request->all());
        
        // Categories are now independent of brands, return all active categories
        $allCategories = Category::active()
            ->with(['parent'])
            ->ordered()
            ->get();

        $formattedCategories = $allCategories->map(function($category) {
            // Build category path for display
            $path = [];
            $current = $category;
            $maxDepth = 20;
            $depth = 0;
            while ($current && $depth < $maxDepth) {
                array_unshift($path, $current->name);
                $current = $current->parent;
                $depth++;
            }
            $pathString = implode(' > ', $path);
            
            return [
                'id' => $category->id,
                'name' => $category->name,
                'path' => $pathString,
                'parent_id' => $category->parent_id,
                'parent_name' => $category->parent ? $category->parent->name : null,
                'has_children' => Category::where('parent_id', $category->id)->exists(),
                'sort_order' => $category->sort_order,
            ];
        });

        return response()->json([
            'success' => true,
            'categories' => $formattedCategories,
        ]);
    }

    /**
     * Get units by type (AJAX)
     */
    public function getUnitsByType(Request $request)
    {
        $type = $request->get('type');
        
        if (!$type) {
            return response()->json(['success' => false, 'message' => 'Unit type required']);
        }

        $units = Unit::where('type', $type)
            ->active()
            ->ordered()
            ->get(['id', 'name', 'symbol', 'abbreviation']);

        return response()->json([
            'success' => true,
            'units' => $units
        ]);
    }

    /**
     * Generate product variants based on attributes.
     */
    public function generateVariants(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'attributes' => 'required|array',
            'attributes.*.attribute_id' => 'required|exists:product_attributes,id',
            'attributes.*.values' => 'required|array',
            'attributes.*.values.*' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Generate all possible combinations
            $combinations = $this->generateAttributeCombinations($request->input('attributes', []));
            
            // Create variants
            foreach ($combinations as $index => $combination) {
                $variantName = $this->generateVariantName($combination);
                $variantSku = $product->sku . '-' . strtoupper(Str::slug($variantName, ''));

                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $variantSku,
                    'name' => $variantName,
                    'price' => 0,
                    'sale_price' => null,
                    'cost_price' => null,
                    'stock_quantity' => 0,
                    'manage_stock' => true,
                    'stock_status' => 'in_stock',
                    'is_active' => true,
                    'discount_type' => null,
                    'discount_value' => null,
                    'discount_active' => false,
                    'attributes' => $combination,
                    'weight' => null,
                    'length' => null,
                    'width' => null,
                    'height' => null,
                    'diameter' => null,
                    'sort_order' => $index,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Variants generated successfully',
                'variants_count' => count($combinations)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating variants: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate all possible attribute combinations.
     */
    private function generateAttributeCombinations(array $attributes)
    {
        $combinations = [[]];
        
        foreach ($attributes as $attribute) {
            $newCombinations = [];
            foreach ($combinations as $combination) {
                foreach ($attribute['values'] as $value) {
                    $newCombination = $combination;
                    $newCombination[$attribute['attribute_id']] = $value;
                    $newCombinations[] = $newCombination;
                }
            }
            $combinations = $newCombinations;
        }
        
        return $combinations;
    }

    /**
     * Generate variant name from attributes.
     */
    private function generateVariantName(array $attributes)
    {
        $parts = [];
        foreach ($attributes as $attributeId => $value) {
            $attribute = ProductAttribute::find($attributeId);
            if ($attribute) {
                $parts[] = $value;
            }
        }
        return implode(' - ', $parts);
    }

    /**
     * Sync product variants with the provided payload.
     */
    private function synchronizeVariants(Product $product, array $variants): void
    {
        $existingVariants = $product->variants()->with('images')->get()->keyBy('id');
        $processedIds = [];

        foreach ($variants as $index => $variantData) {
            $variantId = isset($variantData['id']) && $variantData['id'] !== ''
                ? (int) $variantData['id']
                : null;

            if ($variantId && $existingVariants->has($variantId)) {
                $variant = $this->saveVariant($product, $variantData, $index, $existingVariants->get($variantId));
                $processedIds[] = $variant->id;
            } else {
                $variant = $this->saveVariant($product, $variantData, $index);
                $processedIds[] = $variant->id;
            }
        }

        $idsToDelete = $existingVariants->keys()->diff($processedIds);
        foreach ($idsToDelete as $deleteId) {
            $this->deleteVariantWithAssets($existingVariants->get($deleteId));
        }
    }

    private function saveVariant(Product $product, array $variantData, int $index, ?ProductVariant $variant = null): ProductVariant
    {
        $attributes = $this->normalizeVariantAttributes($variantData['attributes'] ?? ($variant->attributes ?? []));

        $variantName = isset($variantData['name']) && trim((string) $variantData['name']) !== ''
            ? trim($variantData['name'])
            : ($variant ? $variant->name : null);

        if (!$variantName) {
            $variantName = !empty($attributes)
                ? $this->generateVariantName($attributes)
                : 'Variant ' . ($index + 1);
        }

        $providedSku = $variantData['sku'] ?? null;
        $variantSku = ($providedSku && trim((string) $providedSku) !== '')
            ? trim($providedSku)
            : ($variant ? $variant->sku : $product->sku . '-V' . ($index + 1));

        $mrp = $this->normalizeNumeric($variantData['price'] ?? ($variant->price ?? null), 0);
        $rawSalePrice = $this->normalizeNumeric($variantData['sale_price'] ?? ($variant->sale_price ?? null));
        $discountType = $variantData['discount_type'] ?? ($variant->discount_type ?? null);
        $discountValue = $this->normalizeNumeric($variantData['discount_value'] ?? ($variant->discount_value ?? null));
        $discountActive = $this->normalizeBoolean($variantData['discount_active'] ?? ($variant->discount_active ?? false), false);

        $salePrice = $rawSalePrice;
        if ($discountActive && $discountType && $discountValue !== null) {
            if ($discountType === 'percentage') {
                $salePrice = max($mrp - ($mrp * ($discountValue / 100)), 0);
            } elseif ($discountType === 'amount') {
                $salePrice = max($mrp - $discountValue, 0);
            }
        }

        $normalizedMeasurements = $this->normalizeVariantMeasurements($variantData['measurements'] ?? ($variant->measurements ?? []));
        $legacyMeasurements = $this->mapMeasurementsToLegacyDimensions($normalizedMeasurements);

        $weightValue = array_key_exists('weight', $legacyMeasurements)
            ? $legacyMeasurements['weight']
            : $this->normalizeNumeric($variantData['weight'] ?? ($variant->weight ?? null));
        $lengthValue = array_key_exists('length', $legacyMeasurements)
            ? $legacyMeasurements['length']
            : $this->normalizeNumeric($variantData['length'] ?? ($variant->length ?? null));
        $widthValue = array_key_exists('width', $legacyMeasurements)
            ? $legacyMeasurements['width']
            : $this->normalizeNumeric($variantData['width'] ?? ($variant->width ?? null));
        $heightValue = array_key_exists('height', $legacyMeasurements)
            ? $legacyMeasurements['height']
            : $this->normalizeNumeric($variantData['height'] ?? ($variant->height ?? null));
        $diameterValue = array_key_exists('diameter', $legacyMeasurements)
            ? $legacyMeasurements['diameter']
            : $this->normalizeNumeric($variantData['diameter'] ?? ($variant->diameter ?? null));

        $uploadedImages = $variantData['images'] ?? [];
        if ($uploadedImages instanceof UploadedFile) {
            $uploadedImages = [$uploadedImages];
        }
        if (!is_array($uploadedImages)) {
            $uploadedImages = [];
        }

        if (!$variant) {
            $variant = new ProductVariant();
            $variant->product_id = $product->id;
        }

        $variant->fill([
            'sku' => $variantSku,
            'barcode' => isset($variantData['barcode']) && trim((string) $variantData['barcode']) !== ''
                ? trim($variantData['barcode'])
                : ($variant->barcode ?? null),
            'name' => $variantName,
            'price' => $mrp,
            'sale_price' => $salePrice,
            'cost_price' => $this->normalizeNumeric($variantData['cost_price'] ?? ($variant->cost_price ?? null)),
            'stock_quantity' => (int) ($variantData['stock_quantity'] ?? ($variant->stock_quantity ?? 0)),
            'manage_stock' => $this->normalizeBoolean($variantData['manage_stock'] ?? ($variant->manage_stock ?? true), true),
            'stock_status' => (isset($variantData['stock_status']) && trim((string) $variantData['stock_status']) !== '')
                ? trim($variantData['stock_status'])
                : ($variant->stock_status ?? 'in_stock'),
            'low_stock_threshold' => (int) ($variantData['low_stock_threshold'] ?? ($variant->low_stock_threshold ?? 0)),
            'is_active' => $this->normalizeBoolean($variantData['is_active'] ?? ($variant->is_active ?? true), true),
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'discount_active' => $discountActive,
            'attributes' => $attributes,
            'measurements' => $normalizedMeasurements,
            'weight' => $weightValue,
            'length' => $lengthValue,
            'width' => $widthValue,
            'height' => $heightValue,
            'diameter' => $diameterValue,
            'highlights_details' => $this->normalizeHighlightsDetails($variantData['highlights_details'] ?? null),
            'sort_order' => $index,
        ]);

        $variant->product_id = $product->id;
        $variant->save();

        if (!empty($uploadedImages)) {
            $this->handleVariantImages($product, $variant, $uploadedImages);
        }

        return $variant;
    }

    private function deleteVariantWithAssets(?ProductVariant $variant): void
    {
        if (!$variant) {
            return;
        }

        foreach ($variant->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        if ($variant->image) {
            Storage::disk('public')->delete($variant->image);
        }

        $variant->delete();
    }

    private function sanitizeVariantsForLog($variants): array
    {
        if (!is_array($variants)) {
            return [];
        }

        return array_map(function ($variant) {
            if (!is_array($variant)) {
                return [];
            }

            return [
                'id' => $variant['id'] ?? null,
                'sku' => $variant['sku'] ?? null,
                'price' => $variant['price'] ?? null,
                'sale_price' => $variant['sale_price'] ?? null,
                'discount_type' => $variant['discount_type'] ?? null,
                'discount_value' => $variant['discount_value'] ?? null,
                'discount_active' => $variant['discount_active'] ?? null,
                'attributes' => $this->normalizeVariantAttributes($variant['attributes'] ?? []),
                'measurements' => $this->normalizeVariantMeasurements($variant['measurements'] ?? []),
                'has_images' => !empty($variant['images']),
            ];
        }, $variants);
    }

    private function buildDefaultVariantPayload(Request $request, Product $product): array
    {
        // Auto-generate SKU for default variant
        $variantSku = strtoupper(Str::slug($product->name ?? $request->name, '')) . '-DEFAULT';
        $baseSku = $variantSku;
        $counter = 1;
        while (ProductVariant::where('sku', $variantSku)->exists()) {
            $variantSku = $baseSku . '-' . $counter;
            $counter++;
        }
        
        return [
            'name' => $product->name ?? $request->name,
            'sku' => $variantSku,
            'barcode' => null, // Optional, can be set later
            'price' => 0, // Default to 0, user must set it
            'sale_price' => null,
            'cost_price' => null,
            'low_stock_threshold' => 0, // Default to 0
            'stock_quantity' => 0,
            'stock_status' => 'in_stock',
            'manage_stock' => true,
            'is_active' => true,
            'discount_type' => null,
            'discount_value' => null,
            'discount_active' => false,
            'attributes' => [],
            'weight' => null,
            'length' => null,
            'width' => null,
            'height' => null,
            'diameter' => null,
            'measurements' => [],
            'highlights_details' => [],
        ];
    }

    private function normalizeVariantAttributes($attributes): array
    {
        if (is_string($attributes)) {
            $decoded = json_decode($attributes, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
            return [];
        }

        return is_array($attributes) ? $attributes : [];
    }

    private function normalizeHighlightsDetails($highlightsDetails)
    {
        // Log incoming data for debugging
        Log::info('normalizeHighlightsDetails - Input', [
            'type' => gettype($highlightsDetails),
            'value' => $highlightsDetails,
            'is_string' => is_string($highlightsDetails),
            'is_array' => is_array($highlightsDetails),
        ]);

        if (is_string($highlightsDetails)) {
            // Handle JSON string from form input
            $trimmed = trim($highlightsDetails);
            if ($trimmed === '' || $trimmed === '[]' || $trimmed === 'null') {
                return [];
            }
            
            $decoded = json_decode($highlightsDetails, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $highlightsDetails = $decoded;
            } else {
                Log::warning('normalizeHighlightsDetails - JSON decode failed', [
                    'input' => $highlightsDetails,
                    'error' => json_last_error_msg(),
                ]);
                return [];
            }
        }

        if (!is_array($highlightsDetails)) {
            Log::warning('normalizeHighlightsDetails - Not an array after processing', [
                'type' => gettype($highlightsDetails),
                'value' => $highlightsDetails,
            ]);
            return [];
        }

        // Validate and normalize structure
        $normalized = [];
        foreach ($highlightsDetails as $item) {
            if (!is_array($item)) {
                continue;
            }

            $headingName = trim($item['heading_name'] ?? '');
            if (empty($headingName)) {
                continue;
            }

            $bulletPoints = [];
            if (isset($item['bullet_points']) && is_array($item['bullet_points'])) {
                foreach ($item['bullet_points'] as $point) {
                    $pointText = is_string($point) ? trim($point) : (is_array($point) ? '' : (string) $point);
                    if (!empty($pointText)) {
                        $bulletPoints[] = $pointText;
                    }
                }
            }

            $normalized[] = [
                'heading_name' => $headingName,
                'bullet_points' => $bulletPoints,
            ];
        }

        Log::info('normalizeHighlightsDetails - Output', [
            'normalized' => $normalized,
            'count' => count($normalized),
        ]);

        return $normalized;
    }

    private function normalizeVariantMeasurements($measurements): array
    {
        if (empty($measurements)) {
            return [];
        }

        if (is_string($measurements)) {
            $decoded = json_decode($measurements, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $measurements = $decoded;
            } else {
                return [];
            }
        }

        if (!is_array($measurements)) {
            return [];
        }

        static $attributeCache = [];
        static $unitCache = [];

        $normalized = [];

        foreach ($measurements as $measurement) {
            if (!is_array($measurement)) {
                continue;
            }

            $attributeId = isset($measurement['attribute_id']) ? (int) $measurement['attribute_id'] : null;
            if (!$attributeId) {
                continue;
            }

            $value = $this->normalizeNumeric($measurement['value'] ?? null);
            if ($value === null) {
                continue;
            }

            if (!array_key_exists($attributeId, $attributeCache)) {
                $attributeCache[$attributeId] = ProductAttribute::find($attributeId);
            }
            $attribute = $attributeCache[$attributeId];

            $unitId = isset($measurement['unit_id']) ? (int) $measurement['unit_id'] : null;
            if ($unitId && !array_key_exists($unitId, $unitCache)) {
                $unitCache[$unitId] = Unit::find($unitId);
            }
            $unit = $unitId ? ($unitCache[$unitId] ?? null) : null;

            $normalized[] = [
                'attribute_id' => $attributeId,
                'attribute_name' => $measurement['attribute_name'] ?? ($attribute ? $attribute->name : null),
                'attribute_slug' => $measurement['attribute_slug'] ?? ($attribute ? $attribute->slug : null),
                'value' => $value,
                'unit_id' => $unitId,
                'unit_name' => $measurement['unit_name'] ?? ($unit ? $unit->name : null),
                'unit_symbol' => $measurement['unit_symbol'] ?? ($unit ? $unit->symbol : null),
                'unit_type' => $measurement['unit_type'] ?? ($unit ? $unit->type : null),
            ];
        }

        return $normalized;
    }

    private function mapMeasurementsToLegacyDimensions(array $measurements): array
    {
        if (empty($measurements)) {
            return [];
        }

        $legacyKeys = ['weight', 'length', 'width', 'height', 'diameter'];
        $legacy = [];

        foreach ($measurements as $measurement) {
            $slug = $measurement['attribute_slug'] ?? null;
            if (!$slug || !in_array($slug, $legacyKeys, true)) {
                continue;
            }

            if (!array_key_exists($slug, $legacy)) {
                $legacy[$slug] = $measurement['value'];
            }
        }

        return $legacy;
    }

    private function filterProvidedVariants($variants): array
    {
        if (!is_array($variants)) {
            return [];
        }

        $filtered = array_filter($variants, function ($variant) {
            if (!is_array($variant)) {
                return false;
            }

            foreach ($variant as $key => $value) {
                // Skip highlights_details in the empty check - it's always included even if empty
                if ($key === 'highlights_details') {
                    continue;
                }
                
                if ($value instanceof \Illuminate\Http\UploadedFile) {
                    return true;
                }

                if (is_array($value) && !empty($value)) {
                    return true;
                }

                if (!is_array($value) && $value !== null && trim((string) $value) !== '') {
                    return true;
                }
            }

            return false;
        });

        return array_values($filtered);
    }

    private function mergeVariantFileUploads(array $variants, array $uploadedFiles): array
    {
        foreach ($variants as $index => &$variant) {
            if (!isset($uploadedFiles[$index])) {
                continue;
            }

            $images = $uploadedFiles[$index]['images'] ?? $uploadedFiles[$index];

            if ($images instanceof UploadedFile) {
                $images = [$images];
            }

            if (!is_array($images)) {
                $images = [];
            }

            $variant['images'] = $images;
        }

        return $variants;
    }


    private function normalizeNumeric($value, $default = null)
    {
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_numeric($value)) {
            return $value + 0;
        }

        return $default;
    }

    private function normalizeBoolean($value, bool $default = false): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $value = strtolower($value);
            return in_array($value, ['1', 'true', 'yes', 'on'], true);
        }

        return (bool) $value;
    }

    private function handleVariantImages(Product $product, ProductVariant $variant, array $images): void
    {
        $storedPaths = [];

        foreach ($images as $index => $image) {
            if (!$image instanceof UploadedFile || !$image->isValid()) {
                continue;
            }

            $path = $image->store('products/variants', 'public');
            $storedPaths[] = $path;

            ProductImage::create([
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'image_path' => $path,
                'alt_text' => null,
                'sort_order' => $index,
                'is_primary' => $index === 0,
            ]);
        }

        if (!empty($storedPaths)) {
            $variant->update(['image' => $storedPaths[0]]);
        }
    }

    /**
     * Delete a variant image
     */
    public function deleteVariantImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image_id' => 'required|exists:product_images,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid image ID'
            ], 422);
        }

        try {
            $image = ProductImage::findOrFail($request->image_id);
            
            // Verify this is a variant image (has product_variant_id)
            if (!$image->product_variant_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This is not a variant image'
                ], 422);
            }

            // Delete the file from storage
            if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }

            // Delete the database record
            $image->delete();

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle product status (published/hidden).
     */
    public function toggleStatus(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:published,hidden'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status value'
            ], 422);
        }

        try {
            $product->update(['status' => $request->status]);
            
            return response()->json([
                'success' => true,
                'message' => "Product status updated to {$request->status}",
                'status' => $request->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating product status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle product featured status.
     */
    public function toggleFeatured(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'featured' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid featured value'
            ], 422);
        }

        try {
            $product->update(['featured' => $request->featured]);
            
            $status = $request->featured ? 'featured' : 'unfeatured';
            
            return response()->json([
                'success' => true,
                'message' => "Product {$status} successfully",
                'featured' => $request->featured
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating featured status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search products for bundle functionality.
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'products' => []
            ]);
        }

        try {
            $products = Product::with(['images' => function($query) {
                    $query->where('is_primary', true)->orderBy('sort_order');
                }])
                ->where('status', 'published')
                ->where(function($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('sku', 'LIKE', "%{$query}%");
                })
                ->select('id', 'name', 'sku', 'price')
                ->limit(10)
                ->get()
                ->map(function($product) {
                    $primaryImage = $product->images->first();
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'price' => $product->price,
                        'image' => $primaryImage ? Storage::url($primaryImage->image_path) : '/assets/images/placeholder.jpg'
                    ];
                });

            return response()->json([
                'success' => true,
                'products' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Synchronize static attributes for a product.
     */
    private function synchronizeStaticAttributes(Product $product, Request $request): void
    {
        // Delete existing static attributes
        $product->staticAttributes()->delete();

        // Get static attributes from request
        $staticAttributes = $request->input('static_attributes', []);
        $staticAttributesMeta = $request->input('static_attributes_meta', []);

        if (empty($staticAttributes)) {
            return;
        }

        // Process each static attribute
        foreach ($staticAttributes as $attributeId => $value) {
            $attributeId = (int) $attributeId;
            $meta = $staticAttributesMeta[$attributeId] ?? [];
            $attributeType = $meta['type'] ?? 'text';

            // Skip if value is empty
            if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                continue;
            }

            // Handle different attribute types
            if (in_array($attributeType, ['select', 'multiselect'])) {
                // For select/multiselect, value is an array of ProductAttributeValue IDs
                $valueIds = is_array($value) ? $value : [$value];
                
                foreach ($valueIds as $valueId) {
                    if ($valueId) {
                        ProductStaticAttribute::create([
                            'product_id' => $product->id,
                            'product_attribute_id' => $attributeId,
                            'product_attribute_value_id' => $valueId,
                            'custom_value' => null,
                        ]);
                    }
                }
            } else {
                // For other types (text, number, date, boolean, file), store in custom_value
                $customValue = is_array($value) ? json_encode($value) : (string) $value;
                
                ProductStaticAttribute::create([
                    'product_id' => $product->id,
                    'product_attribute_id' => $attributeId,
                    'product_attribute_value_id' => null,
                    'custom_value' => $customValue,
                ]);
            }
        }
    }

    private function ensureOtherBrand(): Brand
    {
        $brand = Brand::withoutGlobalScopes()->firstOrCreate(
            ['slug' => OTHER_BRAND_SLUG],
            [
                'name' => 'Other',
                'description' => 'Generic brand for unbranded products',
                'is_active' => true,
                'sort_order' => 0,
            ]
        );

        if (!$brand->is_active) {
            $brand->is_active = true;
            $brand->save();
        }

        return $brand;
    }

    protected function normalizeTags(mixed $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        if (is_array($value)) {
            $value = implode(',', $value);
        }

        if (!is_string($value)) {
            return null;
        }

        $fragments = preg_split('/[,;]+/', $value);
        if (!$fragments) {
            return null;
        }

        $normalized = [];
        foreach ($fragments as $fragment) {
            $clean = trim(preg_replace('/\s+/u', ' ', $fragment));
            if ($clean === '') {
                continue;
            }
            $normalized[] = $clean;
        }

        $unique = array_values(array_unique($normalized, SORT_STRING));

        return empty($unique) ? null : implode(', ', $unique);
    }

    /**
     * Ensure product request has sensible defaults for type and price fields.
     */
    protected function prepareProductRequestDefaults(Request $request, ?string $fallbackType = null): void
    {
        $resolvedType = $request->input('type');

        if ($request->has('seo_url_slug')) {
            $slugValue = $request->input('seo_url_slug');
            $request->merge(['seo_url_slug' => $slugValue ? Str::slug($slugValue) : null]);
        }

        if (empty($resolvedType)) {
            $resolvedType = $this->inferProductType($request, $fallbackType);
            $request->merge(['type' => $resolvedType]);
        }

        if ($request->has('price') && $request->input('price') === '') {
            $request->merge(['price' => null]);
        }

        if ($request->has('tags')) {
            $request->merge(['tags' => $this->normalizeTags($request->input('tags'))]);
        }
    }

    /**
     * Determine product type when not explicitly provided.
     */
    protected function inferProductType(Request $request, ?string $fallbackType = null): string
    {
        if ($fallbackType && in_array($fallbackType, ['simple', 'variable', 'digital', 'service', 'bundle', 'subscription'], true)) {
            return $fallbackType;
        }

        $variantPayload = $request->input('variants');

        if (is_array($variantPayload) && count(array_filter($variantPayload))) {
            return 'variable';
        }

        return 'simple';
    }

    protected function basePriceIsRequired(?string $type): bool
    {
        return in_array($type, ['simple', 'digital', 'service'], true);
    }

    protected function hasBasePriceValue(Request $request): bool
    {
        $price = $request->input('price');
        return !is_null($price) && $price !== '';
    }
}