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
            $table->string('pincode', 10)->nullable()->after('address');
            $table->string('city')->nullable()->after('pincode');
            $table->string('state')->nullable()->after('city');
            $table->string('pan_no', 20)->nullable()->after('state');
            $table->string('gst_registration_no', 50)->nullable()->after('pan_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['pincode', 'city', 'state', 'pan_no', 'gst_registration_no']);
        });
    }
};