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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->string('alternate_phone')->nullable();
            $table->string('email')->unique();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('password');
            $table->string('profile_image')->nullable();
            $table->string('preferred_contact_method')->nullable(); // call, sms, whatsapp, email
            $table->string('preferred_payment_method')->nullable();
            $table->string('preferred_delivery_slot')->nullable();
            $table->boolean('newsletter_opt_in')->default(false);
            $table->json('tags')->nullable();
            $table->json('risk_flags')->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_data')->nullable(); // For storing additional dynamic field data
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
