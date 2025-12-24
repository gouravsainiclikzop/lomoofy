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
            // Add source field if it doesn't exist
            if (!Schema::hasColumn('orders', 'source')) {
                $table->string('source')->default('frontend')->after('order_number');
            }
            
            // Shipping address snapshot (immutable)
            $table->json('shipping_address')->nullable()->after('notes');
            
            // Billing address snapshot (immutable) 
            $table->json('billing_address')->nullable()->after('shipping_address');
            
            // Flag to indicate if billing address is same as shipping
            $table->boolean('billing_same_as_shipping')->default(false)->after('billing_address');
            
            // Reference to original address IDs (for tracking purposes only)
            $table->unsignedBigInteger('shipping_address_id')->nullable()->after('billing_same_as_shipping');
            $table->unsignedBigInteger('billing_address_id')->nullable()->after('shipping_address_id');
            
            // Add indexes for performance
            $table->index('shipping_address_id');
            $table->index('billing_address_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['shipping_address_id']);
            $table->dropIndex(['billing_address_id']);
            $table->dropColumn([
                'shipping_address',
                'billing_address', 
                'billing_same_as_shipping',
                'shipping_address_id',
                'billing_address_id'
            ]);
        });
    }
};
