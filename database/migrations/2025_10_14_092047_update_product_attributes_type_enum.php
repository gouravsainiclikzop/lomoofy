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
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE product_attributes MODIFY COLUMN type ENUM('select', 'color', 'image', 'text', 'number', 'date', 'boolean') DEFAULT 'select'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE product_attributes MODIFY COLUMN type ENUM('select', 'color', 'image', 'text') DEFAULT 'select'");
        }
    }
};