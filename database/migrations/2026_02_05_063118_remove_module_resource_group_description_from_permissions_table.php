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
        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'module')) {
                $table->dropColumn('module');
            }
            if (Schema::hasColumn('permissions', 'resource')) {
                $table->dropColumn('resource');
            }
            if (Schema::hasColumn('permissions', 'group')) {
                $table->dropColumn('group');
            }
            if (Schema::hasColumn('permissions', 'description')) {
                $table->dropColumn('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('module')->nullable()->after('slug');
            $table->string('resource')->nullable()->after('module');
            $table->string('group')->nullable()->after('resource');
            $table->text('description')->nullable()->after('name');
        });
    }
};
