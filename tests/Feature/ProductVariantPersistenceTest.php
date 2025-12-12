<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductVariantPersistenceTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    protected function createBrand(): Brand
    {
        return Brand::create([
            'name' => 'Acme Brand',
            'slug' => 'acme-brand',
            'description' => 'Test brand',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    protected function createCategoryHierarchy(Brand $brand): array
    {
        $parent = Category::create([
            'name' => 'Parent Category',
            'slug' => 'parent-category',
            'brand_id' => $brand->id,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $child = Category::create([
            'name' => 'Child Category',
            'slug' => 'child-category',
            'brand_id' => $brand->id,
            'parent_id' => $parent->id,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        return [$parent, $child];
    }

    protected function createMeasurementSupport(): array
    {
        $attribute = ProductAttribute::create([
            'name' => 'Test Weight',
            'slug' => 'test-weight',
            'type' => 'text',
            'is_required' => false,
            'is_variation' => false,
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        $unit = Unit::create([
            'name' => 'Gram',
            'symbol' => 'g',
            'type' => 'weight',
            'is_active' => true,
        ]);

        return [$attribute, $unit];
    }

    public function test_product_creation_persists_variant_discounts_measurements_images_and_categories(): void
    {
        Storage::fake('public');
        $this->authenticate();

        $brand = $this->createBrand();
        [$parentCategory, $childCategory] = $this->createCategoryHierarchy($brand);
        [$attribute, $unit] = $this->createMeasurementSupport();

        $measurementPayload = [[
            'attribute_id' => $attribute->id,
            'attribute_name' => $attribute->name,
            'attribute_slug' => $attribute->slug,
            'value' => 250,
            'unit_id' => $unit->id,
            'unit_name' => $unit->name,
            'unit_symbol' => $unit->symbol,
            'unit_type' => $unit->type,
        ]];

        $variantImage = UploadedFile::fake()->create('variant-one.jpg', 100, 'image/jpeg');

        $payload = [
            'name' => 'Test Product',
            'sku' => 'SKU12345',
            'status' => 'published',
            'type' => 'variable',
            'stock_status' => 'in_stock',
            'brand_ids' => [$brand->id],
            'primary_category_ids' => [$parentCategory->id],
            'category_ids' => [$childCategory->id],
            'primary_category' => $parentCategory->id,
            'seo_url_slug' => 'test-product',
            'variants' => [[
                'sku' => 'VARSKU123',
                'price' => 200,
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'discount_active' => 1,
                'cost_price' => 120,
                'stock_quantity' => 5,
                'is_active' => 1,
                'measurements' => $measurementPayload,
                'images' => [$variantImage],
            ]],
        ];

        $response = $this->post(route('products.store'), $payload, ['HTTP_ACCEPT' => 'application/json']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $productId = $response->json('product.id');
        $this->assertNotNull($productId, 'Product ID should be present in response');

        /** @var Product $product */
        $product = Product::with(['variants.images', 'categories'])->findOrFail($productId);
        $this->assertCount(1, $product->variants);

        /** @var ProductVariant $variant */
        $variant = $product->variants->first();
        $this->assertEquals('percentage', $variant->discount_type);
        $this->assertTrue($variant->discount_active);
        $this->assertEquals(10.00, (float) $variant->discount_value);
        $this->assertEquals(180.0, (float) $variant->sale_price);

        $this->assertEqualsCanonicalizing($measurementPayload, $variant->measurements);

        $this->assertNotNull($variant->image);
        $this->assertTrue(Storage::disk('public')->exists($variant->image));

        $this->assertCount(1, $variant->images);
        $this->assertEquals($variant->id, $variant->images->first()->product_variant_id);
        $this->assertTrue(Storage::disk('public')->exists($variant->images->first()->image_path));

        $categoryIds = $product->categories->pluck('id')->sort()->values()->all();
        $this->assertEqualsCanonicalizing([$parentCategory->id, $childCategory->id], $categoryIds);
        $this->assertTrue(
            (bool) $product->categories->firstWhere('id', $parentCategory->id)->pivot->is_primary,
            'Parent category should be marked as primary'
        );
    }

    public function test_product_update_resets_variants_and_categories(): void
    {
        Storage::fake('public');
        $this->authenticate();

        $brand = $this->createBrand();
        [$parentCategory, $childCategory] = $this->createCategoryHierarchy($brand);
        [$attribute, $unit] = $this->createMeasurementSupport();

        $initialPayload = [
            'name' => 'Initial Product',
            'sku' => 'SKUINIT',
            'status' => 'published',
            'type' => 'variable',
            'stock_status' => 'in_stock',
            'brand_ids' => [$brand->id],
            'primary_category_ids' => [$parentCategory->id],
            'category_ids' => [$childCategory->id],
            'primary_category' => $parentCategory->id,
            'seo_url_slug' => 'initial-product',
            'variants' => [[
                'sku' => 'VARINIT',
                'price' => 150,
                'stock_quantity' => 10,
                'measurements' => [],
                'images' => [UploadedFile::fake()->create('variant-init.jpg', 100, 'image/jpeg')],
            ]],
        ];

        $createResponse = $this->post(route('products.store'), $initialPayload, ['HTTP_ACCEPT' => 'application/json']);
        $createResponse->assertStatus(200);
        $productId = $createResponse->json('product.id');
        $product = Product::with('variants')->findOrFail($productId);
        $oldVariantImagePath = $product->variants->first()->image;

        $newChild = Category::create([
            'name' => 'Replacement Category',
            'slug' => 'replacement-category',
            'brand_id' => $brand->id,
            'parent_id' => $parentCategory->id,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $newMeasurementPayload = [[
            'attribute_id' => $attribute->id,
            'attribute_name' => $attribute->name,
            'attribute_slug' => $attribute->slug,
            'value' => 99,
            'unit_id' => $unit->id,
            'unit_name' => $unit->name,
            'unit_symbol' => $unit->symbol,
            'unit_type' => $unit->type,
        ]];

        $updatePayload = [
            'name' => 'Updated Product',
            'sku' => 'SKUUPDATED',
            'status' => 'published',
            'type' => 'variable',
            'stock_status' => 'in_stock',
            'brand_ids' => [$brand->id],
            'primary_category_ids' => [$parentCategory->id],
            'category_ids' => [$newChild->id],
            'primary_category' => $parentCategory->id,
            'seo_url_slug' => 'updated-product',
            'variants' => [[
                'sku' => 'VARUPDATED',
                'price' => 120,
                'discount_type' => 'amount',
                'discount_value' => 15,
                'discount_active' => 1,
                'stock_quantity' => 3,
                'measurements' => $newMeasurementPayload,
                'images' => [UploadedFile::fake()->create('variant-updated.jpg', 100, 'image/jpeg')],
            ]],
        ];

        $updateResponse = $this->post(route('products.update', $productId), $updatePayload, ['HTTP_ACCEPT' => 'application/json']);
        $updateResponse->assertStatus(200);

        $product->refresh()->load(['variants.images', 'categories']);
        $this->assertCount(1, $product->variants);
        $updatedVariant = $product->variants->first();

        $this->assertEquals('amount', $updatedVariant->discount_type);
        $this->assertTrue($updatedVariant->discount_active);
        $this->assertEquals(15.00, (float) $updatedVariant->discount_value);
        $this->assertEquals(105.0, (float) $updatedVariant->sale_price);
        $this->assertEqualsCanonicalizing($newMeasurementPayload, $updatedVariant->measurements);

        $this->assertNotEquals($oldVariantImagePath, $updatedVariant->image);
        if ($oldVariantImagePath) {
            $this->assertFalse(Storage::disk('public')->exists($oldVariantImagePath));
        }
        $this->assertTrue(Storage::disk('public')->exists($updatedVariant->image));

        $this->assertCount(2, $product->categories);
        $this->assertTrue($product->categories->contains('id', $parentCategory->id));
        $this->assertTrue($product->categories->contains('id', $newChild->id));
        $this->assertTrue(
            (bool) $product->categories->firstWhere('id', $parentCategory->id)->pivot->is_primary,
            'Parent category should remain primary after update'
        );
        $this->assertFalse(
            (bool) $product->categories->firstWhere('id', $newChild->id)->pivot->is_primary,
            'New child category should not be marked as primary'
        );
    }
}
