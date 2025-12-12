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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('sku')->unique();
            $table->string('name'); // e.g., "Red - Large"
            $table->json('attributes'); // JSON: {"color": "red", "size": "large"} - Basic attributes for this variant
            
            // Pricing (required for variable products - each variant has its own pricing)
            $table->decimal('price', 10, 2)->nullable(); // null = use parent product price (for simple products)
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->timestamp('sale_price_start')->nullable();
            $table->timestamp('sale_price_end')->nullable();
            
            // Inventory (each variant manages its own stock)
            $table->boolean('manage_stock')->default(true);
            $table->integer('stock_quantity')->default(0);
            $table->enum('stock_status', ['in_stock', 'out_of_stock', 'on_backorder'])->default('in_stock');
            
            // Physical properties (dimensions - each variant can have different dimensions)
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('diameter', 8, 2)->nullable();
            $table->json('measurements')->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['product_id', 'is_active']);
            $table->index(['product_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};