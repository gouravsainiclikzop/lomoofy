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
        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('facebook_url')->nullable()->after('authorized_signatory');
            $table->string('twitter_url')->nullable()->after('facebook_url');
            $table->string('youtube_url')->nullable()->after('twitter_url');
            $table->string('instagram_url')->nullable()->after('youtube_url');
            $table->string('linkedin_url')->nullable()->after('instagram_url');
            $table->string('whatsapp_url')->nullable()->after('linkedin_url');
            $table->string('secondary_logo')->nullable()->after('company_logo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'facebook_url',
                'twitter_url',
                'youtube_url',
                'instagram_url',
                'linkedin_url',
                'whatsapp_url',
                'secondary_logo'
            ]);
        });
    }
};
