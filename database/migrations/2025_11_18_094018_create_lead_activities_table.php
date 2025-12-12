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
        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->enum('type', ['note', 'call', 'email', 'meeting', 'file', 'reminder']);
            $table->text('description');
            $table->timestamp('follow_up_date')->nullable();
            $table->foreignId('next_action_owner')->nullable()->constrained('users')->onDelete('set null');
            $table->string('file_path')->nullable()->comment('For file upload type');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes
            $table->index('lead_id');
            $table->index('type');
            $table->index('follow_up_date');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
    }
};
