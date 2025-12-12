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
        Schema::create('lead_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('lead_tag_id')->constrained('lead_tags')->onDelete('cascade');
            $table->timestamps();
            
            // Ensure unique combination
            $table->unique(['lead_id', 'lead_tag_id']);
            
            // Indexes
            $table->index('lead_id');
            $table->index('lead_tag_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_tag');
    }
};
