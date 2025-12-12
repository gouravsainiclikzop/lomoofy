<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use App\Services\PermissionService;
use Illuminate\Support\Facades\DB;

/**
 * Migration Seeder from Legacy to Enhanced Permission System
 * 
 * This seeder migrates existing permissions from the old structure
 * to the new capability-based structure.
 * 
 * Run this AFTER running the enhanced migrations but BEFORE
 * running the EnhancedRolesAndPermissionsSeeder.
 */
class MigrateLegacyPermissionsSeeder extends Seeder
{
    /**
     * Mapping of old permission slugs to new module/resource/action structure
     */
    protected $permissionMapping = [
        // Dashboard
        'view-dashboard' => ['module' => 'dashboard', 'resource' => 'dashboard', 'action' => 'view'],
        
        // Categories
        'view-categories' => ['module' => 'categories', 'resource' => 'categories', 'action' => 'view'],
        'create-categories' => ['module' => 'categories', 'resource' => 'categories', 'action' => 'create'],
        'edit-categories' => ['module' => 'categories', 'resource' => 'categories', 'action' => 'update'],
        'delete-categories' => ['module' => 'categories', 'resource' => 'categories', 'action' => 'delete'],
        
        // Products
        'view-products' => ['module' => 'products', 'resource' => 'products', 'action' => 'view'],
        'create-products' => ['module' => 'products', 'resource' => 'products', 'action' => 'create'],
        'edit-products' => ['module' => 'products', 'resource' => 'products', 'action' => 'update'],
        'delete-products' => ['module' => 'products', 'resource' => 'products', 'action' => 'delete'],
        
        // Orders
        'view-orders' => ['module' => 'orders', 'resource' => 'orders', 'action' => 'view'],
        'edit-orders' => ['module' => 'orders', 'resource' => 'orders', 'action' => 'update'],
        'delete-orders' => ['module' => 'orders', 'resource' => 'orders', 'action' => 'delete'],
        
        // Customers
        'view-customers' => ['module' => 'customers', 'resource' => 'customers', 'action' => 'view'],
        'edit-customers' => ['module' => 'customers', 'resource' => 'customers', 'action' => 'update'],
        'delete-customers' => ['module' => 'customers', 'resource' => 'customers', 'action' => 'delete'],
        
        // Users
        'view-users' => ['module' => 'users', 'resource' => 'users', 'action' => 'view'],
        'create-users' => ['module' => 'users', 'resource' => 'users', 'action' => 'create'],
        'edit-users' => ['module' => 'users', 'resource' => 'users', 'action' => 'update'],
        'delete-users' => ['module' => 'users', 'resource' => 'users', 'action' => 'delete'],
        
        // Roles
        'view-roles' => ['module' => 'roles', 'resource' => 'roles', 'action' => 'view'],
        'create-roles' => ['module' => 'roles', 'resource' => 'roles', 'action' => 'create'],
        'edit-roles' => ['module' => 'roles', 'resource' => 'roles', 'action' => 'update'],
        'delete-roles' => ['module' => 'roles', 'resource' => 'roles', 'action' => 'delete'],
        'assign-roles' => ['module' => 'roles', 'resource' => 'roles', 'action' => 'assign'],
        
        // Permissions
        'view-permissions' => ['module' => 'permissions', 'resource' => 'permissions', 'action' => 'view'],
        'create-permissions' => ['module' => 'permissions', 'resource' => 'permissions', 'action' => 'create'],
        'edit-permissions' => ['module' => 'permissions', 'resource' => 'permissions', 'action' => 'update'],
        'delete-permissions' => ['module' => 'permissions', 'resource' => 'permissions', 'action' => 'delete'],
        
        // Settings
        'view-settings' => ['module' => 'settings', 'resource' => 'settings', 'action' => 'view'],
        'edit-settings' => ['module' => 'settings', 'resource' => 'settings', 'action' => 'update'],
        
        // Reports
        'view-reports' => ['module' => 'reports', 'resource' => 'reports', 'action' => 'view'],
        'export-reports' => ['module' => 'reports', 'resource' => 'reports', 'action' => 'export'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Migrating legacy permissions to enhanced structure...');

        DB::transaction(function () {
            // Get all existing permissions
            $legacyPermissions = Permission::all();

            foreach ($legacyPermissions as $permission) {
                // Check if we have a mapping for this permission
                if (isset($this->permissionMapping[$permission->slug])) {
                    $mapping = $this->permissionMapping[$permission->slug];
                    
                    // Update permission with new structure
                    $permission->update([
                        'module' => $mapping['module'],
                        'resource' => $mapping['resource'],
                        'action' => $mapping['action'],
                        'group' => $this->getGroupName($mapping['module']),
                        'is_active' => true,
                    ]);

                    $this->command->info("Migrated: {$permission->slug} -> {$mapping['module']}.{$mapping['resource']}.{$mapping['action']}");
                } else {
                    // For unmapped permissions, try to infer structure from slug
                    $this->migrateUnmappedPermission($permission);
                }
            }
        });

        $this->command->info('Legacy permissions migration completed!');
    }

    /**
     * Migrate a permission that doesn't have a direct mapping
     * 
     * @param Permission $permission
     */
    protected function migrateUnmappedPermission(Permission $permission): void
    {
        // Try to parse the slug (format: action-resource or view-resource)
        $parts = explode('-', $permission->slug);
        
        if (count($parts) >= 2) {
            $action = $parts[0];
            $resource = implode('-', array_slice($parts, 1));
            
            // Convert action to standard format
            $actionMap = [
                'view' => 'view',
                'create' => 'create',
                'edit' => 'update',
                'update' => 'update',
                'delete' => 'delete',
                'export' => 'export',
                'import' => 'import',
            ];
            
            $standardAction = $actionMap[$action] ?? $action;
            
            $permission->update([
                'module' => $resource,
                'resource' => $resource,
                'action' => $standardAction,
                'group' => $this->getGroupName($resource),
                'is_active' => true,
            ]);

            $this->command->warn("Auto-migrated: {$permission->slug} -> {$resource}.{$resource}.{$standardAction}");
        } else {
            // Keep as is but mark as inactive
            $permission->update([
                'is_active' => false,
            ]);
            $this->command->error("Could not migrate: {$permission->slug} - marked as inactive");
        }
    }

    /**
     * Get group name from module
     * 
     * @param string $module
     * @return string
     */
    protected function getGroupName(string $module): string
    {
        $groupMap = [
            'dashboard' => 'Dashboard',
            'users' => 'User Management',
            'products' => 'Products',
            'categories' => 'Categories',
            'orders' => 'Orders',
            'customers' => 'Customers',
            'roles' => 'Roles & Permissions',
            'permissions' => 'Roles & Permissions',
            'settings' => 'Settings',
            'reports' => 'Reports',
            'leads' => 'Leads',
            'inventory' => 'Inventory',
        ];

        return $groupMap[$module] ?? ucfirst($module);
    }
}

