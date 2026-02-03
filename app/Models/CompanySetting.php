<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name',
        'company_logo_text',
        'company_logo',
        'secondary_logo',
        'phone',
        'customer_care_phone',
        'careers_phone',
        'email',
        'secondary_email',
        'address',
        'pincode',
        'city',
        'state',
        'pan_no',
        'gst_registration_no',
        'authorized_signatory',
        'coming_soon',
        'facebook_url',
        'twitter_url',
        'youtube_url',
        'instagram_url',
        'linkedin_url',
        'whatsapp_url',
        'font_family',
        'color_themes',
        'active_color_theme',
    ];

    protected $casts = [
        'coming_soon' => 'boolean',
        'color_themes' => 'array',
    ];

    /**
     * Get the first (and only) company setting record
     */
    public static function getSettings()
    {
        return static::first() ?? static::create([
            'company_name' => 'Lomoofy',
            'company_logo_text' => 'Lomoofy Industries',
            'phone' => '+91 9876315314',
            'email' => 'info@lomoofyindustries.com',
            'address' => '3298 Grant Street Longview, TX<br>United Kingdom 75601',
        ]);
    }
}

