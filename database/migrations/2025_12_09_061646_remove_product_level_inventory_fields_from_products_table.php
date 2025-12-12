<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Removes product-level inventory, pricing, and dimension fields.
     * These fields should only exist at the variant level per the unified product structure.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Remove SKU and barcode (variant-level only)
            if (Schema::hasColumn('products', 'sku')) {
                $table->dropUnique(['sku']);
                $table->dropColumn('sku');
            }
            
            if (Schema::hasColumn('products', 'barcode')) {
                $table->dropColumn('barcode');
            }
            
            // Remove pricing fields (variant-level only)
            if (Schema::hasColumn('products', 'price')) {
                $table->dropColumn('price');
            }
            
            if (Schema::hasColumn('products', 'sale_price')) {
                $table->dropColumn('sale_price');
            }
            
            if (Schema::hasColumn('products', 'sale_price_start')) {
                $table->dropColumn('sale_price_start');
            }
            
            if (Schema::hasColumn('products', 'sale_price_end')) {
                $table->dropColumn('sale_price_end');
            }
            
            // Remove inventory fields (variant-level only)
            if (Schema::hasColumn('products', 'manage_stock')) {
                $table->dropColumn('manage_stock');
            }
            
            if (Schema::hasColumn('products', 'stock_quantity')) {
                $table->dropColumn('stock_quantity');
            }
            
            if (Schema::hasColumn('products', 'stock_status')) {
                $table->dropColumn('stock_status');
            }
            
            if (Schema::hasColumn('products', 'allow_backorder')) {
                $table->dropColumn('allow_backorder');
            }
            
            if (Schema::hasColumn('products', 'low_stock_threshold')) {
                $table->dropColumn('low_stock_threshold');
            }
            
            // Remove dimension fields (variant-level only)
            if (Schema::hasColumn('products', 'weight')) {
                $table->dropColumn('weight');
            }
            
            if (Schema::hasColumn('products', 'length')) {
                $table->dropColumn('length');
            }
            
            if (Schema::hasColumn('products', 'width')) {
                $table->dropColumn('width');
            }
            
            if (Schema::hasColumn('products', 'height')) {
                $table->dropColumn('height');
            }
            
            if (Schema::hasColumn('products', 'diameter')) {
                $table->dropColumn('diameter');
            }
            
            // Remove product type and SKU type (unified structure doesn't need these)
            if (Schema::hasColumn('products', 'type')) {
                $table->dropColumn('type');
            }
            
            if (Schema::hasColumn('products', 'sku_type')) {
                $table->dropColumn('sku_type');
            }
            
            // Remove cost_price if it exists (variant-level only)
            if (Schema::hasColumn('products', 'cost_price')) {
                $table->dropColumn('cost_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     * 
     * Note: This restoration may not be perfect as some data may have been lost.
     * Use with caution in production.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Restore SKU (nullable since variants now handle it)
            if (!Schema::hasColumn('products', 'sku')) {
                $table->string('sku')->nullable()->unique()->after('short_description');
            }
            
            // Restore barcode
            if (!Schema::hasColumn('products', 'barcode')) {
                $table->string('barcode')->nullable()->after('sku');
            }
            
            // Restore pricing fields (nullable for backward compatibility)
            if (!Schema::hasColumn('products', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('category_id');
            }
            
            if (!Schema::hasColumn('products', 'sale_price')) {
                $table->decimal('sale_price', 10, 2)->nullable()->after('price');
            }
            
            if (!Schema::hasColumn('products', 'sale_price_start')) {
                $table->timestamp('sale_price_start')->nullable()->after('sale_price');
            }
            
            if (!Schema::hasColumn('products', 'sale_price_end')) {
                $table->timestamp('sale_price_end')->nullable()->after('sale_price_start');
            }
            
            // Restore inventory fields
            if (!Schema::hasColumn('products', 'manage_stock')) {
                $table->boolean('manage_stock')->nullable()->default(true)->after('sale_price_end');
            }
            
            if (!Schema::hasColumn('products', 'stock_quantity')) {
                $table->integer('stock_quantity')->nullable()->default(0)->after('manage_stock');
            }
            
            if (!Schema::hasColumn('products', 'stock_status')) {
                $table->enum('stock_status', ['in_stock', 'out_of_stock', 'on_backorder'])->nullable()->default('in_stock')->after('stock_quantity');
            }
            
            if (!Schema::hasColumn('products', 'allow_backorder')) {
                $table->boolean('allow_backorder')->default(false)->after('stock_status');
            }
            
            // Restore dimension fields
            if (!Schema::hasColumn('products', 'weight')) {
                $table->decimal('weight', 8, 2)->nullable()->after('allow_backorder');
            }
            
            if (!Schema::hasColumn('products', 'length')) {
                $table->decimal('length', 8, 2)->nullable()->after('weight');
            }
            
            if (!Schema::hasColumn('products', 'width')) {
                $table->decimal('width', 8, 2)->nullable()->after('length');
            }
            
            if (!Schema::hasColumn('products', 'height')) {
                $table->decimal('height', 8, 2)->nullable()->after('width');
            }
            
            // Restore product type
            if (!Schema::hasColumn('products', 'type')) {
                $table->enum('type', ['simple', 'variable', 'digital', 'service', 'bundle', 'subscription'])->default('simple')->after('barcode');
            }
            
            if (!Schema::hasColumn('products', 'sku_type')) {
                $table->enum('sku_type', ['single', 'variant'])->default('single')->after('type');
            }
        });
    }
};
