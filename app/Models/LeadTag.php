<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class LeadTag extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Auto-generate slug from name
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($tag) {
            if (empty($tag->slug) && !empty($tag->name)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
        
        static::updating(function ($tag) {
            if (empty($tag->slug) && !empty($tag->name)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    // Relationships
    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Lead::class, 'lead_tag', 'lead_tag_id', 'lead_id')
            ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
