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
        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('secondary_email')->nullable()->after('email');
            $table->string('customer_care_phone')->nullable()->after('phone');
            $table->string('careers_phone')->nullable()->after('customer_care_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['secondary_email', 'customer_care_phone', 'careers_phone']);
        });
    }
};
