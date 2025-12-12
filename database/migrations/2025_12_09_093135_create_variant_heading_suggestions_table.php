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
        Schema::create('variant_heading_suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('heading_name')->unique();
            $table->integer('usage_count')->default(0);
            $table->timestamps();
            
            $table->index('heading_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variant_heading_suggestions');
    }
};
