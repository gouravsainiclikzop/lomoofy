<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('Lomoof');
            $table->string('company_logo_text')->default('Lomoofy');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // Insert default values
        DB::table('company_settings')->insert([
            'company_name' => 'Lomoof',
            'company_logo_text' => 'Lomoofy',
            'phone' => '+91 9876315314',
            'email' => 'info@lomoof.com',
            'address' => '123, Main Street, Anytown, USA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};

