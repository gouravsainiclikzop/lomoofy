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
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('product_variant_id')->constrained('warehouses')->onDelete('set null');
            $table->foreignId('warehouse_location_id')->nullable()->after('warehouse_id')->constrained('warehouse_locations')->onDelete('set null');
            $table->index('warehouse_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropForeign(['warehouse_location_id']);
            $table->dropIndex(['warehouse_id']);
            $table->dropColumn(['warehouse_id', 'warehouse_location_id']);
        });
    }
};
