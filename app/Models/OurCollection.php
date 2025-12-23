<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OurCollection extends Model
{
    protected $table = 'our_collection';

    protected $fillable = [
        'background_image',
        'heading',
        'description',
        'category_id',
    ];

    /**
     * Category relationship
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get or create the singleton instance
     */
    public static function getInstance()
    {
        $instance = self::first();
        if (!$instance) {
            $instance = self::create([
                'background_image' => null,
                'heading' => null,
                'description' => null,
                'category_id' => null,
            ]);
        }
        return $instance;
    }
}
