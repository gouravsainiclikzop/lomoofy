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
        // Copy sort_order values to sort_no if sort_no is null or 0
        if (Schema::hasColumn('permissions', 'sort_order') && Schema::hasColumn('permissions', 'sort_no')) {
            DB::statement('UPDATE permissions SET sort_no = sort_order WHERE sort_no IS NULL OR sort_no = 0');
        }
        
        // Drop sort_order column
        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('permissions', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('group');
            }
        });
        
        // Copy sort_no values back to sort_order
        if (Schema::hasColumn('permissions', 'sort_no') && Schema::hasColumn('permissions', 'sort_order')) {
            DB::statement('UPDATE permissions SET sort_order = sort_no WHERE sort_no IS NOT NULL');
        }
    }
};
