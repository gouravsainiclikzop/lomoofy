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
        Schema::rename('our_collections', 'featured_category_styles');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('featured_category_styles', 'our_collections');
    }
};
