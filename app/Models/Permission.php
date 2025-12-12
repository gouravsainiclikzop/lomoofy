<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Permission extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'module',
        'resource',
        'action',
        'group',
        'sort_order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the roles that have this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role')
                    ->withTimestamps();
    }

    /**
     * Get the users that have this permission directly.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'permission_user')
                    ->withPivot('granted', 'expires_at')
                    ->withTimestamps();
    }

    /**
     * Scope to get only active permissions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by module.
     */
    public function scopeInModule(Builder $query, string $module): Builder
    {
        return $query->where('module', $module);
    }

    /**
     * Scope to filter by resource.
     */
    public function scopeInResource(Builder $query, string $resource): Builder
    {
        return $query->where('resource', $resource);
    }

    /**
     * Scope to filter by action.
     */
    public function scopeWithAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by group.
     */
    public function scopeInGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    /**
     * Check if permission matches a capability pattern
     * 
     * @param string $module
     * @param string|null $resource
     * @param string|null $action
     * @return bool
     */
    public function matches(string $module, ?string $resource = null, ?string $action = null): bool
    {
        if ($this->module !== $module) {
            return false;
        }

        if ($resource !== null && $this->resource !== $resource) {
            return false;
        }

        if ($action !== null && $this->action !== $action) {
            return false;
        }

        return true;
    }

    /**
     * Get permission identifier (module.resource.action or module.action)
     */
    public function getIdentifierAttribute(): string
    {
        $parts = [$this->module];
        
        if ($this->resource && $this->resource !== $this->module) {
            $parts[] = $this->resource;
        }
        
        if ($this->action) {
            $parts[] = $this->action;
        }
        
        return implode('.', $parts);
    }
}
