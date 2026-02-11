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

    protected $fillable = [
        'name',
        'email',
        'password',
        'image',
    ];
 
    protected $hidden = [
        'password',
        'remember_token',
    ];

    
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
 
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')
                    ->withTimestamps();
    }
 
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
  
    public function hasPermission($permission): bool
    {
        // Check if permission contains action (format: "Permission Name.action")
        $parts = explode('.', $permission);
        if (count($parts) === 2) {
            // Format: permissionName.action
            return $this->canDo($parts[0], null, $parts[1]);
        }

        // Normalize permission input (returns permission name)
        $permissionName = $this->normalizePermission($permission);

        if (empty($permissionName)) {
            return false;
        }
 
        $cacheKey = "user.{$this->id}.permissions";
        $cachedPermissions = cache()->remember($cacheKey, 3600, function () {
            return $this->getAllPermissions()->pluck('name')->toArray();
        });

        if (in_array($permissionName, $cachedPermissions)) {
            return true;
        }
 
        $directPermission = $this->directPermissions()
            ->whereRaw('LOWER(permissions.name) = ?', [strtolower($permissionName)])
            ->first();

        if ($directPermission) {
            return $directPermission->pivot->granted === true;
        }
 
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permissionName)) {
                return true;
            }
        }

        return false;
    }
 
    public function canDo(string $permissionName, ?string $resource = null, ?string $action = null): bool
    { 
        if ($action !== null) { 
            foreach ($this->roles as $role) {
                $permission = $role->permissions()
                    ->whereRaw('LOWER(name) = ?', [strtolower($permissionName)])
                    ->first();
                
                if ($permission) { 
                    $selectedActions = [];
                    if ($permission->pivot->actions) {
                        $selectedActions = json_decode($permission->pivot->actions, true);
                        if (!is_array($selectedActions)) {
                            $selectedActions = [];
                        }
                    }
                     
                    if (empty($selectedActions)) {
                        if ($permission->hasAction($action)) {
                            return true;
                        }
                    } else { 
                        $actionLower = strtolower($action);
                        $selectedActionsLower = array_map('strtolower', $selectedActions);
                        if (in_array($actionLower, $selectedActionsLower)) {
                            return true;
                        }
                    }
                }
            }
             
            $directPermission = $this->directPermissions()
                ->whereRaw('LOWER(permissions.name) = ?', [strtolower($permissionName)])
                ->first();
            
            if ($directPermission && $directPermission->pivot->granted === true) { 
                if ($directPermission->hasAction($action)) {
                    return true;
                }
            }
            
            return false;
        }
 
        return $this->hasPermission($permissionName);
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
 
    public function getAllPermissions()
    {
        $permissions = collect();
 
        $roles = $this->roles;
        
        foreach ($roles as $role) {
            $permissions = $permissions->merge($role->getAllPermissions());
        }
 
        $directPermissions = $this->directPermissions()->get();
        $permissions = $permissions->merge($directPermissions);

        // Remove duplicates and return
        return $permissions->unique('id');
    }
 
    public function assignRole(Role $role): void
    {
        $this->roles()->syncWithoutDetaching([$role->id]);
        $this->clearPermissionCache();
    }
 
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
 
    public function revokePermission($permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        $this->directPermissions()->detach($permission->id);
        $this->clearPermissionCache();
    }

 
    public function clearPermissionCache(): void
    {
        cache()->forget("user.{$this->id}.permissions");
    }

    
    private function normalizePermission($permission): string
    { 
        if ($permission === null) {
            return '';
        }

        if ($permission instanceof Permission) {
            // Return name first, fallback to slug
            return $permission->name ?? $permission->slug ?? '';
        }
 
        if (!is_string($permission)) {
            return '';
        }
  
        // First try to find by name (case-insensitive)
        $permissionObj = Permission::whereRaw('LOWER(name) = ?', [strtolower($permission)])->first();
        if ($permissionObj) {
            return $permissionObj->name;
        }

        // Then try by slug
        $permissionObj = Permission::where('slug', $permission)->first();
        if ($permissionObj) {
            return $permissionObj->name;
        }
 
        // Return as-is if not found (might be a legacy format)
        return $permission;
    }
}
