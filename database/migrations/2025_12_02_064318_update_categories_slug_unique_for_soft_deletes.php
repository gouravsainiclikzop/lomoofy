<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration updates the unique constraint on slug to work with soft deletes.
     * It creates a composite unique index on (slug, deleted_at) which allows:
     * - Only one active record (deleted_at = NULL) per slug
     * - Multiple soft-deleted records can have the same slug
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Drop the existing unique constraint on slug
            $table->dropUnique(['slug']);
        });

        // Create a composite unique index on (slug, deleted_at)
        // MySQL allows multiple NULLs in unique indexes, so this works perfectly:
        // - Active records (deleted_at = NULL) must have unique slugs
        // - Soft-deleted records (deleted_at = timestamp) can have duplicate slugs
        DB::statement('CREATE UNIQUE INDEX categories_slug_deleted_at_unique ON categories (slug, deleted_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the composite unique index
        DB::statement('DROP INDEX categories_slug_deleted_at_unique ON categories');

        // Restore the original unique constraint on slug
        Schema::table('categories', function (Blueprint $table) {
            $table->unique('slug');
        });
    }
};
