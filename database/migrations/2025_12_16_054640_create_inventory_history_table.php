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
        Schema::create('inventory_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->foreignId('warehouse_location_id')->nullable()->constrained('warehouse_locations')->onDelete('set null');
            $table->integer('previous_quantity')->default(0);
            $table->integer('new_quantity')->default(0);
            $table->integer('quantity_change')->default(0)->comment('Difference: new_quantity - previous_quantity');
            $table->enum('change_type', ['set', 'increment', 'decrement', 'adjustment'])->default('set');
            $table->string('reference_type')->nullable()->comment('manual, order, import, bulk_add, etc.');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('ID of related record (e.g., order_id)');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes
            $table->index('product_variant_id');
            $table->index('warehouse_id');
            $table->index('created_at');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_history');
    }
};
