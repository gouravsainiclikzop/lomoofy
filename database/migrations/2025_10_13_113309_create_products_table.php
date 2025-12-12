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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable();
            
            // Product Type: simple, variable, digital, service, bundle, subscription
            $table->enum('type', ['simple', 'variable', 'digital', 'service', 'bundle', 'subscription'])->default('simple');
            
            // Pricing (nullable for variable products - pricing will be on variants)
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->timestamp('sale_price_start')->nullable();
            $table->timestamp('sale_price_end')->nullable();
            
            // Inventory (nullable for variable products - inventory will be on variants)
            $table->boolean('manage_stock')->nullable()->default(true);
            $table->integer('stock_quantity')->nullable()->default(0);
            $table->enum('stock_status', ['in_stock', 'out_of_stock', 'on_backorder'])->nullable()->default('in_stock');
            $table->boolean('allow_backorder')->default(false);
            
            // Physical properties (nullable - dimensions will be on variants for variable products)
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            
            // Shipping
            $table->boolean('requires_shipping')->default(true);
            $table->boolean('free_shipping')->default(false);
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            
            // Status and visibility
            $table->enum('status', ['published', 'hidden'])->default('hidden');
            $table->timestamp('published_at')->nullable();
            $table->boolean('featured')->default(false);
            
            // Digital product specific
            $table->string('download_limit')->nullable(); // null = unlimited
            $table->string('download_expiry')->nullable(); // days, null = never expires
            
            // Bundle product specific
            $table->json('bundle_items')->nullable(); // JSON array of bundled product IDs with quantities
            
            // Subscription product specific
            $table->enum('subscription_period', ['day', 'week', 'month', 'year'])->nullable();
            $table->integer('subscription_interval')->nullable(); // e.g., 1 for monthly, 3 for every 3 months
            $table->integer('subscription_length')->nullable(); // null = indefinite
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['status', 'published_at']);
            $table->index(['type', 'status']);
            $table->index('featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};