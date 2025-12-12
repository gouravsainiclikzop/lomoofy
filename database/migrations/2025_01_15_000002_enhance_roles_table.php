<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Enhances roles table with:
     * - Hierarchy support (parent roles)
     * - Status management
     * - Better metadata
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('slug')
                ->constrained('roles')->onDelete('set null');
            $table->string('level')->default('standard')->after('parent_id')
                ->index(); // system, admin, standard, custom
            $table->boolean('is_active')->default(true)->after('level')->index();
            $table->boolean('is_system')->default(false)->after('is_active')->index();
            $table->integer('sort_order')->default(0)->after('is_system');
            $table->json('metadata')->nullable()->after('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['level']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['is_system']);
            
            $table->dropColumn([
                'parent_id',
                'level',
                'is_active',
                'is_system',
                'sort_order',
                'metadata'
            ]);
        });
    }
};

