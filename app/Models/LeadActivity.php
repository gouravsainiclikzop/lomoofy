<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadActivity extends Model
{
    protected $fillable = [
        'lead_id',
        'type',
        'description',
        'follow_up_date',
        'next_action_owner',
        'file_path',
        'created_by',
    ];

    protected $casts = [
        'follow_up_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function nextActionOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'next_action_owner');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
