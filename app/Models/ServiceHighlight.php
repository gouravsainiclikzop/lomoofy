<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceHighlight extends Model
{
    protected $table = 'service_highlights';

    protected $fillable = [
        'highlight1_title',
        'highlight1_text',
        'highlight1_icon',
        'highlight1_active',
        'highlight2_title',
        'highlight2_text',
        'highlight2_icon',
        'highlight2_active',
        'highlight3_title',
        'highlight3_text',
        'highlight3_icon',
        'highlight3_active',
        'highlight4_title',
        'highlight4_text',
        'highlight4_icon',
        'highlight4_active',
    ];

    protected $casts = [
        'highlight1_active' => 'boolean',
        'highlight2_active' => 'boolean',
        'highlight3_active' => 'boolean',
        'highlight4_active' => 'boolean',
    ];

    /**
     * Get or create the singleton instance
     */
    public static function getInstance()
    {
        $instance = self::first();
        if (!$instance) {
            $instance = self::create([
                'highlight1_title' => null,
                'highlight1_text' => null,
                'highlight1_icon' => null,
                'highlight1_active' => false,
                'highlight2_title' => null,
                'highlight2_text' => null,
                'highlight2_icon' => null,
                'highlight2_active' => false,
                'highlight3_title' => null,
                'highlight3_text' => null,
                'highlight3_icon' => null,
                'highlight3_active' => false,
                'highlight4_title' => null,
                'highlight4_text' => null,
                'highlight4_icon' => null,
                'highlight4_active' => false,
            ]);
        }
        return $instance;
    }
}
