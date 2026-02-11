<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use HasFactory;

    protected $fillable = [
        'software_id',
        'theme_name',
        'theme_thumbnail',
        'theme_pdf',
        'status',
        'type',
        'preview_url',
    ];

    public function isExternal(): bool
    {
        return $this->type === 'external';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBySoftware($query, string $softwareId)
    {
        return $query->where('software_id', $softwareId);
    }
}
