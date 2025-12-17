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
        // Check if country column exists, if not add it
        if (!Schema::hasColumn('customer_addresses', 'country')) {
            Schema::table('customer_addresses', function (Blueprint $table) {
                // Add country column before state (state should always exist)
                $table->string('country')->nullable()->before('state');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('customer_addresses', 'country')) {
            Schema::table('customer_addresses', function (Blueprint $table) {
                $table->dropColumn('country');
            });
        }
    }
};
