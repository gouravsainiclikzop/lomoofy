<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if columns don't exist before adding them
        if (!Schema::hasColumn('products', 'gst_type')) {
            Schema::table('products', function (Blueprint $table) {
                // GST Type: true = Inclusive of GST, false = Exclusive of GST
                $table->boolean('gst_type')->default(true)->comment('true = Inclusive of GST, false = Exclusive of GST');
            });
        }
        
        if (!Schema::hasColumn('products', 'gst_percentage')) {
            Schema::table('products', function (Blueprint $table) {
                // GST Percentage: numeric value (3, 5, 12, 18, 28)
                $table->decimal('gst_percentage', 5, 2)->nullable()->comment('GST percentage value (e.g., 3, 5, 12, 18, 28)');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['gst_type', 'gst_percentage']);
        });
    }
};

