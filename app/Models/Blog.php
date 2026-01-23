<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Blog extends Model
{
    protected $fillable = [
        'thumbnail_image',
        'featured_image',
        'title',
        'slug',
        'description',
        'added_by',
        'published_date',
    ];

    protected $casts = [
        'published_date' => 'date',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from title if not provided
        static::creating(function ($blog) {
            if (empty($blog->slug)) {
                $blog->slug = Str::slug($blog->title);
            }
            // Set published_date to current date if not provided
            if (empty($blog->published_date)) {
                $blog->published_date = now()->toDateString();
            }
        });

        static::updating(function ($blog) {
            if ($blog->isDirty('title') && empty($blog->slug)) {
                $blog->slug = Str::slug($blog->title);
            }
        });
    }

    /**
     * Get the thumbnail image URL.
     */
    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail_image 
            ? asset('storage/' . $this->thumbnail_image) 
            : asset('frontend/images/blog-placeholder.jpg');
    }

    /**
     * Get the featured image URL.
     */
    public function getFeaturedImageUrlAttribute()
    {
        return $this->featured_image 
            ? asset('storage/' . $this->featured_image) 
            : asset('frontend/images/blog-featured-placeholder.jpg');
    }
}
