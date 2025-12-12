<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Unit;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;

DB::transaction(function () {
    $now = now();

    $units = collect([
        'kg' => Unit::create(['name' => 'Kilogram', 'symbol' => 'kg', 'type' => 'weight', 'is_active' => true]),
        'g'  => Unit::create(['name' => 'Gram', 'symbol' => 'g', 'type' => 'weight', 'is_active' => true]),
        'cm' => Unit::create(['name' => 'Centimeter', 'symbol' => 'cm', 'type' => 'length', 'is_active' => true]),
        'm'  => Unit::create(['name' => 'Meter', 'symbol' => 'm', 'type' => 'length', 'is_active' => true]),
        'L'  => Unit::create(['name' => 'Liter', 'symbol' => 'L', 'type' => 'volume', 'is_active' => true]),
        'ml' => Unit::create(['name' => 'Milliliter', 'symbol' => 'ml', 'type' => 'volume', 'is_active' => true]),
        'pc' => Unit::create(['name' => 'Piece', 'symbol' => 'pc', 'type' => 'other', 'is_active' => true]),
    ]);

    $brandDescriptions = [
        'Apple' => 'Innovative consumer electronics branded by Apple.',
        'Samsung' => 'Cutting-edge electronics and smart appliances.',
        'LG' => 'Smart home appliance and consumer electronics brand.',
        'FreshFarm' => 'Farm-fresh pantry staples sourced sustainably.',
        'DailyGrocer' => 'Daily grocery essentials and beverages.',
        'FashionHub' => 'Contemporary apparel for everyday wear.',
        'StyleCo' => 'Modern fashion staples with durable fabrics.',
        'HomeComfort' => 'Comfort-focused furniture and home goods.',
        'CosmoCare' => 'Luxury cosmetics and fine fragrances.',
    ];

    $brands = collect();
    $brandOrder = 1;
    foreach ($brandDescriptions as $name => $description) {
        $brands[$name] = Brand::create([
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $description,
            'is_active' => true,
            'sort_order' => $brandOrder++,
        ]);
    }

    $baselineAttributes = collect([
        'text-overview' => ProductAttribute::create([
            'name' => 'Overview Notes',
            'slug' => 'overview-notes',
            'type' => 'text',
            'description' => 'Additional descriptive text for product variants',
            'is_variation' => false,
            'is_visible' => true,
            'sort_order' => 5,
        ]),
        'boolean-organic' => ProductAttribute::create([
            'name' => 'Organic Certified',
            'slug' => 'organic-certified',
            'type' => 'boolean',
            'description' => 'Marks whether the product is organically certified',
            'is_variation' => false,
            'is_visible' => true,
            'sort_order' => 6,
        ]),
        'date-release' => ProductAttribute::create([
            'name' => 'Release Date',
            'slug' => 'release-date',
            'type' => 'date',
            'description' => 'Launch or release date for the variant',
            'is_variation' => false,
            'is_visible' => true,
            'sort_order' => 7,
        ]),
        'image-swatch' => ProductAttribute::create([
            'name' => 'Swatch Image',
            'slug' => 'swatch-image',
            'type' => 'image',
            'description' => 'Image swatch associated with a variant option',
            'is_variation' => true,
            'is_visible' => true,
            'sort_order' => 8,
        ]),
    ]);

    $measurementAttributes = collect([
        'weight' => ProductAttribute::create(['name' => 'Weight', 'slug' => 'weight', 'type' => 'number', 'description' => 'Variant weight', 'is_variation' => false, 'is_visible' => true, 'sort_order' => 10]),
        'length' => ProductAttribute::create(['name' => 'Length', 'slug' => 'length', 'type' => 'number', 'description' => 'Variant length', 'is_variation' => false, 'is_visible' => true, 'sort_order' => 20]),
        'width'  => ProductAttribute::create(['name' => 'Width', 'slug' => 'width', 'type' => 'number', 'description' => 'Variant width', 'is_variation' => false, 'is_visible' => true, 'sort_order' => 30]),
        'height' => ProductAttribute::create(['name' => 'Height', 'slug' => 'height', 'type' => 'number', 'description' => 'Variant height', 'is_variation' => false, 'is_visible' => true, 'sort_order' => 40]),
        'diameter' => ProductAttribute::create(['name' => 'Diameter', 'slug' => 'diameter', 'type' => 'number', 'description' => 'Variant diameter', 'is_variation' => false, 'is_visible' => true, 'sort_order' => 50]),
        'volume' => ProductAttribute::create(['name' => 'Volume', 'slug' => 'volume', 'type' => 'number', 'description' => 'Variant volume', 'is_variation' => false, 'is_visible' => true, 'sort_order' => 60]),
    ]);

    $variationSpecs = [
        'storage-capacity' => ['Storage Capacity', ['128GB', '256GB', '512GB']],
        'color' => ['Color', ['Midnight Black', 'Alpine Blue', 'Crimson Red', 'Graphite Gray', 'Champagne Gold', 'Ocean Blue', 'Forest Green', 'Caramel Brown', 'Ivory Beige', 'Natural Oak', 'Walnut Brown', 'Deep Indigo']],
        'drum-capacity' => ['Drum Capacity', ['6kg Drum', '7kg Drum', '8kg Drum']],
        'pack-size' => ['Pack Size', ['500g Pack', '1kg Pack', '2kg Pack', '5kg Pack', '500ml Bottle', '1L Bottle']],
        'size' => ['Clothing Size', ['S', 'M', 'L', 'XL']],
        'waist-size' => ['Waist Size', ['30', '32', '34', '36']],
        'fragrance-option' => ['Fragrance Option', ['Daywear Blend', 'Nightfall Blend', 'Signature Scent']],
        'seating-configuration' => ['Seating Configuration', ['2-Seater', '3-Seater', 'L-Shape Sectional']],
        'bed-size' => ['Bed Size', ['Queen', 'King']],
    ];

    $variationAttributes = collect();
    $variationOrder = 1;
    foreach ($variationSpecs as $slug => [$name, $values]) {
        $attribute = ProductAttribute::create([
            'name' => $name,
            'slug' => $slug,
            'type' => 'select',
            'description' => $name,
            'is_variation' => true,
            'is_visible' => true,
            'sort_order' => $variationOrder++,
        ]);

        foreach ($values as $index => $value) {
            ProductAttributeValue::create([
                'attribute_id' => $attribute->id,
                'value' => $value,
                'sort_order' => $index,
            ]);
        }

        $variationAttributes[$slug] = $attribute;
    }

    // Seed baseline attribute values for quick usage
    ProductAttributeValue::insert([
        [
            'attribute_id' => $baselineAttributes['text-overview']->id,
            'value' => 'Limited edition run',
            'sort_order' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'attribute_id' => $baselineAttributes['boolean-organic']->id,
            'value' => 'Yes',
            'sort_order' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'attribute_id' => $baselineAttributes['date-release']->id,
            'value' => $now->copy()->subMonths(1)->format('Y-m-d'),
            'sort_order' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'attribute_id' => $baselineAttributes['image-swatch']->id,
            'value' => 'swatch-default.png',
            'sort_order' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ],
    ]);

    $categoryTree = [
        'Electronics' => ['Smartphones', 'Home Appliances'],
        'Grocery' => ['Staples', 'Beverages', 'Snacks'],
        'Fashion' => ['Men\'s Wear', 'Women\'s Wear', 'Accessories'],
        'Furniture' => ['Bedroom Furniture', 'Living Room Furniture'],
        'Cosmetics' => ['Fragrances', 'Skincare'],
    ];

    $categories = collect();
    $categorySort = 1;
    foreach ($categoryTree as $parentName => $children) {
        $parent = Category::create([
            'name' => $parentName,
            'parent_id' => null,
            'is_active' => true,
            'sort_order' => $categorySort++,
            'featured' => false,
        ]);
        $categories[$parentName] = $parent;

        $childSort = 1;
        foreach ($children as $childName) {
            $child = Category::create([
                'name' => $childName,
                'parent_id' => $parent->id,
                'is_active' => true,
                'sort_order' => $childSort++,
                'featured' => false,
            ]);
            $categories[$childName] = $child;
        }
    }

    $makeMeasurement = function (string $slug, float $value, string $unitSymbol) use ($measurementAttributes, $units) {
        $attribute = $measurementAttributes->get($slug);
        $unit = $units->get($unitSymbol);

        return [
            'attribute_id' => $attribute ? $attribute->id : null,
            'attribute_name' => $attribute ? $attribute->name : Str::title(str_replace('-', ' ', $slug)),
            'attribute_slug' => $attribute ? $attribute->slug : $slug,
            'value' => $value,
            'unit_id' => $unit ? $unit->id : null,
            'unit_name' => $unit ? $unit->name : null,
            'unit_symbol' => $unit ? $unit->symbol : null,
            'unit_type' => $unit ? $unit->type : null,
        ];
    };

    $mapLegacyDimensions = function (array $measurements) {
        $legacy = [];
        foreach ($measurements as $measurement) {
            $slug = $measurement['attribute_slug'] ?? null;
            if ($slug && in_array($slug, ['weight', 'length', 'width', 'height', 'diameter'], true) && !array_key_exists($slug, $legacy)) {
                $legacy[$slug] = $measurement['value'];
            }
        }
        return $legacy;
    };

    $buildMeasurements = function (array $specs) use ($makeMeasurement) {
        return array_map(function ($spec) use ($makeMeasurement) {
            return $makeMeasurement($spec['slug'], $spec['value'], $spec['unit']);
        }, $specs);
    };

    $buildAttributes = function (array $pairs) use ($variationAttributes) {
        $result = [];
        foreach ($pairs as $pair) {
            $attribute = $variationAttributes->get($pair['slug']);
            if ($attribute) {
                $result[(string) $attribute->id] = $pair['value'];
            }
        }
        return $result;
    };

    $attachBrand = function (Product $product, string $brandName) use ($brands, $now) {
        $brand = $brands->get($brandName);
        if ($brand) {
            $product->brand_id = $brand->id;
            $product->save();

            $product->brands()->attach($brand->id, [
                'is_primary' => true,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    };

    $attachCategories = function (Product $product, array $categorySpecs) use ($categories, $now) {
        $pivot = [];
        foreach ($categorySpecs as $spec) {
            $category = $categories->get($spec['name']);
            if ($category) {
                $pivot[$category->id] = [
                    'is_primary' => $spec['is_primary'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        if ($pivot) {
            $product->categories()->attach($pivot);
        }
    };

    $createVariant = function (Product $product, int $index, array $variant, callable $buildMeasurements, callable $mapLegacyDimensions, callable $buildAttributes) {
        $measurements = $buildMeasurements($variant['measurements']);
        $legacy = $mapLegacyDimensions($measurements);
        $attributes = $buildAttributes($variant['attributes']);

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $variant['sku'],
            'name' => $variant['name'],
            'price' => $variant['price'],
            'sale_price' => $variant['sale_price'],
            'cost_price' => $variant['cost_price'],
            'stock_quantity' => $variant['stock_quantity'],
            'manage_stock' => true,
            'stock_status' => $variant['stock_quantity'] > 0 ? 'in_stock' : 'out_of_stock',
            'discount_type' => $variant['discount_type'] ?? null,
            'discount_value' => $variant['discount_value'] ?? null,
            'discount_active' => $variant['discount_active'] ?? false,
            'attributes' => $attributes,
            'measurements' => $measurements,
            'weight' => $legacy['weight'] ?? null,
            'length' => $legacy['length'] ?? null,
            'width' => $legacy['width'] ?? null,
            'height' => $legacy['height'] ?? null,
            'diameter' => $legacy['diameter'] ?? null,
            'is_active' => true,
            'sort_order' => $index,
        ]);
    };

    $createProduct = function (array $data) use ($now, $attachBrand, $attachCategories, $createVariant, $buildMeasurements, $mapLegacyDimensions, $buildAttributes) {
        $primaryVariant = $data['variants'][0] ?? null;
        $basePrice = $data['base_price'] ?? ($primaryVariant['price'] ?? 0);
        $baseSalePrice = $data['base_sale_price'] ?? ($primaryVariant['sale_price'] ?? null);
        $product = Product::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'],
            'short_description' => $data['short_description'],
            'sku' => $data['sku'],
            'barcode' => $data['barcode'] ?? null,
            'type' => $data['type'],
            'price' => $basePrice,
            'sale_price' => $baseSalePrice,
            'manage_stock' => false,
            'stock_quantity' => 0,
            'stock_status' => 'in_stock',
            'allow_backorder' => false,
            'requires_shipping' => true,
            'free_shipping' => false,
            'meta_title' => $data['meta_title'] ?? $data['name'],
            'meta_description' => $data['meta_description'] ?? Str::limit($data['description'], 150),
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'status' => $data['status'] ?? 'published',
            'featured' => $data['featured'] ?? false,
            'published_at' => $now,
        ]);

        $attachBrand($product, $data['brand']);
        $attachCategories($product, $data['categories']);

        foreach ($data['variants'] as $index => $variant) {
            $createVariant($product, $index, $variant, $buildMeasurements, $mapLegacyDimensions, $buildAttributes);
        }

        return $product;
    };

    $products = [
        [
            'name' => 'Apple iPhone 17 Pro',
            'type' => 'variable',
            'brand' => 'Apple',
            'sku' => 'PROD-IPH17',
            'barcode' => 'APL-IPH17-0001',
            'base_price' => 1299.00,
            'base_sale_price' => null,
            'description' => 'The latest Apple iPhone 17 Pro with A19 Bionic chip, ProMotion display, and an advanced triple-lens camera system ideal for creative professionals.',
            'short_description' => 'Flagship Apple iPhone 17 Pro with ProMotion display and advanced camera.',
            'categories' => [
                ['name' => 'Electronics', 'is_primary' => true],
                ['name' => 'Smartphones', 'is_primary' => false],
            ],
            'variants' => [
                [
                    'sku' => 'IP17-128-MBK',
                    'name' => 'iPhone 17 Pro 128GB - Midnight Black',
                    'price' => 1299.00,
                    'sale_price' => null,
                    'cost_price' => 945.00,
                    'stock_quantity' => 30,
                    'attributes' => [
                        ['slug' => 'storage-capacity', 'value' => '128GB'],
                        ['slug' => 'color', 'value' => 'Midnight Black'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 0.24, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 15.6, 'unit' => 'cm'],
                        ['slug' => 'width', 'value' => 7.5, 'unit' => 'cm'],
                        ['slug' => 'height', 'value' => 0.78, 'unit' => 'cm'],
                    ],
                ],
                [
                    'sku' => 'IP17-256-GLD',
                    'name' => 'iPhone 17 Pro 256GB - Champagne Gold',
                    'price' => 1399.00,
                    'sale_price' => 1349.00,
                    'cost_price' => 995.00,
                    'stock_quantity' => 20,
                    'attributes' => [
                        ['slug' => 'storage-capacity', 'value' => '256GB'],
                        ['slug' => 'color', 'value' => 'Champagne Gold'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 0.24, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 15.6, 'unit' => 'cm'],
                        ['slug' => 'width', 'value' => 7.5, 'unit' => 'cm'],
                        ['slug' => 'height', 'value' => 0.78, 'unit' => 'cm'],
                    ],
                ],
                [
                    'sku' => 'IP17-512-BLU',
                    'name' => 'iPhone 17 Pro 512GB - Alpine Blue',
                    'price' => 1549.00,
                    'sale_price' => null,
                    'cost_price' => 1080.00,
                    'stock_quantity' => 15,
                    'attributes' => [
                        ['slug' => 'storage-capacity', 'value' => '512GB'],
                        ['slug' => 'color', 'value' => 'Alpine Blue'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 0.24, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 15.6, 'unit' => 'cm'],
                        ['slug' => 'width', 'value' => 7.5, 'unit' => 'cm'],
                        ['slug' => 'height', 'value' => 0.78, 'unit' => 'cm'],
                    ],
                ],
            ],
        ],
        [
            'name' => 'Samsung FlexWash 9000 Washing Machine',
            'type' => 'variable',
            'brand' => 'Samsung',
            'sku' => 'PROD-FLEX9000',
            'barcode' => 'SAM-FLEX-9000',
            'base_price' => 999.00,
            'base_sale_price' => 949.00,
            'description' => 'High-capacity FlexWash 9000 washing machine with AI-powered cycles, steam cleaning, and smart load balancing for busy households.',
            'short_description' => 'Smart FlexWash washer with steam cleaning and dual load flexibility.',
            'categories' => [
                ['name' => 'Electronics', 'is_primary' => true],
                ['name' => 'Home Appliances', 'is_primary' => false],
            ],
            'variants' => [
                [
                    'sku' => 'FLEX-6KG-GRY',
                    'name' => 'FlexWash 9000 6kg Drum - Graphite Gray',
                    'price' => 999.00,
                    'sale_price' => 949.00,
                    'cost_price' => 680.00,
                    'stock_quantity' => 18,
                    'attributes' => [
                        ['slug' => 'drum-capacity', 'value' => '6kg Drum'],
                        ['slug' => 'color', 'value' => 'Graphite Gray'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 68.0, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 85.0, 'unit' => 'cm'],
                        ['slug' => 'width', 'value' => 60.0, 'unit' => 'cm'],
                        ['slug' => 'height', 'value' => 85.0, 'unit' => 'cm'],
                    ],
                ],
                [
                    'sku' => 'FLEX-7KG-WHT',
                    'name' => 'FlexWash 9000 7kg Drum - Ivory Beige',
                    'price' => 1099.00,
                    'sale_price' => null,
                    'cost_price' => 725.00,
                    'stock_quantity' => 12,
                    'attributes' => [
                        ['slug' => 'drum-capacity', 'value' => '7kg Drum'],
                        ['slug' => 'color', 'value' => 'Ivory Beige'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 72.5, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 86.0, 'unit' => 'cm'],
                        ['slug' => 'width', 'value' => 60.0, 'unit' => 'cm'],
                        ['slug' => 'height', 'value' => 85.0, 'unit' => 'cm'],
                    ],
                ],
                [
                    'sku' => 'FLEX-8KG-BLK',
                    'name' => 'FlexWash 9000 8kg Drum - Midnight Black',
                    'price' => 1199.00,
                    'sale_price' => null,
                    'cost_price' => 770.00,
                    'stock_quantity' => 10,
                    'attributes' => [
                        ['slug' => 'drum-capacity', 'value' => '8kg Drum'],
                        ['slug' => 'color', 'value' => 'Midnight Black'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 75.8, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 86.0, 'unit' => 'cm'],
                        ['slug' => 'width', 'value' => 60.0, 'unit' => 'cm'],
                        ['slug' => 'height', 'value' => 85.0, 'unit' => 'cm'],
                    ],
                ],
            ],
        ],
        [
            'name' => 'FreshFarm Organic Basmati Rice',
            'type' => 'variable',
            'brand' => 'FreshFarm',
            'sku' => 'PROD-FF-RICE',
            'barcode' => 'FF-RICE-0001',
            'base_price' => 6.49,
            'base_sale_price' => 5.99,
            'description' => 'Long-grain organic basmati rice harvested from sustainable farms and packaged for freshness.',
            'short_description' => 'Organic basmati rice with aromatic long grains.',
            'categories' => [
                ['name' => 'Grocery', 'is_primary' => true],
                ['name' => 'Staples', 'is_primary' => false],
            ],
            'variants' => [
                [
                    'sku' => 'RICE-500G',
                    'name' => 'Organic Basmati Rice 500g Pack',
                    'price' => 6.49,
                    'sale_price' => 5.99,
                    'cost_price' => 3.20,
                    'stock_quantity' => 80,
                    'attributes' => [
                        ['slug' => 'pack-size', 'value' => '500g Pack'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 0.5, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 24.0, 'unit' => 'cm'],
                        ['slug' => 'width', 'value' => 16.0, 'unit' => 'cm'],
                        ['slug' => 'height', 'value' => 6.0, 'unit' => 'cm'],
                    ],
                ],
                [
                    'sku' => 'RICE-1KG',
                    'name' => 'Organic Basmati Rice 1kg Pack',
                    'price' => 11.99,
                    'sale_price' => null,
                    'cost_price' => 6.10,
                    'stock_quantity' => 60,
                    'attributes' => [
                        ['slug' => 'pack-size', 'value' => '1kg Pack'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 1.0, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 28.0, 'unit' => 'cm'],
                        ['slug' => 'width', 'value' => 18.0, 'unit' => 'cm'],
                        ['slug' => 'height', 'value' => 8.0, 'unit' => 'cm'],
                    ],
                ],
                [
                    'sku' => 'RICE-2KG',
                    'name' => 'Organic Basmati Rice 2kg Pack',
                    'price' => 21.99,
                    'sale_price' => 19.99,
                    'cost_price' => 11.80,
                    'stock_quantity' => 40,
                    'attributes' => [
                        ['slug' => 'pack-size', 'value' => '2kg Pack'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 2.0, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 32.0, 'unit' => 'cm'],
                        ['slug' => 'width', 'value' => 20.0, 'unit' => 'cm'],
                        ['slug' => 'height', 'value' => 9.0, 'unit' => 'cm'],
                    ],
                ],
            ],
        ],
        [
            'name' => 'DailyGrocer Cold Brew Coffee Concentrate',
            'type' => 'variable',
            'brand' => 'DailyGrocer',
            'sku' => 'PROD-DG-COLDBREW',
            'barcode' => 'DG-COLDBREW',
            'base_price' => 9.50,
            'base_sale_price' => 8.99,
            'description' => 'Slow-steeped cold brew coffee concentrate made with 100% Arabica beans and perfect for iced beverages.',
            'short_description' => 'Arabica cold brew concentrate in ready-to-mix bottles.',
            'categories' => [
                ['name' => 'Grocery', 'is_primary' => true],
                ['name' => 'Beverages', 'is_primary' => false],
            ],
            'variants' => [
                [
                    'sku' => 'COLDBREW-500ML',
                    'name' => 'Cold Brew Concentrate 500ml Bottle',
                    'price' => 9.50,
                    'sale_price' => 8.99,
                    'cost_price' => 4.60,
                    'stock_quantity' => 90,
                    'attributes' => [
                        ['slug' => 'pack-size', 'value' => '500ml Bottle'],
                    ],
                    'measurements' => [
                        ['slug' => 'volume', 'value' => 500.0, 'unit' => 'ml'],
                        ['slug' => 'weight', 'value' => 0.65, 'unit' => 'kg'],
                        ['slug' => 'height', 'value' => 21.0, 'unit' => 'cm'],
                    ],
                ],
                [
                    'sku' => 'COLDBREW-1L',
                    'name' => 'Cold Brew Concentrate 1L Bottle',
                    'price' => 16.00,
                    'sale_price' => null,
                    'cost_price' => 7.90,
                    'stock_quantity' => 70,
                    'attributes' => [
                        ['slug' => 'pack-size', 'value' => '1L Bottle'],
                    ],
                    'measurements' => [
                        ['slug' => 'volume', 'value' => 1000.0, 'unit' => 'ml'],
                        ['slug' => 'weight', 'value' => 1.2, 'unit' => 'kg'],
                        ['slug' => 'height', 'value' => 25.0, 'unit' => 'cm'],
                    ],
                ],
            ],
        ],
        [
            'name' => 'FreshFarm Crunchy Almond Trail Mix',
            'type' => 'variable',
            'brand' => 'FreshFarm',
            'sku' => 'PROD-FF-TRAIL',
            'barcode' => 'FF-TRAIL-0001',
            'base_price' => 11.25,
            'base_sale_price' => 10.50,
            'description' => 'Premium almond trail mix with dried berries, roasted seeds, and dark chocolate chips for a balanced snack.',
            'short_description' => 'Almond and berry trail mix packaged in resealable bags.',
            'categories' => [
                ['name' => 'Grocery', 'is_primary' => true],
                ['name' => 'Snacks', 'is_primary' => false],
            ],
            'variants' => [
                [
                    'sku' => 'TRAIL-500G',
                    'name' => 'Trail Mix 500g Pack',
                    'price' => 11.25,
                    'sale_price' => 10.50,
                    'cost_price' => 5.40,
                    'stock_quantity' => 75,
                    'attributes' => [
                        ['slug' => 'pack-size', 'value' => '500g Pack'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 0.5, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 22.0, 'unit' => 'cm'],
                        ['slug' => 'width', 'value' => 16.0, 'unit' => 'cm'],
                        ['slug' => 'height', 'value' => 5.5, 'unit' => 'cm'],
                    ],
                ],
                [
                    'sku' => 'TRAIL-1KG',
                    'name' => 'Trail Mix 1kg Pack',
                    'price' => 19.75,
                    'sale_price' => null,
                    'cost_price' => 9.80,
                    'stock_quantity' => 55,
                    'attributes' => [
                        ['slug' => 'pack-size', 'value' => '1kg Pack'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 1.0, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 26.0, 'unit' => 'cm'],
                        ['slug' => 'width', 'value' => 18.0, 'unit' => 'cm'],
                        ['slug' => 'height', 'value' => 7.0, 'unit' => 'cm'],
                    ],
                ],
            ],
        ],
        [
            'name' => 'FashionHub Classic Cotton Tee',
            'type' => 'variable',
            'brand' => 'FashionHub',
            'sku' => 'PROD-FH-TEE',
            'barcode' => 'FH-TEE-0001',
            'base_price' => 24.00,
            'base_sale_price' => 19.99,
            'description' => 'Soft and breathable 100% cotton t-shirt tailored for everyday comfort, available in multiple colors and sizes.',
            'short_description' => 'Classic fit cotton tee in seasonal colors.',
            'categories' => [
                ['name' => 'Fashion', 'is_primary' => true],
                ['name' => 'Men\'s Wear', 'is_primary' => false],
            ],
            'variants' => [
                [
                    'sku' => 'TEE-S-NAV',
                    'name' => 'Classic Cotton Tee - Navy / Small',
                    'price' => 24.00,
                    'sale_price' => 19.99,
                    'cost_price' => 8.50,
                    'stock_quantity' => 40,
                    'attributes' => [
                        ['slug' => 'size', 'value' => 'S'],
                        ['slug' => 'color', 'value' => 'Ocean Blue'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 0.18, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 70.0, 'unit' => 'cm'],
                        ['slug' => 'width', 'value' => 48.0, 'unit' => 'cm'],
                    ],
                ],
                [
                    'sku' => 'TEE-M-GRY',
                    'name' => 'Classic Cotton Tee - Heather Grey / Medium',
                    'price' => 24.00,
                    'sale_price' => null,
                    'cost_price' => 8.50,
                    'stock_quantity' => 55,
                    'attributes' => [
                        ['slug' => 'size', 'value' => 'M'],
                        ['slug' => 'color', 'value' => 'Graphite Gray'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 0.19, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 72.0, 'unit' => 'cm'],
                        ['slug' => 'width', 'value' => 51.0, 'unit' => 'cm'],
                    ],
                ],
                [
                    'sku' => 'TEE-L-RED',
                    'name' => 'Classic Cotton Tee - Crimson / Large',
                    'price' => 24.00,
                    'sale_price' => null,
                    'cost_price' => 8.50,
                    'stock_quantity' => 35,
                    'attributes' => [
                        ['slug' => 'size', 'value' => 'L'],
                        ['slug' => 'color', 'value' => 'Crimson Red'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 0.20, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 74.0, 'unit' => 'cm'],
                        ['slug' => 'width', 'value' => 53.0, 'unit' => 'cm'],
                    ],
                ],
            ],
        ],
        [
            'name' => 'StyleCo Slim Fit Denim',
            'type' => 'variable',
            'brand' => 'StyleCo',
            'sku' => 'PROD-SC-DENIM',
            'barcode' => 'SC-DENIM-0001',
            'base_price' => 54.00,
            'base_sale_price' => 49.00,
            'description' => 'Stretch-denim slim fit pants with reinforced stitching and enzyme-wash finish.',
            'short_description' => 'Slim fit denim in classic indigo washes.',
            'categories' => [
                ['name' => 'Fashion', 'is_primary' => true],
                ['name' => 'Men\'s Wear', 'is_primary' => false],
            ],
            'variants' => [
                [
                    'sku' => 'DENIM-30-IND',
                    'name' => 'Slim Fit Denim - Waist 30 / Indigo',
                    'price' => 54.00,
                    'sale_price' => 49.00,
                    'cost_price' => 18.00,
                    'stock_quantity' => 28,
                    'attributes' => [
                        ['slug' => 'waist-size', 'value' => '30'],
                        ['slug' => 'color', 'value' => 'Deep Indigo'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 0.62, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 104.0, 'unit' => 'cm'],
                    ],
                ],
                [
                    'sku' => 'DENIM-32-IND',
                    'name' => 'Slim Fit Denim - Waist 32 / Indigo',
                    'price' => 54.00,
                    'sale_price' => null,
                    'cost_price' => 18.00,
                    'stock_quantity' => 32,
                    'attributes' => [
                        ['slug' => 'waist-size', 'value' => '32'],
                        ['slug' => 'color', 'value' => 'Deep Indigo'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 0.64, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 106.0, 'unit' => 'cm'],
                    ],
                ],
                [
                    'sku' => 'DENIM-34-BLK',
                    'name' => 'Slim Fit Denim - Waist 34 / Black',
                    'price' => 54.00,
                    'sale_price' => null,
                    'cost_price' => 18.00,
                    'stock_quantity' => 20,
                    'attributes' => [
                        ['slug' => 'waist-size', 'value' => '34'],
                        ['slug' => 'color', 'value' => 'Midnight Black'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 0.66, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 107.0, 'unit' => 'cm'],
                    ],
                ],
            ],
        ],
        [
            'name' => 'FashionHub Leather Crossbody Purse',
            'type' => 'variable',
            'brand' => 'FashionHub',
            'sku' => 'PROD-FH-PURSE',
            'barcode' => 'FH-PURSE-0001',
            'base_price' => 119.00,
            'base_sale_price' => 109.00,
            'description' => 'Premium leather crossbody purse with adjustable strap, interior zip pocket, and suede lining.',
            'short_description' => 'Leather crossbody purse with adjustable strap.',
            'categories' => [
                ['name' => 'Fashion', 'is_primary' => true],
                ['name' => 'Accessories', 'is_primary' => false],
            ],
            'variants' => [
                [
                    'sku' => 'PURSE-BRN',
                    'name' => 'Leather Crossbody Purse - Caramel Brown',
                    'price' => 119.00,
                    'sale_price' => 109.00,
                    'cost_price' => 45.00,
                    'stock_quantity' => 25,
                    'attributes' => [
                        ['slug' => 'color', 'value' => 'Caramel Brown'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 0.48, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 24.0, 'unit' => 'cm'],
                        ['slug' => 'width', 'value' => 8.0, 'unit' => 'cm'],
                        ['slug' => 'height', 'value' => 18.0, 'unit' => 'cm'],
                    ],
                ],
                [
                    'sku' => 'PURSE-BLK',
                    'name' => 'Leather Crossbody Purse - Midnight Black',
                    'price' => 119.00,
                    'sale_price' => null,
                    'cost_price' => 45.00,
                    'stock_quantity' => 22,
                    'attributes' => [
                        ['slug' => 'color', 'value' => 'Midnight Black'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 0.49, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 24.0, 'unit' => 'cm'],
                        ['slug' => 'width', 'value' => 8.0, 'unit' => 'cm'],
                        ['slug' => 'height', 'value' => 18.0, 'unit' => 'cm'],
                    ],
                ],
            ],
        ],
        [
            'name' => 'HomeComfort Solid Oak Platform Bed',
            'type' => 'variable',
            'brand' => 'HomeComfort',
            'sku' => 'PROD-HC-OAKBED',
            'barcode' => 'HC-OAKBED-0001',
            'base_price' => 899.00,
            'base_sale_price' => 849.00,
            'description' => 'Solid oak platform bed with slatted frame, reinforced center rail, and a natural hand-oiled finish.',
            'short_description' => 'Solid oak platform bed in queen and king sizes.',
            'categories' => [
                ['name' => 'Furniture', 'is_primary' => true],
                ['name' => 'Bedroom Furniture', 'is_primary' => false],
            ],
            'variants' => [
                [
                    'sku' => 'OAKBED-QUEEN',
                    'name' => 'Solid Oak Platform Bed - Queen',
                    'price' => 899.00,
                    'sale_price' => 849.00,
                    'cost_price' => 520.00,
                    'stock_quantity' => 14,
                    'attributes' => [
                        ['slug' => 'bed-size', 'value' => 'Queen'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 68.0, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 212.0, 'unit' => 'cm'],
                        ['slug' => 'width', 'value' => 162.0, 'unit' => 'cm'],
                        ['slug' => 'height', 'value' => 35.0, 'unit' => 'cm'],
                    ],
                ],
                [
                    'sku' => 'OAKBED-KING',
                    'name' => 'Solid Oak Platform Bed - King',
                    'price' => 979.00,
                    'sale_price' => null,
                    'cost_price' => 565.00,
                    'stock_quantity' => 10,
                    'attributes' => [
                        ['slug' => 'bed-size', 'value' => 'King'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 74.0, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 212.0, 'unit' => 'cm'],
                        ['slug' => 'width', 'value' => 192.0, 'unit' => 'cm'],
                        ['slug' => 'height', 'value' => 35.0, 'unit' => 'cm'],
                    ],
                ],
            ],
        ],
        [
            'name' => 'HomeComfort Modular Sectional Sofa',
            'type' => 'variable',
            'brand' => 'HomeComfort',
            'sku' => 'PROD-HC-SOFA',
            'barcode' => 'HC-SOFA-0001',
            'base_price' => 1299.00,
            'base_sale_price' => 1249.00,
            'description' => 'Modular sectional sofa with high-density cushioning, stain-resistant upholstery, and configurable seating layouts.',
            'short_description' => 'Modular sectional sofa in multiple configurations.',
            'categories' => [
                ['name' => 'Furniture', 'is_primary' => true],
                ['name' => 'Living Room Furniture', 'is_primary' => false],
            ],
            'variants' => [
                [
                    'sku' => 'SOFA-2S-GRY',
                    'name' => 'Modular Sofa - 2-Seater Graphite',
                    'price' => 1299.00,
                    'sale_price' => 1249.00,
                    'cost_price' => 720.00,
                    'stock_quantity' => 9,
                    'attributes' => [
                        ['slug' => 'seating-configuration', 'value' => '2-Seater'],
                        ['slug' => 'color', 'value' => 'Graphite Gray'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 58.0, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 190.0, 'unit' => 'cm'],
                        ['slug' => 'width', 'value' => 92.0, 'unit' => 'cm'],
                        ['slug' => 'height', 'value' => 88.0, 'unit' => 'cm'],
                    ],
                ],
                [
                    'sku' => 'SOFA-3S-BLU',
                    'name' => 'Modular Sofa - 3-Seater Ocean Blue',
                    'price' => 1549.00,
                    'sale_price' => null,
                    'cost_price' => 845.00,
                    'stock_quantity' => 7,
                    'attributes' => [
                        ['slug' => 'seating-configuration', 'value' => '3-Seater'],
                        ['slug' => 'color', 'value' => 'Ocean Blue'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 72.0, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 240.0, 'unit' => 'cm'],
                        ['slug' => 'width', 'value' => 92.0, 'unit' => 'cm'],
                        ['slug' => 'height', 'value' => 88.0, 'unit' => 'cm'],
                    ],
                ],
                [
                    'sku' => 'SOFA-L-TAU',
                    'name' => 'Modular Sofa - L-Shape Taupe',
                    'price' => 1999.00,
                    'sale_price' => 1899.00,
                    'cost_price' => 1020.00,
                    'stock_quantity' => 5,
                    'attributes' => [
                        ['slug' => 'seating-configuration', 'value' => 'L-Shape Sectional'],
                        ['slug' => 'color', 'value' => 'Ivory Beige'],
                    ],
                    'measurements' => [
                        ['slug' => 'weight', 'value' => 96.0, 'unit' => 'kg'],
                        ['slug' => 'length', 'value' => 290.0, 'unit' => 'cm'],
                        ['slug' => 'width', 'value' => 220.0, 'unit' => 'cm'],
                        ['slug' => 'height', 'value' => 88.0, 'unit' => 'cm'],
                    ],
                ],
            ],
        ],
        [
            'name' => 'CosmoCare Signature Eau de Parfum',
            'type' => 'variable',
            'brand' => 'CosmoCare',
            'sku' => 'PROD-CC-SIGNATURE',
            'barcode' => 'CC-SIGN-0001',
            'base_price' => 129.00,
            'base_sale_price' => 119.00,
            'description' => 'CosmoCare signature eau de parfum crafted with layered florals, amber, and woods, designed for day and evening wear.',
            'short_description' => 'Signature eau de parfum in three fragrance blends.',
            'categories' => [
                ['name' => 'Cosmetics', 'is_primary' => true],
                ['name' => 'Fragrances', 'is_primary' => false],
            ],
            'variants' => [
                [
                    'sku' => 'PERFUME-DAY',
                    'name' => 'Signature Eau de Parfum - Daywear Blend',
                    'price' => 129.00,
                    'sale_price' => 119.00,
                    'cost_price' => 48.00,
                    'stock_quantity' => 35,
                    'attributes' => [
                        ['slug' => 'fragrance-option', 'value' => 'Daywear Blend'],
                    ],
                    'measurements' => [
                        ['slug' => 'volume', 'value' => 100.0, 'unit' => 'ml'],
                        ['slug' => 'weight', 'value' => 0.35, 'unit' => 'kg'],
                        ['slug' => 'height', 'value' => 14.0, 'unit' => 'cm'],
                    ],
                ],
                [
                    'sku' => 'PERFUME-NIGHT',
                    'name' => 'Signature Eau de Parfum - Nightfall Blend',
                    'price' => 129.00,
                    'sale_price' => null,
                    'cost_price' => 48.00,
                    'stock_quantity' => 28,
                    'attributes' => [
                        ['slug' => 'fragrance-option', 'value' => 'Nightfall Blend'],
                    ],
                    'measurements' => [
                        ['slug' => 'volume', 'value' => 100.0, 'unit' => 'ml'],
                        ['slug' => 'weight', 'value' => 0.35, 'unit' => 'kg'],
                        ['slug' => 'height', 'value' => 14.0, 'unit' => 'cm'],
                    ],
                ],
                [
                    'sku' => 'PERFUME-SIGN',
                    'name' => 'Signature Eau de Parfum - Signature Scent',
                    'price' => 129.00,
                    'sale_price' => null,
                    'cost_price' => 48.00,
                    'stock_quantity' => 30,
                    'attributes' => [
                        ['slug' => 'fragrance-option', 'value' => 'Signature Scent'],
                    ],
                    'measurements' => [
                        ['slug' => 'volume', 'value' => 100.0, 'unit' => 'ml'],
                        ['slug' => 'weight', 'value' => 0.35, 'unit' => 'kg'],
                        ['slug' => 'height', 'value' => 14.0, 'unit' => 'cm'],
                    ],
                ],
            ],
        ],
        [
            'name' => 'CosmoCare Pure Bloom Eau de Parfum',
            'type' => 'simple',
            'brand' => 'CosmoCare',
            'sku' => 'PROD-CC-BLOOM',
            'barcode' => 'CC-BLOOM-0075',
            'description' => 'Pure Bloom eau de parfum crafted with jasmine petals, white musk, and citrus top notes for a light floral aroma.',
            'short_description' => 'Light floral eau de parfum in a 75ml bottle.',
            'base_price' => 89.00,
            'base_sale_price' => 79.00,
            'categories' => [
                ['name' => 'Cosmetics', 'is_primary' => true],
                ['name' => 'Fragrances', 'is_primary' => false],
            ],
            'variants' => [
                [
                    'sku' => 'BLOOM-75ML',
                    'name' => 'Pure Bloom Eau de Parfum 75ml',
                    'price' => 89.00,
                    'sale_price' => 79.00,
                    'cost_price' => 32.00,
                    'stock_quantity' => 45,
                    'attributes' => [],
                    'measurements' => [
                        ['slug' => 'volume', 'value' => 75.0, 'unit' => 'ml'],
                        ['slug' => 'weight', 'value' => 0.28, 'unit' => 'kg'],
                        ['slug' => 'height', 'value' => 12.5, 'unit' => 'cm'],
                    ],
                ],
            ],
        ],
    ];

    foreach ($products as $productData) {
        $createProduct($productData);
    }

    echo "Demo catalog seeded successfully.\n";
});
