<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;

class ComprehensiveDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting comprehensive data seeding...');
        
        // Step 1: Clear existing data
        $this->clearExistingData();
        
        // Step 2: Seed in correct order
        $this->seedUnits();
        $this->seedProductAttributes();
        $this->seedProductAttributeValues();
        $this->seedBrands();
        $this->seedCategories();
        $this->seedProducts();
        
        $this->command->info('✅ Comprehensive data seeding completed successfully!');
    }
    
    /**
     * Clear existing data from all tables
     */
    private function clearExistingData(): void
    {
        $this->command->info('🧹 Clearing existing data...');
        
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear data in reverse dependency order
        Product::query()->forceDelete();
        Category::query()->forceDelete();
        Brand::query()->forceDelete();
        ProductAttributeValue::query()->delete();
        ProductAttribute::query()->delete();
        Unit::query()->delete();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('✅ Existing data cleared successfully!');
    }
    
    /**
     * Seed units using existing seeder
     */
    private function seedUnits(): void
    {
        $this->command->info('📏 Seeding units...');
        $this->call(ComprehensiveUnitsSeeder::class);
    }
    
    /**
     * Seed product attributes using existing seeder
     */
    private function seedProductAttributes(): void
    {
        $this->command->info('🏷️ Seeding product attributes...');
        $this->call(AttributeSeeder::class);
    }
    
    /**
     * Seed product attribute values using existing seeder
     */
    private function seedProductAttributeValues(): void
    {
        $this->command->info('📝 Seeding product attribute values...');
        $this->call(AttributeValueSeeder::class);
    }
    
    /**
     * Seed brands using existing seeder
     */
    private function seedBrands(): void
    {
        $this->command->info('🏢 Seeding brands...');
        $this->call(BrandSeeder::class);
    }
    
    /**
     * Seed categories using existing seeder
     */
    private function seedCategories(): void
    {
        $this->command->info('📂 Seeding categories...');
        $this->call(CategorySeeder::class);
    }
    
    /**
     * Seed products using existing seeder
     */
    private function seedProducts(): void
    {
        $this->command->info('📦 Seeding products...');
        $this->call(ProductSeeder::class);
    }
}
