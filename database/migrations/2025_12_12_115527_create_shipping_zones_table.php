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
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', ['pincode', 'state', 'city', 'country'])->default('pincode');
            $table->text('description')->nullable();
            $table->json('pincodes')->nullable(); // Array of pincodes or ranges
            $table->json('states')->nullable(); // Array of state names
            $table->json('cities')->nullable(); // Array of city names
            $table->string('country')->default('India');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            // Indexes
            $table->index('code');
            $table->index('type');
            $table->index('status');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_zones');
    }
};
