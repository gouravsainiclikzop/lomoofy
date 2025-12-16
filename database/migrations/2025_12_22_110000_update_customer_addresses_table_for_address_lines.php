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
        // Add address_line1 and address_line2 columns if they don't exist
        if (!Schema::hasColumn('customer_addresses', 'address_line1')) {
            Schema::table('customer_addresses', function (Blueprint $table) {
                $table->string('address_line1')->nullable()->after('address_type');
            });
        }

        if (!Schema::hasColumn('customer_addresses', 'address_line2')) {
            Schema::table('customer_addresses', function (Blueprint $table) {
                $table->string('address_line2')->nullable()->after('address_line1');
            });
        }

        // Migrate existing full_address data to address_line1
        DB::table('customer_addresses')
            ->whereNotNull('full_address')
            ->whereNull('address_line1')
            ->update([
                'address_line1' => DB::raw('full_address')
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Migrate address_line1 back to full_address before dropping
        DB::table('customer_addresses')
            ->whereNotNull('address_line1')
            ->update([
                'full_address' => DB::raw('CONCAT(COALESCE(address_line1, ""), " ", COALESCE(address_line2, ""))')
            ]);

        if (Schema::hasColumn('customer_addresses', 'address_line2')) {
            Schema::table('customer_addresses', function (Blueprint $table) {
                $table->dropColumn('address_line2');
            });
        }

        if (Schema::hasColumn('customer_addresses', 'address_line1')) {
            Schema::table('customer_addresses', function (Blueprint $table) {
                $table->dropColumn('address_line1');
            });
        }
    }
};

