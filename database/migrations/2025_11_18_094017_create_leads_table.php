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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->decimal('value', 15, 2)->nullable()->comment('Expected deal amount');
            $table->foreignId('status_id')->nullable()->constrained('lead_statuses')->onDelete('set null');
            $table->foreignId('source_id')->nullable()->constrained('lead_sources')->onDelete('set null');
            $table->string('priority')->nullable()->comment('low, medium, high');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('status_id');
            $table->index('source_id');
            $table->index('assigned_to');
            $table->index('priority');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
