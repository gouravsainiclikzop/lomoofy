<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstagramGallery extends Model
{
    protected $table = 'instagram_gallery';

    protected $fillable = [
        'thumbnail_image',
        'instagram_link',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope to get only active gallery items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }
}
