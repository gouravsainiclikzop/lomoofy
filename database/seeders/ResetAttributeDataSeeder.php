<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ProductVariant;
use App\Models\ProductAttributeValue;
use App\Models\ProductAttribute;

class ResetAttributeDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🧹 Resetting attribute-related data...');
        
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate tables in correct order (respecting foreign keys)
        ProductVariant::query()->truncate();
        ProductAttributeValue::query()->truncate();
        ProductAttribute::query()->truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('✅ Attribute-related data reset completed!');
    }
}
