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
        Schema::create('customer_otps', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('otp', 6);
            $table->string('purpose')->default('registration'); // registration, login, password_reset
            $table->timestamp('expires_at');
            $table->boolean('is_verified')->default(false);
            $table->string('ip_address')->nullable();
            $table->timestamps();
            
            // Index for faster lookups
            $table->index(['email', 'purpose', 'is_verified']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_otps');
    }
};
