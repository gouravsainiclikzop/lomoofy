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
     * Note: This migration should run AFTER the data migration script
     * that moves existing brand_id data to the brand_category pivot table.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Check if brand_id column exists before trying to drop
            if (Schema::hasColumn('categories', 'brand_id')) {
                // Try to drop foreign key constraint if it exists
                // Check for foreign key constraint names
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'categories' 
                    AND COLUMN_NAME = 'brand_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                foreach ($foreignKeys as $fk) {
                    try {
                        DB::statement("ALTER TABLE categories DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                    } catch (\Exception $e) {
                        // Foreign key might not exist, continue
                    }
                }
                
                // Try to drop the composite index if it exists
                try {
                    $table->dropIndex(['brand_id', 'parent_id']);
                } catch (\Exception $e) {
                    // Index might not exist or have different name, continue
                }
                
                // Drop the column
                $table->dropColumn('brand_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('id')->constrained('brands')->onDelete('cascade');
            $table->index(['brand_id', 'parent_id']);
        });
    }
};
