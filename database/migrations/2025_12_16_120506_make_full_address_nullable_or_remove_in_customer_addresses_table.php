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
        // Check if full_address column exists and drop it
        if (Schema::hasColumn('customer_addresses', 'full_address')) {
            Schema::table('customer_addresses', function (Blueprint $table) {
                $table->dropColumn('full_address');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate full_address column if rolling back
        if (!Schema::hasColumn('customer_addresses', 'full_address')) {
            Schema::table('customer_addresses', function (Blueprint $table) {
                // Add it before state (state should always exist)
                $table->text('full_address')->nullable()->before('state');
            });
        }
    }
};
