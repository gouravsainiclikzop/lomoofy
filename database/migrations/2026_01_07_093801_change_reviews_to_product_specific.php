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
        Schema::table('reviews', function (Blueprint $table) {
            // Drop existing constraints and indexes
            $table->dropForeign(['variant_id']);
            $table->dropUnique(['variant_id', 'user_id']);
            $table->dropIndex(['variant_id', 'status']);
            
            // Drop variant_id column
            $table->dropColumn('variant_id');
            
            // Add product_id column
            $table->foreignId('product_id')->after('rating')->constrained('products')->onDelete('cascade');
            
            // Add new unique constraint: one review per user per product
            $table->unique(['product_id', 'user_id']);
            
            // Add new indexes
            $table->index(['product_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Drop new constraints and indexes
            $table->dropForeign(['product_id']);
            $table->dropUnique(['product_id', 'user_id']);
            $table->dropIndex(['product_id', 'status']);
            
            // Drop product_id column
            $table->dropColumn('product_id');
            
            // Restore variant_id column
            $table->foreignId('variant_id')->after('rating')->constrained('product_variants')->onDelete('cascade');
            
            // Restore original unique constraint
            $table->unique(['variant_id', 'user_id']);
            
            // Restore original indexes
            $table->index(['variant_id', 'status']);
        });
    }
};
