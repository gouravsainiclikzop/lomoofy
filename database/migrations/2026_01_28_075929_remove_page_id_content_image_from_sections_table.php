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
        // Get the actual foreign key name from database
        $foreignKeyName = null;
        if (Schema::hasColumn('sections', 'page_id')) {
            $foreignKeys = \DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'sections' 
                AND COLUMN_NAME = 'page_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            if (!empty($foreignKeys)) {
                $foreignKeyName = $foreignKeys[0]->CONSTRAINT_NAME;
            }
        }
        
        Schema::table('sections', function (Blueprint $table) use ($foreignKeyName) {
            // Drop foreign key constraint first if page_id exists
            if (Schema::hasColumn('sections', 'page_id')) {
                if ($foreignKeyName) {
                    $table->dropForeign($foreignKeyName);
                }
                $table->dropColumn('page_id');
            }
            
            // Drop content column if it exists
            if (Schema::hasColumn('sections', 'content')) {
                $table->dropColumn('content');
            }
            
            // Drop image column if it exists
            if (Schema::hasColumn('sections', 'image')) {
                $table->dropColumn('image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            // Re-add page_id column
            if (!Schema::hasColumn('sections', 'page_id')) {
                $table->foreignId('page_id')->after('id')->constrained()->onDelete('cascade');
            }
            
            // Re-add content column
            if (!Schema::hasColumn('sections', 'content')) {
                $table->text('content')->nullable()->after('section_id');
            }
            
            // Re-add image column
            if (!Schema::hasColumn('sections', 'image')) {
                $table->string('image')->nullable()->after('content');
            }
        });
    }
};
