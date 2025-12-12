<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates direct user-permission relationship for:
     * - Overriding role permissions at user level
     * - Temporary permissions
     * - Granular access control
     */
    public function up(): void
    {
        Schema::create('permission_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->boolean('granted')->default(true); // true = granted, false = denied (override)
            $table->timestamp('expires_at')->nullable(); // For temporary permissions
            $table->timestamps();
            
            $table->unique(['user_id', 'permission_id']);
            $table->index(['user_id', 'granted']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_user');
    }
};

