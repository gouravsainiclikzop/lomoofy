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
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable()->index(); // For guest wishlists
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade'); // For authenticated users
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes
            $table->index(['session_id', 'customer_id']);
            $table->unique(['product_id', 'customer_id', 'session_id']); // Prevent duplicates
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
