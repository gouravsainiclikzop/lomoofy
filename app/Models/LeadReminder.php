<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadReminder extends Model
{
    protected $fillable = [
        'lead_id',
        'reminder_note',
        'reminder_date',
        'is_completed',
        'completed_at',
        'assigned_to',
        'created_by',
    ];

    protected $casts = [
        'reminder_date' => 'datetime',
        'completed_at' => 'datetime',
        'is_completed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeDue($query)
    {
        return $query->where('is_completed', false)
                    ->where('reminder_date', '<=', now());
    }

    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }
}
