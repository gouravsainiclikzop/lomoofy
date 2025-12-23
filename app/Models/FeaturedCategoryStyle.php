<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeaturedCategoryStyle extends Model
{
    protected $table = 'featured_category_styles';

    protected $fillable = [
        'category_id',
        'title',
        'featured_image',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Category relationship
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}

