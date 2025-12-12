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
        Schema::create('product_category_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_attribute_id')->constrained('category_attributes')->onDelete('cascade');
            $table->text('value'); // Store as text, can be JSON for multiselect
            $table->timestamps();
            
            // Indexes
            $table->index(['product_id', 'category_attribute_id'], 'pca_values_product_attr_index');
            $table->unique(['product_id', 'category_attribute_id'], 'pca_values_product_attr_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_category_attribute_values');
    }
};