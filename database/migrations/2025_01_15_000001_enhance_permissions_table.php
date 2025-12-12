<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration enhances the permissions table with:
     * - Module/Resource grouping for better organization
     * - Action-based permissions (view, create, update, delete, etc.)
     * - Better indexing for performance
     * - Support for future features like permission hierarchies
     */
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            // Add module/resource grouping
            $table->string('module')->nullable()->after('slug')->index();
            $table->string('resource')->nullable()->after('module')->index();
            $table->string('action')->nullable()->after('resource')->index();
            
            // Add metadata for better organization
            $table->string('group')->nullable()->after('action')->index();
            $table->integer('sort_order')->default(0)->after('group');
            $table->boolean('is_active')->default(true)->after('sort_order');
            
            // Add composite index for faster lookups
            $table->index(['module', 'resource', 'action']);
            $table->index(['module', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropIndex(['module', 'resource', 'action']);
            $table->dropIndex(['module', 'is_active']);
            $table->dropIndex(['module']);
            $table->dropIndex(['resource']);
            $table->dropIndex(['action']);
            $table->dropIndex(['group']);
            
            $table->dropColumn([
                'module',
                'resource',
                'action',
                'group',
                'sort_order',
                'is_active'
            ]);
        });
    }
};

