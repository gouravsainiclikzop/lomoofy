<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Drops the brand_category pivot table as categories no longer have brand associations.
     */
    public function up(): void
    {
        Schema::dropIfExists('brand_category');
    }

    /**
     * Reverse the migrations.
     * 
     * Recreates the brand_category pivot table if needed for rollback.
     */
    public function down(): void
    {
        if (!Schema::hasTable('brand_category')) {
            Schema::create('brand_category', function (Blueprint $table) {
                $table->id();
                $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
                $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
                $table->timestamps();
                
                // Ensure unique combination of brand and category
                $table->unique(['brand_id', 'category_id']);
                
                // Indexes for performance
                $table->index('brand_id');
                $table->index('category_id');
            });
        }
    }
};
