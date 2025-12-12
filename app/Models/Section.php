<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'page_id',
        'section_id',
        'content',
        'image',
        'sort_order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the page that owns the section.
     */
    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Scope to filter by page ID.
     */
    public function scopeForPage($query, $pageId)
    {
        return $query->where('page_id', $pageId);
    }

    /**
     * Scope to get only active sections.
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

    /**
     * Get the image URL attribute.
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    /**
     * Get the base section name (without variation suffix).
     * Example: banner_variation_1 -> banner
     */
    public function getBaseSectionNameAttribute()
    {
        // Remove _variation_X pattern from section_id
        return preg_replace('/_variation_\d+$/', '', $this->section_id);
    }

    /**
     * Get variation number from section_id.
     * Example: banner_variation_1 -> 1
     */
    public function getVariationNumberAttribute()
    {
        if (preg_match('/_variation_(\d+)$/', $this->section_id, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    /**
     * Check if this section is a variation.
     */
    public function isVariation()
    {
        return $this->variation_number !== null;
    }

    /**
     * Get all variations of this section (same base name).
     */
    public function variations()
    {
        $baseName = $this->base_section_name;
        return self::where('page_id', $this->page_id)
            ->where(function($query) use ($baseName) {
                $query->where('section_id', 'like', $baseName . '_variation_%')
                      ->orWhere('section_id', $baseName);
            })
            ->where('id', '!=', $this->id)
            ->get();
    }

    /**
     * Deactivate all other variations when this one is activated.
     */
    public function deactivateOtherVariations()
    {
        if ($this->isVariation() && $this->is_active) {
            $baseName = $this->base_section_name;
            self::where('page_id', $this->page_id)
                ->where(function($query) use ($baseName) {
                    $query->where('section_id', 'like', $baseName . '_variation_%')
                          ->orWhere('section_id', $baseName);
                })
                ->where('id', '!=', $this->id)
                ->update(['is_active' => false]);
        }
    }
}
