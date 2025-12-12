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
        Schema::create('product_static_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('attribute_id')->constrained('product_attributes')->onDelete('cascade');
            $table->foreignId('value_id')->nullable()->constrained('product_attribute_values')->onDelete('cascade');
            $table->text('custom_value')->nullable(); // For text, number, boolean, date, file types
            $table->timestamps();
            
            // Ensure one value per product-attribute combination
            $table->unique(['product_id', 'attribute_id']);
            
            // Indexes for performance
            $table->index('product_id');
            $table->index('attribute_id');
            $table->index('value_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_static_attributes');
    }
};
