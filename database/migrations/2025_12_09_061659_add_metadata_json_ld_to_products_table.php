<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds metadata/json_ld field for raw SEO metadata and JSON-LD structured data.
     * This is optional per the unified product structure requirements.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'metadata')) {
                $table->text('metadata')->nullable()->after('meta_keywords');
            }
            
            if (!Schema::hasColumn('products', 'json_ld')) {
                $table->json('json_ld')->nullable()->after('metadata');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'metadata')) {
                $table->dropColumn('metadata');
            }
            
            if (Schema::hasColumn('products', 'json_ld')) {
                $table->dropColumn('json_ld');
            }
        });
    }
};
