<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
 
class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated');
        }
 
        if (empty($permissions)) {
            return $next($request);
        }
 
        foreach ($permissions as $permissionString) {
            if ($this->checkPermissionString($user, $permissionString)) {
                return $next($request);
            }
        }
 
        abort(403, 'Insufficient permissions');
    }
 
    protected function checkPermissionString($user, string $permissionString): bool
    { 
        if (strpos($permissionString, '|') !== false) {
            $permissions = explode('|', $permissionString);
            foreach ($permissions as $permission) {
                if ($this->checkSinglePermission($user, trim($permission))) {
                    return true;
                }
            }
            return false;
        }
 
        if (strpos($permissionString, '&') !== false) {
            $permissions = explode('&', $permissionString);
            foreach ($permissions as $permission) {
                if (!$this->checkSinglePermission($user, trim($permission))) {
                    return false;
                }
            }
            return true;
        }
 
        return $this->checkSinglePermission($user, $permissionString);
    }
 
    protected function checkSinglePermission($user, string $permission): bool
    {
        // First, try direct permission name match
        if ($user->hasPermission($permission)) {
            return true;
        }
         
        $parts = explode('.', $permission);
        
        if (count($parts) === 2) {
            // Format: permissionName.action (e.g., "Product Management.view" or "product.view")
            $permissionName = $parts[0];
            $action = $parts[1];
            
            // Check if user has permission with this name and action
            if ($user->canDo($permissionName, null, $action)) {
                return true;
            }
        }
  
        // Final fallback: try direct permission check
        return $user->hasPermission($permission);
    }
}
