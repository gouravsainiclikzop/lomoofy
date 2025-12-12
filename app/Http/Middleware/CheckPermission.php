<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enhanced Permission Middleware
 * 
 * Supports multiple permission checking methods:
 * 1. Permission slug: 'products.view'
 * 2. Capability pattern: 'products.products.view' or 'products.view'
 * 3. Multiple permissions (OR): 'products.view|products.create'
 * 4. Multiple permissions (AND): 'products.view&products.create'
 */
class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        // If no permissions specified, allow access
        if (empty($permissions)) {
            return $next($request);
        }

        // Check each permission requirement
        foreach ($permissions as $permissionString) {
            if ($this->checkPermissionString($user, $permissionString)) {
                return $next($request);
            }
        }

        // If we get here, user doesn't have any of the required permissions
        abort(403, 'Insufficient permissions');
    }

    /**
     * Check a permission string that may contain OR (|) or AND (&) operators.
     * 
     * @param \App\Models\User $user
     * @param string $permissionString
     * @return bool
     */
    protected function checkPermissionString($user, string $permissionString): bool
    {
        // Handle OR operator (|)
        if (strpos($permissionString, '|') !== false) {
            $permissions = explode('|', $permissionString);
            foreach ($permissions as $permission) {
                if ($this->checkSinglePermission($user, trim($permission))) {
                    return true;
                }
            }
            return false;
        }

        // Handle AND operator (&)
        if (strpos($permissionString, '&') !== false) {
            $permissions = explode('&', $permissionString);
            foreach ($permissions as $permission) {
                if (!$this->checkSinglePermission($user, trim($permission))) {
                    return false;
                }
            }
            return true;
        }

        // Single permission
        return $this->checkSinglePermission($user, $permissionString);
    }

    /**
     * Check a single permission.
     * 
     * @param \App\Models\User $user
     * @param string $permission
     * @return bool
     */
    protected function checkSinglePermission($user, string $permission): bool
    {
        // Parse capability pattern (module.resource.action or module.action)
        $parts = explode('.', $permission);
        
        if (count($parts) === 2) {
            // Format: module.action
            return $user->canDo($parts[0], $parts[0], $parts[1]);
        } elseif (count($parts) === 3) {
            // Format: module.resource.action
            return $user->canDo($parts[0], $parts[1], $parts[2]);
        }

        // Try as permission slug
        return $user->hasPermission($permission);
    }
}
