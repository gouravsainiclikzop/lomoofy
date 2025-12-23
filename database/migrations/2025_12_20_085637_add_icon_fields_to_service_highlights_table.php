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
        Schema::table('service_highlights', function (Blueprint $table) {
            $table->string('highlight1_icon')->nullable()->after('highlight1_text');
            $table->string('highlight2_icon')->nullable()->after('highlight2_text');
            $table->string('highlight3_icon')->nullable()->after('highlight3_text');
            $table->string('highlight4_icon')->nullable()->after('highlight4_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_highlights', function (Blueprint $table) {
            $table->dropColumn(['highlight1_icon', 'highlight2_icon', 'highlight3_icon', 'highlight4_icon']);
        });
    }
};
