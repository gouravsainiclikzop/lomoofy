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
            $table->boolean('tax_type')->nullable()->after('discount_active')->comment('true = inclusive, false = exclusive');
            $table->decimal('tax_value', 10, 2)->nullable()->after('tax_type')->comment('Tax amount for this item');
            $table->decimal('tax_percentage', 5, 2)->nullable()->after('tax_value')->comment('Tax percentage (e.g., 18.00 for 18%)');
            $table->decimal('discount_percentage', 5, 2)->nullable()->after('discount_value')->comment('Discount percentage if discount_type is percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'tax_type',
                'tax_value',
                'tax_percentage',
                'discount_percentage'
            ]);
        });
    }
};
