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
        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('warehouse_location_id')->nullable()->constrained('warehouse_locations')->onDelete('set null');
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0)->comment('Quantity reserved for pending orders');
            $table->integer('available_quantity')->virtualAs('quantity - reserved_quantity');
            $table->timestamps();
            
            // Indexes
            $table->index('product_variant_id');
            $table->index('warehouse_id');
            $table->index('warehouse_location_id');
            $table->unique(['product_variant_id', 'warehouse_id', 'warehouse_location_id'], 'unique_stock_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_stocks');
    }
};
