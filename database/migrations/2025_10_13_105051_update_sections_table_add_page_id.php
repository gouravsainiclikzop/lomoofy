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
        if (Schema::hasColumn('sections', 'page_url') && Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('sections', function (Blueprint $table) {
                $table->dropColumn('page_url');
            });
        }

        if (!Schema::hasColumn('sections', 'page_id')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->foreignId('page_id')->after('id')->constrained()->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sections', 'page_id')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->dropForeign(['page_id']);
                $table->dropColumn('page_id');
            });
        }

        if (!Schema::hasColumn('sections', 'page_url')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->string('page_url')->after('id');
            });
        }
    }
};
