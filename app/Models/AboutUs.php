<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutUs extends Model
{
    protected $table = 'about_us';

    protected $fillable = [
        'description',
        'image',
    ];

    /**
     * Get or create the singleton instance
     */
    public static function getInstance()
    {
        $instance = self::first();
        if (!$instance) {
            $instance = self::create([
                'description' => null,
                'image' => null,
            ]);
        }
        return $instance;
    }
}
