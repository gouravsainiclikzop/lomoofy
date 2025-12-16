<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            // Add gst_type column if it doesn't exist
            if (!Schema::hasColumn('products', 'gst_type')) {
                DB::statement('ALTER TABLE products ADD COLUMN gst_type TINYINT(1) DEFAULT 1 COMMENT "true = Inclusive of GST, false = Exclusive of GST"');
            }
        } catch (\Exception $e) {
            // Column might already exist, ignore error
            if (!str_contains($e->getMessage(), 'Duplicate column name')) {
                throw $e;
            }
        }
        
        try {
            // Add gst_percentage column if it doesn't exist
            if (!Schema::hasColumn('products', 'gst_percentage')) {
                DB::statement('ALTER TABLE products ADD COLUMN gst_percentage DECIMAL(5,2) NULL COMMENT "GST percentage value (e.g., 3, 5, 12, 18, 28)"');
            }
        } catch (\Exception $e) {
            // Column might already exist, ignore error
            if (!str_contains($e->getMessage(), 'Duplicate column name')) {
                throw $e;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('products', 'gst_type')) {
            DB::statement('ALTER TABLE products DROP COLUMN gst_type');
        }
        
        if (Schema::hasColumn('products', 'gst_percentage')) {
            DB::statement('ALTER TABLE products DROP COLUMN gst_percentage');
        }
    }
};

