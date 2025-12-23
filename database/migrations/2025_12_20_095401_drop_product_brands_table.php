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
        Schema::dropIfExists('product_brands');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: This migration drops the product_brands table
        // If you need to recreate it, you would need to run the original migration
        // We don't recreate it here as it's being permanently removed
    }
};
