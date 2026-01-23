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
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->string('integration_type'); // e.g., 'email', 'payment', 'otp', 'whatsapp', 'shopify', 'analytics'
            $table->string('provider')->nullable(); // e.g., 'razorpay', 'msg91', 'twilio'
            $table->text('configuration'); // JSON data with all configuration fields
            $table->boolean('status')->default(false); // enabled/disabled
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index('integration_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integrations');
    }
};
