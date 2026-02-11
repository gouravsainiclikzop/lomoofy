<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Role extends Model
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
        'parent_id',
        'level',
        'is_active',
        'is_system',
        'sort_order',
        'metadata',
    ];
 
    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'metadata' => 'array',
        'sort_order' => 'integer',
    ];
 
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')
                    ->withTimestamps();
    }

    
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role')
                    ->withPivot('actions')
                    ->withTimestamps();
    }
 
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'parent_id');
    }

    /**
     * Get child roles.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Role::class, 'parent_id');
    }
 
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
 
    public function scopeNonSystem(Builder $query): Builder
    {
        return $query->where('is_system', false);
    }

    
    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('is_system', true);
    }

    
    public function hasPermission($permission): bool
    {
        $permissionName = null;
        
        if ($permission instanceof Permission) {
            $permissionName = $permission->name;
        } else {
            $permissionName = $permission;
        }

        if (empty($permissionName)) {
            return false;
        }

        // Check direct permissions by name (case-insensitive)
        if ($this->permissions()->whereRaw('LOWER(name) = ?', [strtolower($permissionName)])->exists()) {
            return true;
        }

        // Check inherited permissions from parent role
        if ($this->parent) {
            return $this->parent->hasPermission($permissionName);
        }

        return false;
    }

     
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }
 
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }
 
    public function givePermission($permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        $this->permissions()->syncWithoutDetaching([$permission->id]);
    }
 
    public function removePermission($permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        $this->permissions()->detach($permission->id);
    }
 
    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }
 
    public function getAllPermissions()
    {
        $permissions = $this->permissions;

        if ($this->parent) {
            $permissions = $permissions->merge($this->parent->getAllPermissions());
        }

        return $permissions->unique('id');
    }
}
