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
        Schema::create('service_highlights', function (Blueprint $table) {
            $table->id();
            $table->string('highlight1_title')->nullable();
            $table->text('highlight1_text')->nullable();
            $table->boolean('highlight1_active')->default(false);
            $table->string('highlight2_title')->nullable();
            $table->text('highlight2_text')->nullable();
            $table->boolean('highlight2_active')->default(false);
            $table->string('highlight3_title')->nullable();
            $table->text('highlight3_text')->nullable();
            $table->boolean('highlight3_active')->default(false);
            $table->string('highlight4_title')->nullable();
            $table->text('highlight4_text')->nullable();
            $table->boolean('highlight4_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_highlights');
    }
};
