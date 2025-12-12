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
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained('shipping_zones')->onDelete('cascade');
            $table->foreignId('shipping_method_id')->constrained('shipping_methods')->onDelete('cascade');
            $table->enum('rate_type', ['flat_rate', 'weight_based', 'price_based', 'distance_based'])->default('flat_rate');
            $table->decimal('rate', 10, 2)->default(0); // Base rate or flat rate
            $table->decimal('rate_per_kg', 10, 2)->nullable(); // For weight-based: rate per kilogram
            $table->decimal('rate_percentage', 5, 2)->nullable(); // For price-based: percentage of order total
            $table->decimal('min_value', 10, 2)->nullable(); // Minimum weight/price for this rate
            $table->decimal('max_value', 10, 2)->nullable(); // Maximum weight/price for this rate
            $table->decimal('fragile_surcharge', 10, 2)->default(0); // Additional charge for fragile items
            $table->decimal('oversized_surcharge', 10, 2)->default(0); // Additional charge for oversized items
            $table->decimal('hazardous_surcharge', 10, 2)->default(0); // Additional charge for hazardous items
            $table->decimal('express_surcharge', 10, 2)->default(0); // Additional charge for express shipping
            $table->decimal('free_shipping_threshold', 10, 2)->nullable(); // Free shipping if order total exceeds this
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            
            // Indexes
            $table->index('shipping_zone_id');
            $table->index('shipping_method_id');
            $table->index('rate_type');
            $table->index('status');
            $table->unique(['shipping_zone_id', 'shipping_method_id', 'rate_type'], 'unique_zone_method_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
