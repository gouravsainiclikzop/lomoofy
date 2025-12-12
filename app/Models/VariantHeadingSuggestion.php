<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariantHeadingSuggestion extends Model
{
    protected $fillable = [
        'heading_name',
        'usage_count',
    ];

    protected $casts = [
        'usage_count' => 'integer',
    ];

    /**
     * Increment usage count when a heading is used
     */
    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    /**
     * Get suggestions ordered by usage count
     */
    public static function getSuggestions($limit = 20)
    {
        return static::orderBy('usage_count', 'desc')
            ->orderBy('heading_name', 'asc')
            ->limit($limit)
            ->pluck('heading_name')
            ->toArray();
    }

    /**
     * Find or create a heading suggestion
     */
    public static function findOrCreate($headingName)
    {
        $heading = static::firstOrCreate(
            ['heading_name' => trim($headingName)],
            ['usage_count' => 0]
        );
        
        $heading->incrementUsage();
        
        return $heading;
    }
}
