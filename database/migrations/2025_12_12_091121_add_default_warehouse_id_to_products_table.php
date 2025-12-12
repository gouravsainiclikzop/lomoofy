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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('default_warehouse_id')->nullable()->after('category_id')->constrained('warehouses')->onDelete('set null');
            $table->index('default_warehouse_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['default_warehouse_id']);
            $table->dropIndex(['default_warehouse_id']);
            $table->dropColumn('default_warehouse_id');
        });
    }
};
