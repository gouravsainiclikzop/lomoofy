<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\ProductAttributeValue;
use App\Models\ProductAttribute;

class ResetProductDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🧹 Resetting all product-related data...');
        
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate tables in correct order (respecting foreign keys)
        $this->command->info('  → Truncating product_images...');
        DB::table('product_images')->truncate();
        
        $this->command->info('  → Truncating product_variants...');
        DB::table('product_variants')->truncate();
        
        $this->command->info('  → Truncating products...');
        DB::table('products')->truncate();
        
        $this->command->info('  → Truncating product_attribute_values...');
        DB::table('product_attribute_values')->truncate();
        
        $this->command->info('  → Truncating product_attributes...');
        DB::table('product_attributes')->truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('✅ All product-related data reset completed!');
    }
}

