<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the roles assigned to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')
                    ->withTimestamps();
    }

    /**
     * Get direct permissions assigned to the user.
     */
    public function directPermissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user')
                    ->wherePivot('granted', true)
                    ->where(function ($query) {
                        $query->whereNull('permission_user.expires_at')
                              ->orWhere('permission_user.expires_at', '>', now());
                    })
                    ->withPivot('granted', 'expires_at')
                    ->withTimestamps();
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole(array $roleSlugs): bool
    {
        return $this->roles()->whereIn('slug', $roleSlugs)->exists();
    }

    /**
     * Check if user has a specific permission.
     * 
     * This method checks:
     * 1. Direct user permissions (overrides)
     * 2. Permissions through roles
     * 3. Inherited permissions from parent roles
     * 
     * @param string|Permission $permission Permission slug, identifier (module.resource.action), or Permission instance
     * @return bool
     */
    public function hasPermission($permission): bool
    {
        // Normalize permission input
        $permissionSlug = $this->normalizePermission($permission);

        // Check cache first
        $cacheKey = "user.{$this->id}.permissions";
        $cachedPermissions = cache()->remember($cacheKey, 3600, function () {
            return $this->getAllPermissions()->pluck('slug')->toArray();
        });

        if (in_array($permissionSlug, $cachedPermissions)) {
            return true;
        }

        // Check direct user permissions (overrides)
        $directPermission = $this->directPermissions()
            ->where('permissions.slug', $permissionSlug)
            ->first();

        if ($directPermission) {
            return $directPermission->pivot->granted === true;
        }

        // Check permissions through roles
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permissionSlug)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has permission using capability pattern
     * 
     * @param string $module
     * @param string|null $resource
     * @param string|null $action
     * @return bool
     */
    public function canDo(string $module, ?string $resource = null, ?string $action = null): bool
    {
        // Try exact match first
        $identifier = $this->buildPermissionIdentifier($module, $resource, $action);
        if ($this->hasPermission($identifier)) {
            return true;
        }

        // Try pattern matching
        $permissions = $this->getAllPermissions();
        foreach ($permissions as $permission) {
            if ($permission->matches($module, $resource, $action)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has any of the given permissions.
     * 
     * @param array $permissions
     * @return bool
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given permissions.
     * 
     * @param array $permissions
     * @return bool
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all permissions for the user (from roles and direct).
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPermissions()
    {
        $permissions = collect();

        // Get permissions from roles
        // Note: After running migrations, you can filter by is_active if needed
        $roles = $this->roles;
        
        foreach ($roles as $role) {
            $permissions = $permissions->merge($role->getAllPermissions());
        }

        // Get direct permissions
        $directPermissions = $this->directPermissions()->get();
        $permissions = $permissions->merge($directPermissions);

        // Remove duplicates and return
        return $permissions->unique('id');
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(Role $role): void
    {
        $this->roles()->syncWithoutDetaching([$role->id]);
        $this->clearPermissionCache();
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole(Role $role): void
    {
        $this->roles()->detach($role->id);
        $this->clearPermissionCache();
    }

    /**
     * Sync user roles.
     */
    public function syncRoles(array $roleIds): void
    {
        $this->roles()->sync($roleIds);
        $this->clearPermissionCache();
    }

    /**
     * Grant a direct permission to the user.
     * 
     * @param Permission|string $permission
     * @param \DateTime|null $expiresAt
     * @return void
     */
    public function givePermission($permission, ?\DateTime $expiresAt = null): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        $this->directPermissions()->syncWithoutDetaching([
            $permission->id => [
                'granted' => true,
                'expires_at' => $expiresAt,
            ]
        ]);

        $this->clearPermissionCache();
    }

    /**
     * Revoke a direct permission from the user.
     * 
     * @param Permission|string $permission
     * @return void
     */
    public function revokePermission($permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        $this->directPermissions()->detach($permission->id);
        $this->clearPermissionCache();
    }

    /**
     * Clear permission cache for this user.
     */
    public function clearPermissionCache(): void
    {
        cache()->forget("user.{$this->id}.permissions");
    }

    /**
     * Normalize permission input to slug.
     * 
     * @param string|Permission $permission
     * @return string
     */
    private function normalizePermission($permission): string
    {
        if ($permission instanceof Permission) {
            return $permission->slug;
        }

        // If it's already a slug, return as is
        if (Permission::where('slug', $permission)->exists()) {
            return $permission;
        }

        // Try to find by identifier pattern (module.resource.action)
        $parts = explode('.', $permission);
        if (count($parts) >= 2) {
            $permission = Permission::where('module', $parts[0])
                ->where('resource', $parts[1] ?? $parts[0])
                ->where('action', $parts[2] ?? null)
                ->first();

            if ($permission) {
                return $permission->slug;
            }
        }

        return $permission;
    }

    /**
     * Build permission identifier from parts.
     * 
     * @param string $module
     * @param string|null $resource
     * @param string|null $action
     * @return string
     */
    private function buildPermissionIdentifier(string $module, ?string $resource = null, ?string $action = null): string
    {
        $parts = [$module];
        
        if ($resource && $resource !== $module) {
            $parts[] = $resource;
        }
        
        if ($action) {
            $parts[] = $action;
        }
        
        return implode('.', $parts);
    }
}
