<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;

class AttributeValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📝 Seeding product attribute values...');

        // Color attribute values
        $colorAttribute = ProductAttribute::where('slug', 'color')->first();
        if ($colorAttribute) {
            $colors = [
                ['value' => 'White', 'color_code' => '#FFFFFF', 'sort_order' => 1],
                ['value' => 'Black', 'color_code' => '#000000', 'sort_order' => 2],
                ['value' => 'Olive', 'color_code' => '#808000', 'sort_order' => 3],
                ['value' => 'Navy', 'color_code' => '#000080', 'sort_order' => 4],
            ];
            
            foreach ($colors as $colorData) {
                ProductAttributeValue::updateOrCreate(
                    ['attribute_id' => $colorAttribute->id, 'value' => $colorData['value']],
                    $colorData
                );
            }
        }

        // Clothing Size attribute values
        $clothingSizeAttribute = ProductAttribute::where('slug', 'clothing-size')->first();
        if ($clothingSizeAttribute) {
            $sizes = [
                ['value' => 'S', 'sort_order' => 1],
                ['value' => 'M', 'sort_order' => 2],
                ['value' => 'L', 'sort_order' => 3],
                ['value' => 'XL', 'sort_order' => 4],
                ['value' => 'XXL', 'sort_order' => 5],
            ];
            
            foreach ($sizes as $sizeData) {
                ProductAttributeValue::updateOrCreate(
                    ['attribute_id' => $clothingSizeAttribute->id, 'value' => $sizeData['value']],
                    $sizeData
                );
            }
        }

        // Storage Capacity attribute values
        $storageAttribute = ProductAttribute::where('slug', 'storage-capacity')->first();
        if ($storageAttribute) {
            $storageOptions = [
                ['value' => '64GB', 'sort_order' => 1],
                ['value' => '128GB', 'sort_order' => 2],
                ['value' => '256GB', 'sort_order' => 3],
            ];
            
            foreach ($storageOptions as $storageData) {
                ProductAttributeValue::updateOrCreate(
                    ['attribute_id' => $storageAttribute->id, 'value' => $storageData['value']],
                    $storageData
                );
            }
        }

        // Shoe Size attribute values
        $shoeSizeAttribute = ProductAttribute::where('slug', 'shoe-size')->first();
        if ($shoeSizeAttribute) {
            $shoeSizes = [
                ['value' => 'UK7', 'sort_order' => 1],
                ['value' => 'UK8', 'sort_order' => 2],
                ['value' => 'UK9', 'sort_order' => 3],
            ];
            
            foreach ($shoeSizes as $shoeSizeData) {
                ProductAttributeValue::updateOrCreate(
                    ['attribute_id' => $shoeSizeAttribute->id, 'value' => $shoeSizeData['value']],
                    $shoeSizeData
                );
            }
        }

        $this->command->info('✅ Product attribute values seeded successfully!');
    }
}
