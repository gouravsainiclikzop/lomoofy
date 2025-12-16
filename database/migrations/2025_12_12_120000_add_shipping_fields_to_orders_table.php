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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shipping_zone_id')->nullable()->after('shipping_amount')->constrained('shipping_zones')->onDelete('set null');
            $table->foreignId('shipping_method_id')->nullable()->after('shipping_zone_id')->constrained('shipping_methods')->onDelete('set null');
            $table->foreignId('shipping_rate_id')->nullable()->after('shipping_method_id')->constrained('shipping_rates')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['shipping_zone_id']);
            $table->dropForeign(['shipping_method_id']);
            $table->dropForeign(['shipping_rate_id']);
            $table->dropColumn(['shipping_zone_id', 'shipping_method_id', 'shipping_rate_id']);
        });
    }
};

