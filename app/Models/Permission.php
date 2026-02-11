<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Permission extends Model
{
  
    protected $fillable = [
        'name',
        'slug',
        'action',
        'sort_no',
        'is_active',
    ];

   
    protected $casts = [
        'is_active' => 'boolean',
        'sort_no' => 'integer',
    ];

   
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role')
                    ->withTimestamps();
    }

    
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'permission_user')
                    ->withPivot('granted', 'expires_at')
                    ->withTimestamps();
    }
 
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if permission has a specific action
     */
    public function hasAction(string $action): bool
    {
        if (empty($this->action)) {
            return false;
        }

        // Parse actions from action field (stored as JSON)
        $actions = [];
        $parsedActions = json_decode($this->action, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($parsedActions)) {
            $actions = $parsedActions;
        } else {
            // If not JSON, treat as single action or comma-separated
            $actions = strpos($this->action, ',') !== false 
                ? array_map('trim', explode(',', $this->action))
                : [$this->action];
        }

        $actionLower = strtolower($action);
        $actionsLower = array_map('strtolower', $actions);
        return in_array($actionLower, $actionsLower);
    }
}
