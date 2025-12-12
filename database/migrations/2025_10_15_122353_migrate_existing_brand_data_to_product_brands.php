<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        // Migrate existing brand_id data to product_brands table
        DB::statement("
            INSERT INTO product_brands (product_id, brand_id, is_primary, sort_order, created_at, updated_at)
            SELECT id, brand_id, true, 0, NOW(), NOW()
            FROM products 
            WHERE brand_id IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not reversible as we can't determine which brand was primary
        // The original brand_id data would be lost
    }
};