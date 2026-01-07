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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->integer('rating'); // Rating from 1 to 5
            $table->foreignId('variant_id')->constrained('product_variants')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('customers')->onDelete('cascade');
            $table->text('comment')->nullable(); // Review description/comment
            $table->enum('status', ['active', 'inactive'])->default('inactive'); // Admin can activate/deactivate reviews
            $table->timestamps();
            
            // Unique constraint: one review per user per variant
            $table->unique(['variant_id', 'user_id']);
            
            // Indexes for better query performance
            $table->index(['variant_id', 'status']);
            $table->index(['user_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
