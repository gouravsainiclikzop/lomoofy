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
            $table->dropColumn([
                'company_billing_address',
                'company_billing_city',
                'company_billing_state',
                'company_billing_pincode',
                'company_billing_country',
                'company_shipping_address',
                'company_shipping_city',
                'company_shipping_state',
                'company_shipping_pincode',
                'company_shipping_country',
                'company_billing_same_as_shipping',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->text('company_billing_address')->nullable();
            $table->string('company_billing_city')->nullable();
            $table->string('company_billing_state')->nullable();
            $table->string('company_billing_pincode', 10)->nullable();
            $table->string('company_billing_country')->nullable();
            $table->text('company_shipping_address')->nullable();
            $table->string('company_shipping_city')->nullable();
            $table->string('company_shipping_state')->nullable();
            $table->string('company_shipping_pincode', 10)->nullable();
            $table->string('company_shipping_country')->nullable();
            $table->boolean('company_billing_same_as_shipping')->default(false);
        });
    }
};