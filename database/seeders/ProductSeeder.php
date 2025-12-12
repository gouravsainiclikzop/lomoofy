<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📦 Seeding products...');
        
        // Get or create necessary brands
        $brands = $this->getOrCreateBrands();
        
        // Get categories from hierarchy
        $categories = $this->getCategories();
        
        // Get attributes
        $colorAttr = ProductAttribute::where('slug', 'color')->first();
        $clothingSizeAttr = ProductAttribute::where('slug', 'clothing-size')->first();
        $storageAttr = ProductAttribute::where('slug', 'storage-capacity')->first();
        $shoeSizeAttr = ProductAttribute::where('slug', 'shoe-size')->first();
        
        // Get attribute values
        $colors = ProductAttributeValue::where('attribute_id', $colorAttr->id)->get();
        $sizes = ProductAttributeValue::where('attribute_id', $clothingSizeAttr->id)->get();
        $storageOptions = ProductAttributeValue::where('attribute_id', $storageAttr->id)->get();
        $shoeSizes = ProductAttributeValue::where('attribute_id', $shoeSizeAttr->id)->get();
        
        // ===== VARIANT PRODUCTS =====
        
        // 1. Men's Cotton Polo T-Shirt (Variant: Color + Clothing Size)
        // Category: Men → Clothing → T-Shirts → Polos
        $poloCategory = $this->findCategoryByPath(['Men', 'Clothing', 'T-Shirts', 'Polos']) 
            ?? $this->findCategoryByPath(['Men', 'Clothing']) 
            ?? ($categories['clothing'] ?? ($categories['apparel'] ?? null));
        
        $this->createVariantProduct([
            'name' => 'Men\'s Cotton Polo T-Shirt',
            'slug' => 'mens-cotton-polo-tshirt',
            'description' => 'Classic cotton polo t-shirt with comfortable fit and breathable fabric. Perfect for casual wear and everyday comfort.',
            'short_description' => 'Classic cotton polo t-shirt with comfortable fit',
            'sku' => 'POLO-001',
            'barcode' => '1000000001',
            'type' => 'simple',
            'sku_type' => 'variant',
            'brand_id' => $brands['Fashion Brand']->id,
            'category_id' => $poloCategory->id,
            'price' => null,
            'status' => 'published',
            'featured' => true,
        ], [
            'attributes' => [$colorAttr, $clothingSizeAttr],
            'color_values' => $colors,
            'size_values' => $sizes,
            'base_price' => 29.99,
        ]);
        
        // 2. Smartphone (Variant: Storage Capacity + Color)
        $smartphoneCategory = $this->getCategoryWithFallback(
            $this->findCategoryByPath(['Electronics', 'Mobile Devices', 'Smartphones'])
                ?? $this->findCategoryByPath(['Electronics', 'Mobile Devices'])
                ?? ($categories['electronics'] ?? null),
            $categories
        );
        
        $this->createVariantProduct([
            'name' => 'Smartphone',
            'slug' => 'smartphone',
            'description' => 'Latest generation smartphone with advanced camera system, long battery life, and premium build quality.',
            'short_description' => 'Latest generation smartphone with advanced features',
            'sku' => 'PHONE-001',
            'barcode' => '1000000002',
            'type' => 'simple',
            'sku_type' => 'variant',
            'brand_id' => $brands['Tech Brand']->id,
            'category_id' => $smartphoneCategory?->id,
            'price' => null,
            'status' => 'published',
            'featured' => true,
        ], [
            'attributes' => [$storageAttr, $colorAttr],
            'storage_values' => $storageOptions,
            'color_values' => $colors,
            'base_price' => 599.99,
        ]);
        
        // 3. Running Shoes (Variant: Shoe Size + Color)
        $shoesCategory = $this->findCategoryByPath(['Footwear', 'Shoes', 'Running Shoes'])
            ?? $this->findCategoryByPath(['Footwear', 'Shoes'])
            ?? ($categories['shoes'] ?? ($categories['footwear'] ?? null));
        
        $this->createVariantProduct([
            'name' => 'Running Shoes',
            'slug' => 'running-shoes',
            'description' => 'Lightweight running shoes with excellent cushioning, breathable mesh upper, and superior traction for all terrains.',
            'short_description' => 'Lightweight running shoes with excellent cushioning',
            'sku' => 'SHOE-001',
            'barcode' => '1000000003',
            'type' => 'simple',
            'sku_type' => 'variant',
            'brand_id' => $brands['Sports Brand']->id,
            'category_id' => $shoesCategory->id,
            'price' => null,
            'status' => 'published',
            'featured' => true,
        ], [
            'attributes' => [$shoeSizeAttr, $colorAttr],
            'shoe_size_values' => $shoeSizes,
            'color_values' => $colors,
            'base_price' => 79.99,
        ]);
        
        // 4. Women's Summer Dress (Variant: Color + Clothing Size)
        $dressCategory = $this->findCategoryByPath(['Women', 'Clothing', 'Dresses', 'Summer Dresses'])
            ?? $this->findCategoryByPath(['Women', 'Clothing', 'Dresses'])
            ?? $this->findCategoryByPath(['Women', 'Clothing'])
            ?? ($categories['clothing'] ?? ($categories['apparel'] ?? null));
        
        $this->createVariantProduct([
            'name' => 'Women\'s Summer Dress',
            'slug' => 'womens-summer-dress',
            'description' => 'Lightweight summer dress with floral pattern and flowing silhouette. Perfect for warm weather and casual occasions.',
            'short_description' => 'Lightweight summer dress with floral pattern',
            'sku' => 'DRESS-001',
            'barcode' => '1000000004',
            'type' => 'simple',
            'sku_type' => 'variant',
            'brand_id' => $brands['Fashion Brand']->id,
            'category_id' => $dressCategory->id,
            'price' => null,
            'status' => 'published',
            'featured' => false,
        ], [
            'attributes' => [$colorAttr, $clothingSizeAttr],
            'color_values' => $colors,
            'size_values' => $sizes,
            'base_price' => 49.99,
        ]);
        
        // ===== SINGLE SKU PRODUCTS =====
        
        // 5. Silver Vertical Bar Pendant for Men
        $jewelryCategory = $this->findCategoryByPath(['Jewelry', 'Men\'s Jewelry', 'Pendants'])
            ?? $this->findCategoryByPath(['Jewelry', 'Men\'s Jewelry'])
            ?? $this->findCategoryByPath(['Jewelry', 'Pendants'])
            ?? ($categories['jewelry'] ?? ($categories['accessories'] ?? null));
        
        $this->createSingleSkuProduct([
            'name' => 'Silver Vertical Bar Pendant for Men',
            'slug' => 'silver-vertical-bar-pendant-men',
            'description' => 'Elegant sterling silver vertical bar pendant with modern minimalist design. Perfect for special occasions and everyday wear.',
            'short_description' => 'Elegant sterling silver vertical bar pendant',
            'sku' => 'PEND-001',
            'barcode' => '1000000005',
            'type' => 'simple',
            'sku_type' => 'single',
            'brand_id' => $brands['Jewelry Brand']->id,
            'category_id' => $jewelryCategory->id,
            'price' => 89.99,
            'sale_price' => 69.99,
            'status' => 'published',
            'featured' => false,
        ]);
        
        // 6. Leather Wallet
        $walletCategory = $this->findCategoryByPath(['Accessories', 'Wallets & Card Holders'])
            ?? $this->findCategoryByPath(['Accessories'])
            ?? ($categories['accessories'] ?? null);
        
        $this->createSingleSkuProduct([
            'name' => 'Leather Wallet',
            'slug' => 'leather-wallet',
            'description' => 'Premium genuine leather wallet with multiple card slots, cash compartment, and RFID blocking technology.',
            'short_description' => 'Premium genuine leather wallet',
            'sku' => 'WALL-001',
            'barcode' => '1000000006',
            'type' => 'simple',
            'sku_type' => 'single',
            'brand_id' => $brands['Fashion Brand']->id,
            'category_id' => $walletCategory->id,
            'price' => 45.99,
            'status' => 'published',
            'featured' => false,
        ]);
        
        // 7. Stainless Steel Water Bottle
        $bottleCategory = $this->getCategoryWithFallback(
            $this->findCategoryByPath(['Sports & Outdoors', 'Fitness', 'Water Bottles'])
                ?? $this->findCategoryByPath(['Sports & Outdoors', 'Water Bottles'])
                ?? ($categories['sports'] ?? ($categories['sports-fitness'] ?? null)),
            $categories
        );
        
        $this->createSingleSkuProduct([
            'name' => 'Stainless Steel Water Bottle',
            'slug' => 'stainless-steel-water-bottle',
            'description' => 'BPA-free stainless steel water bottle with leak-proof lid, double-wall insulation, and ergonomic design. Keeps drinks cold for 24 hours.',
            'short_description' => 'BPA-free stainless steel water bottle',
            'sku' => 'BOTT-001',
            'barcode' => '1000000007',
            'type' => 'simple',
            'sku_type' => 'single',
            'brand_id' => $brands['Sports Brand']->id,
            'category_id' => $bottleCategory?->id,
            'price' => 24.99,
            'status' => 'published',
            'featured' => false,
        ]);
        
        // 8. Wireless Earbuds
        $earbudsCategory = $this->findCategoryByPath(['Electronics', 'Audio', 'Headphones', 'Earbuds'])
            ?? $this->findCategoryByPath(['Electronics', 'Audio', 'Headphones'])
            ?? $this->findCategoryByPath(['Electronics', 'Audio'])
            ?? ($categories['electronics'] ?? null);
        
        $this->createSingleSkuProduct([
            'name' => 'Wireless Earbuds',
            'slug' => 'wireless-earbuds',
            'description' => 'Premium wireless earbuds with active noise cancellation, 24-hour battery life, and crystal-clear sound quality.',
            'short_description' => 'Premium wireless earbuds with noise cancellation',
            'sku' => 'EARB-001',
            'barcode' => '1000000008',
            'type' => 'simple',
            'sku_type' => 'single',
            'brand_id' => $brands['Tech Brand']->id,
            'category_id' => $earbudsCategory->id,
            'price' => 129.99,
            'status' => 'published',
            'featured' => true,
        ]);
        
        // 9. Men's Denim Jeans (Variant: Color + Clothing Size)
        $jeansCategory = $this->findCategoryByPath(['Men', 'Clothing', 'Bottoms', 'Jeans'])
            ?? $this->findCategoryByPath(['Men', 'Clothing', 'Jeans'])
            ?? $this->findCategoryByPath(['Men', 'Clothing'])
            ?? ($categories['clothing'] ?? ($categories['apparel'] ?? null));
        
        $this->createVariantProduct([
            'name' => 'Men\'s Denim Jeans',
            'slug' => 'mens-denim-jeans',
            'description' => 'Classic fit denim jeans with stretch comfort, durable construction, and timeless style. Perfect for casual and smart-casual wear.',
            'short_description' => 'Classic fit denim jeans with stretch comfort',
            'sku' => 'JEAN-001',
            'barcode' => '1000000009',
            'type' => 'simple',
            'sku_type' => 'variant',
            'brand_id' => $brands['Fashion Brand']->id,
            'category_id' => $jeansCategory->id,
            'price' => null,
            'status' => 'published',
            'featured' => false,
        ], [
            'attributes' => [$colorAttr, $clothingSizeAttr],
            'color_values' => $colors->whereIn('value', ['Black', 'Navy']), // Only Black and Navy for jeans
            'size_values' => $sizes,
            'base_price' => 59.99,
        ]);
        
        // 10. Laptop (Variant: Storage Capacity)
        $laptopCategory = $this->findCategoryByPath(['Electronics', 'Computers', 'Laptops'])
            ?? $this->findCategoryByPath(['Electronics', 'Computers'])
            ?? ($categories['electronics'] ?? null);
        
        $this->createVariantProduct([
            'name' => 'Laptop',
            'slug' => 'laptop',
            'description' => 'High-performance laptop with fast processor, vibrant display, and long battery life. Perfect for work and entertainment.',
            'short_description' => 'High-performance laptop with fast processor',
            'sku' => 'LAPT-001',
            'barcode' => '1000000010',
            'type' => 'simple',
            'sku_type' => 'variant',
            'brand_id' => $brands['Tech Brand']->id,
            'category_id' => $laptopCategory->id,
            'price' => null,
            'status' => 'published',
            'featured' => true,
        ], [
            'attributes' => [$storageAttr],
            'storage_values' => $storageOptions,
            'base_price' => 899.99,
        ]);
        
        // 11. Canvas Backpack
        $backpackCategory = $this->findCategoryByPath(['Accessories', 'Bags & Luggage', 'Backpacks'])
            ?? $this->findCategoryByPath(['Accessories', 'Bags'])
            ?? ($categories['accessories'] ?? null);
        
        $this->createSingleSkuProduct([
            'name' => 'Canvas Backpack',
            'slug' => 'canvas-backpack',
            'description' => 'Durable canvas backpack with multiple compartments, padded laptop sleeve, and adjustable shoulder straps. Perfect for school, work, or travel.',
            'short_description' => 'Durable canvas backpack with multiple compartments',
            'sku' => 'BACK-001',
            'barcode' => '1000000011',
            'type' => 'simple',
            'sku_type' => 'single',
            'brand_id' => $brands['Fashion Brand']->id,
            'category_id' => $backpackCategory->id,
            'price' => 39.99,
            'status' => 'published',
            'featured' => false,
        ]);
        
        // 12. Fitness Tracker
        $fitnessCategory = $this->findCategoryByPath(['Electronics', 'Wearables', 'Fitness Trackers'])
            ?? $this->findCategoryByPath(['Electronics', 'Wearables'])
            ?? ($categories['electronics'] ?? null);
        
        $this->createSingleSkuProduct([
            'name' => 'Fitness Tracker',
            'slug' => 'fitness-tracker',
            'description' => 'Advanced fitness tracker with heart rate monitor, sleep tracking, step counter, and smartphone notifications. Water-resistant design.',
            'short_description' => 'Advanced fitness tracker with heart rate monitor',
            'sku' => 'FITN-001',
            'barcode' => '1000000012',
            'type' => 'simple',
            'sku_type' => 'single',
            'brand_id' => $brands['Sports Brand']->id,
            'category_id' => $fitnessCategory->id,
            'price' => 79.99,
            'status' => 'published',
            'featured' => false,
        ]);
        
        $this->command->info('✅ Products seeded successfully!');
    }
    
    /**
     * Find category by hierarchical path (e.g., ['Men', 'Clothing', 'T-Shirts'])
     */
    private function findCategoryByPath(array $path): ?Category
    {
        if (empty($path)) {
            return null;
        }
        
        $category = Category::where('name', $path[0])
            ->whereNull('parent_id')
            ->first();
        
        if (!$category) {
            return null;
        }
        
        for ($i = 1; $i < count($path); $i++) {
            $category = Category::where('name', $path[$i])
                ->where('parent_id', $category->id)
                ->first();
            
            if (!$category) {
                return null;
            }
        }
        
        return $category;
    }
    
    /**
     * Get or create necessary brands
     */
    private function getOrCreateBrands(): array
    {
        $brands = [];
        
        $brandData = [
            'Fashion Brand' => 'FashionCo',
            'Tech Brand' => 'TechPro',
            'Sports Brand' => 'SportMax',
            'Jewelry Brand' => 'JewelCraft',
        ];
        
        foreach ($brandData as $name => $slug) {
            $brands[$name] = Brand::firstOrCreate(
                ['slug' => Str::slug($slug)],
                [
                    'name' => $name,
                    'slug' => Str::slug($slug),
                    'is_active' => true,
                ]
            );
        }
        
        return $brands;
    }
    
    /**
     * Get categories by slug or name
     */
    private function getCategories(): array
    {
        $categories = [];
        
        $categorySlugs = ['clothing', 'apparel', 'electronics', 'shoes', 'footwear', 'jewelry', 'accessories', 'sports', 'sports-fitness'];
        
        foreach ($categorySlugs as $slug) {
            $category = Category::where('slug', $slug)->orWhere('name', ucfirst($slug))->first();
            if ($category) {
                $categories[$slug] = $category;
            }
        }
        
        // Get a default category as fallback
        $defaultCategory = Category::active()->first();
        if ($defaultCategory) {
            $categories['default'] = $defaultCategory;
        }
        
        return $categories;
    }
    
    /**
     * Get category with fallback
     */
    private function getCategoryWithFallback($category, array $categories): ?Category
    {
        if ($category) {
            return $category;
        }
        
        // Try fallback categories
        return $categories['default'] ?? Category::active()->first();
    }
    
    /**
     * Create a variant product with multiple variants
     */
    private function createVariantProduct(array $productData, array $variantConfig): void
    {
        $product = Product::updateOrCreate(
            ['slug' => $productData['slug']],
            $productData
        );
        
        // Attach brand
        if (isset($productData['brand_id']) && $productData['brand_id']) {
            $product->brands()->syncWithoutDetaching([
                $productData['brand_id'] => [
                    'is_primary' => true,
                    'sort_order' => 0
                ]
            ]);
        }
        
        // Attach category
        if (isset($productData['category_id']) && $productData['category_id']) {
            $product->categories()->syncWithoutDetaching([
                $productData['category_id'] => [
                    'is_primary' => true
                ]
            ]);
        }
        
        // Generate variant combinations
        $attributes = $variantConfig['attributes'];
        $combinations = $this->generateCombinations($attributes, $variantConfig);
        
        $sortOrder = 0;
        foreach ($combinations as $combination) {
            $variantName = implode(' - ', array_values($combination));
            // SKU pattern: {PARENT-SKU}-{ATTR1-VALUE}-{ATTR2-VALUE}
            $variantSku = $product->sku . '-' . strtoupper(implode('-', array_map(function($v) {
                return Str::slug($v, '');
            }, array_values($combination))));
            
            $variantPrice = $variantConfig['base_price'] + (rand(-5, 10)); // Slight price variation
            
            ProductVariant::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'sku' => $variantSku
                ],
                [
                    'name' => $variantName,
                    'attributes' => $combination,
                    'price' => round($variantPrice, 2),
                    'sale_price' => rand(0, 1) ? round($variantPrice * 0.85, 2) : null, // 15% off sometimes
                    'is_active' => true,
                    'sort_order' => $sortOrder++,
                ]
            );
        }
    }
    
    /**
     * Create a single-SKU product
     */
    private function createSingleSkuProduct(array $productData): void
    {
        $product = Product::updateOrCreate(
            ['slug' => $productData['slug']],
            $productData
        );
        
        // Attach brand
        if (isset($productData['brand_id']) && $productData['brand_id']) {
            $product->brands()->syncWithoutDetaching([
                $productData['brand_id'] => [
                    'is_primary' => true,
                    'sort_order' => 0
                ]
            ]);
        }
        
        // Attach category
        if (isset($productData['category_id']) && $productData['category_id']) {
            $product->categories()->syncWithoutDetaching([
                $productData['category_id'] => [
                    'is_primary' => true
                ]
            ]);
        }
        
        // Auto-create default variant for single-SKU products
        ProductVariant::updateOrCreate(
            [
                'product_id' => $product->id,
                'sku' => $product->sku
            ],
            [
                'name' => $product->name,
                'attributes' => [],
                'price' => $productData['price'],
                'sale_price' => $productData['sale_price'] ?? null,
                'is_active' => true,
                'sort_order' => 0,
            ]
        );
    }
    
    /**
     * Generate all combinations of attribute values
     */
    private function generateCombinations(array $attributes, array $config): array
    {
        $combinations = [[]];
        
        foreach ($attributes as $attribute) {
            $newCombinations = [];
            $values = [];
            
            if ($attribute->slug === 'color') {
                $values = $config['color_values'];
            } elseif ($attribute->slug === 'clothing-size') {
                $values = $config['size_values'];
            } elseif ($attribute->slug === 'storage-capacity') {
                $values = $config['storage_values'];
            } elseif ($attribute->slug === 'shoe-size') {
                $values = $config['shoe_size_values'];
            } else {
                continue; // Skip if attribute not found in config
            }
            
            foreach ($combinations as $combination) {
                foreach ($values as $value) {
                    $newCombination = $combination;
                    $newCombination[$attribute->name] = $value->value;
                    $newCombinations[] = $newCombination;
                }
            }
            
            $combinations = $newCombinations;
        }
        
        return $combinations;
    }
}
