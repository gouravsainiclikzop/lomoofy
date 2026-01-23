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
        Schema::create('legal_pages', function (Blueprint $table) {
            $table->id();
            $table->text('terms_conditions')->nullable();
            $table->boolean('terms_conditions_status')->default(true);
            $table->text('shipping')->nullable();
            $table->boolean('shipping_status')->default(true);
            $table->text('cancellation_refund')->nullable();
            $table->boolean('cancellation_refund_status')->default(true);
            $table->text('return_refund_policy')->nullable();
            $table->boolean('return_refund_policy_status')->default(true);
            $table->text('privacy_policy')->nullable();
            $table->boolean('privacy_policy_status')->default(true);
            $table->text('disclaimer')->nullable();
            $table->boolean('disclaimer_status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_pages');
    }
};
