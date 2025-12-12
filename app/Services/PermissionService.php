<?php

namespace App\Services;

use App\Models\Permission;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Permission Registration Service
 * 
 * This service provides a standardized way to register permissions
 * when new modules or actions are added to the system.
 * 
 * Usage:
 * PermissionService::registerModule('products', [
 *     'view', 'create', 'update', 'delete', 'export'
 * ]);
 */
class PermissionService
{
    /**
     * Cache key for permissions
     */
    const CACHE_KEY = 'permissions:all';
    const CACHE_TTL = 3600; // 1 hour

    /**
     * Standard CRUD actions
     */
    const ACTIONS = [
        'view' => 'View',
        'create' => 'Create',
        'update' => 'Update',
        'delete' => 'Delete',
        'export' => 'Export',
        'import' => 'Import',
        'approve' => 'Approve',
        'reject' => 'Reject',
        'publish' => 'Publish',
        'unpublish' => 'Unpublish',
    ];

    /**
     * Register a new module with its permissions
     * 
     * @param string $module Module name (e.g., 'products', 'orders')
     * @param array $actions List of actions (e.g., ['view', 'create', 'update', 'delete'])
     * @param string|null $group Optional group name for UI organization
     * @param int $sortOrder Sort order for display
     * @return array Created permissions
     */
    public static function registerModule(
        string $module,
        array $actions = ['view', 'create', 'update', 'delete'],
        ?string $group = null,
        int $sortOrder = 0
    ): array {
        $permissions = [];
        
        DB::transaction(function () use ($module, $actions, $group, $sortOrder, &$permissions) {
            foreach ($actions as $index => $action) {
                $actionName = self::ACTIONS[$action] ?? ucfirst($action);
                $slug = self::generateSlug($module, $action);
                
                $permission = Permission::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => "{$actionName} " . ucfirst($module),
                        'module' => $module,
                        'resource' => $module,
                        'action' => $action,
                        'group' => $group ?? ucfirst($module),
                        'sort_order' => $sortOrder + $index,
                        'is_active' => true,
                        'description' => "Can {$action} {$module}",
                    ]
                );
                
                $permissions[] = $permission;
            }
        });

        // Clear cache
        self::clearCache();

        return $permissions;
    }

    /**
     * Register a resource within a module
     * 
     * @param string $module Module name
     * @param string $resource Resource name (e.g., 'variants' within 'products')
     * @param array $actions List of actions
     * @param string|null $group Optional group name
     * @return array Created permissions
     */
    public static function registerResource(
        string $module,
        string $resource,
        array $actions = ['view', 'create', 'update', 'delete'],
        ?string $group = null
    ): array {
        $permissions = [];
        
        DB::transaction(function () use ($module, $resource, $actions, $group, &$permissions) {
            foreach ($actions as $index => $action) {
                $actionName = self::ACTIONS[$action] ?? ucfirst($action);
                $slug = self::generateSlug($module, $resource, $action);
                
                $permission = Permission::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => "{$actionName} {$resource}",
                        'module' => $module,
                        'resource' => $resource,
                        'action' => $action,
                        'group' => $group ?? ucfirst($module),
                        'sort_order' => $index,
                        'is_active' => true,
                        'description' => "Can {$action} {$resource} in {$module}",
                    ]
                );
                
                $permissions[] = $permission;
            }
        });

        self::clearCache();

        return $permissions;
    }

    /**
     * Register a custom permission
     * 
     * @param string $name Permission name
     * @param string $slug Permission slug
     * @param string $module Module name
     * @param string|null $resource Resource name
     * @param string|null $action Action name
     * @param string|null $group Group name
     * @param string|null $description Description
     * @return Permission
     */
    public static function registerCustom(
        string $name,
        string $slug,
        string $module,
        ?string $resource = null,
        ?string $action = null,
        ?string $group = null,
        ?string $description = null
    ): Permission {
        $permission = Permission::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'module' => $module,
                'resource' => $resource ?? $module,
                'action' => $action,
                'group' => $group ?? ucfirst($module),
                'is_active' => true,
                'description' => $description ?? $name,
            ]
        );

        self::clearCache();

        return $permission;
    }

    /**
     * Get all permissions grouped by module
     * 
     * @return array
     */
    public static function getPermissionsByModule(): array
    {
        return Cache::remember(self::CACHE_KEY . ':by-module', self::CACHE_TTL, function () {
            return Permission::where('is_active', true)
                ->orderBy('module')
                ->orderBy('sort_order')
                ->get()
                ->groupBy('module')
                ->toArray();
        });
    }

    /**
     * Get all permissions grouped by group
     * 
     * @return array
     */
    public static function getPermissionsByGroup(): array
    {
        return Cache::remember(self::CACHE_KEY . ':by-group', self::CACHE_TTL, function () {
            return Permission::where('is_active', true)
                ->orderBy('group')
                ->orderBy('sort_order')
                ->get()
                ->groupBy('group')
                ->toArray();
        });
    }

    /**
     * Clear permission cache
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::CACHE_KEY . ':by-module');
        Cache::forget(self::CACHE_KEY . ':by-group');
    }

    /**
     * Generate permission slug
     * 
     * @param string $module
     * @param string $resource
     * @param string|null $action
     * @return string
     */
    private static function generateSlug(string $module, string $resource, ?string $action = null): string
    {
        $parts = [$module];
        
        if ($resource !== $module) {
            $parts[] = $resource;
        }
        
        if ($action) {
            $parts[] = $action;
        }
        
        return implode('.', $parts);
    }

    /**
     * Sync permissions from configuration
     * Useful for bulk registration during deployment
     * 
     * @param array $config Configuration array
     * @return void
     */
    public static function syncFromConfig(array $config): void
    {
        DB::transaction(function () use ($config) {
            foreach ($config as $module => $moduleConfig) {
                $actions = $moduleConfig['actions'] ?? ['view', 'create', 'update', 'delete'];
                $group = $moduleConfig['group'] ?? null;
                $sortOrder = $moduleConfig['sort_order'] ?? 0;
                
                self::registerModule($module, $actions, $group, $sortOrder);
                
                // Register resources if any
                if (isset($moduleConfig['resources'])) {
                    foreach ($moduleConfig['resources'] as $resource => $resourceConfig) {
                        $resourceActions = $resourceConfig['actions'] ?? $actions;
                        self::registerResource($module, $resource, $resourceActions, $group);
                    }
                }
            }
        });
    }
}

