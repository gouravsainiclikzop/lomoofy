<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Migrates existing brand_id data from categories table to brand_category pivot table.
     */
    public function up(): void
    {
        // Only migrate if brand_id column exists
        if (Schema::hasColumn('categories', 'brand_id')) {
            // Get all categories with brand_id
            $categories = DB::table('categories')
                ->whereNotNull('brand_id')
                ->select('id', 'brand_id')
                ->get();
            
            // Insert into pivot table
            foreach ($categories as $category) {
                // Check if relationship already exists (avoid duplicates)
                $exists = DB::table('brand_category')
                    ->where('brand_id', $category->brand_id)
                    ->where('category_id', $category->id)
                    ->exists();
                
                if (!$exists) {
                    DB::table('brand_category')->insert([
                        'brand_id' => $category->brand_id,
                        'category_id' => $category->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration doesn't need a down method as it's a data migration
        // The remove_brand_id migration handles the schema reversal
    }
};

