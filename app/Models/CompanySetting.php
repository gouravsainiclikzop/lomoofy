<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name',
        'company_logo_text',
        'company_logo',
        'phone',
        'email',
        'address',
    ];

    /**
     * Get the first (and only) company setting record
     */
    public static function getSettings()
    {
        return static::first() ?? static::create([
            'company_name' => 'Lomoof',
            'company_logo_text' => 'Lomoofy',
            'phone' => '+91 9876315314',
            'email' => 'info@lomoof.com',
            'address' => '123, Main Street, Anytown, USA',
        ]);
    }
}

