<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductAttribute;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏷️ Seeding product attributes...');

        $attributes = [
            [
                'name' => 'Color',
                'slug' => 'color',
                'description' => 'Product color selection',
                'type' => 'color',
                'is_variation' => true,
                'is_visible' => true,
                'is_required' => false,
                'sort_order' => 10,
            ],
            [
                'name' => 'Clothing Size',
                'slug' => 'clothing-size',
                'description' => 'Standard clothing sizes (S, M, L, XL, XXL)',
                'type' => 'select',
                'is_variation' => true,
                'is_visible' => true,
                'is_required' => false,
                'sort_order' => 20,
            ],
            [
                'name' => 'Storage Capacity',
                'slug' => 'storage-capacity',
                'description' => 'Internal storage capacity for electronic devices',
                'type' => 'select',
                'is_variation' => true,
                'is_visible' => true,
                'is_required' => false,
                'sort_order' => 30,
            ],
            [
                'name' => 'Shoe Size',
                'slug' => 'shoe-size',
                'description' => 'Standard UK shoe sizes',
                'type' => 'select',
                'is_variation' => true,
                'is_visible' => true,
                'is_required' => false,
                'sort_order' => 40,
            ],
        ];

        foreach ($attributes as $attributeData) {
            ProductAttribute::updateOrCreate(
                ['slug' => $attributeData['slug']],
                $attributeData
            );
        }

        $this->command->info('✅ Product attributes seeded successfully!');
    }
}
