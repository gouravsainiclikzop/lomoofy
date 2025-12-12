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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand_category');
    }
};
