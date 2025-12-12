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
        Schema::create('field_management_fields', function (Blueprint $table) {
            $table->id();
            $table->string('field_key')->unique();
            $table->string('label');
            $table->string('input_type'); // text, email, password, select, textarea, date, file, checkbox, radio, number, tel
            $table->string('placeholder')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('field_group')->nullable(); // basic_info, credentials, address, business, preferences, qol, internal
            $table->json('options')->nullable(); // For select, radio, checkbox options
            $table->json('conditional_rules')->nullable(); // Rules for conditional display
            $table->string('validation_rules')->nullable(); // Additional validation rules
            $table->text('help_text')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_management_fields');
    }
};
