<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Services\PermissionService;

/**
 * Enhanced Roles and Permissions Seeder
 * 
 * This seeder uses the PermissionService to register permissions
 * in a standardized, maintainable way.
 */
class EnhancedRolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Registering permissions...');

        // Register permissions for each module
        $this->registerDashboardPermissions();
        $this->registerUserManagementPermissions();
        $this->registerProductPermissions();
        $this->registerCategoryPermissions();
        $this->registerOrderPermissions();
        $this->registerCustomerPermissions();
        $this->registerRolePermissions();
        $this->registerPermissionPermissions();
        $this->registerSettingsPermissions();
        $this->registerReportPermissions();
        $this->registerLeadPermissions();
        $this->registerInventoryPermissions();

        $this->command->info('Creating roles...');

        // Create roles
        $this->createRoles();

        $this->command->info('Assigning permissions to roles...');

        // Assign permissions to roles
        $this->assignPermissionsToRoles();

        $this->command->info('Assigning admin role to first user...');

        // Assign admin role to first user
        $this->assignAdminToFirstUser();

        $this->command->info('Roles and permissions seeded successfully!');
    }

    /**
     * Register dashboard permissions
     */
    protected function registerDashboardPermissions(): void
    {
        PermissionService::registerModule('dashboard', ['view'], 'Dashboard', 0);
    }

    /**
     * Register user management permissions
     */
    protected function registerUserManagementPermissions(): void
    {
        PermissionService::registerModule('users', ['view', 'create', 'update', 'delete'], 'User Management', 10);
    }

    /**
     * Register product permissions
     */
    protected function registerProductPermissions(): void
    {
        PermissionService::registerModule('products', ['view', 'create', 'update', 'delete', 'export', 'import'], 'Products', 20);
        
        // Register product resources
        PermissionService::registerResource('products', 'variants', ['view', 'create', 'update', 'delete'], 'Products');
        PermissionService::registerResource('products', 'images', ['view', 'create', 'update', 'delete'], 'Products');
    }

    /**
     * Register category permissions
     */
    protected function registerCategoryPermissions(): void
    {
        PermissionService::registerModule('categories', ['view', 'create', 'update', 'delete'], 'Categories', 30);
    }

    /**
     * Register order permissions
     */
    protected function registerOrderPermissions(): void
    {
        PermissionService::registerModule('orders', ['view', 'create', 'update', 'delete', 'export'], 'Orders', 40);
    }

    /**
     * Register customer permissions
     */
    protected function registerCustomerPermissions(): void
    {
        PermissionService::registerModule('customers', ['view', 'create', 'update', 'delete', 'export'], 'Customers', 50);
    }

    /**
     * Register role permissions
     */
    protected function registerRolePermissions(): void
    {
        PermissionService::registerModule('roles', ['view', 'create', 'update', 'delete', 'assign'], 'Roles & Permissions', 60);
    }

    /**
     * Register permission management permissions
     */
    protected function registerPermissionPermissions(): void
    {
        PermissionService::registerModule('permissions', ['view', 'create', 'update', 'delete'], 'Roles & Permissions', 60);
    }

    /**
     * Register settings permissions
     */
    protected function registerSettingsPermissions(): void
    {
        PermissionService::registerModule('settings', ['view', 'update'], 'Settings', 70);
    }

    /**
     * Register report permissions
     */
    protected function registerReportPermissions(): void
    {
        PermissionService::registerModule('reports', ['view', 'export'], 'Reports', 80);
    }

    /**
     * Register lead permissions
     */
    protected function registerLeadPermissions(): void
    {
        PermissionService::registerModule('leads', ['view', 'create', 'update', 'delete', 'assign'], 'Leads', 90);
    }

    /**
     * Register inventory permissions
     */
    protected function registerInventoryPermissions(): void
    {
        PermissionService::registerModule('inventory', ['view', 'update'], 'Inventory', 100);
    }

    /**
     * Create roles
     */
    protected function createRoles(): void
    {
        // Administrator - Full access
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Has full access to all features and settings',
                'level' => 'admin',
                'is_active' => true,
                'is_system' => true,
                'sort_order' => 0,
            ]
        );

        // Manager - Can manage products, orders, customers
        $managerRole = Role::firstOrCreate(
            ['slug' => 'manager'],
            [
                'name' => 'Manager',
                'description' => 'Can manage products, orders, and customers',
                'level' => 'standard',
                'is_active' => true,
                'is_system' => true,
                'sort_order' => 10,
            ]
        );

        // Editor - Can manage content
        $editorRole = Role::firstOrCreate(
            ['slug' => 'editor'],
            [
                'name' => 'Editor',
                'description' => 'Can manage content like products and categories',
                'level' => 'standard',
                'is_active' => true,
                'is_system' => true,
                'sort_order' => 20,
            ]
        );

        // Viewer - Read-only access
        $viewerRole = Role::firstOrCreate(
            ['slug' => 'viewer'],
            [
                'name' => 'Viewer',
                'description' => 'Can only view data, no editing permissions',
                'level' => 'standard',
                'is_active' => true,
                'is_system' => true,
                'sort_order' => 30,
            ]
        );
    }

    /**
     * Assign permissions to roles
     */
    protected function assignPermissionsToRoles(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $managerRole = Role::where('slug', 'manager')->first();
        $editorRole = Role::where('slug', 'editor')->first();
        $viewerRole = Role::where('slug', 'viewer')->first();

        // Admin gets all permissions
        $adminRole->syncPermissions(Permission::active()->pluck('id')->toArray());

        // Manager permissions
        $managerPermissions = Permission::active()
            ->where(function ($query) {
                $query->whereIn('module', ['dashboard', 'products', 'categories', 'orders', 'customers', 'reports', 'inventory'])
                    ->whereIn('action', ['view', 'create', 'update', 'export']);
            })
            ->orWhere(function ($query) {
                $query->where('module', 'orders')
                    ->whereIn('action', ['view', 'update', 'export']);
            })
            ->pluck('id')->toArray();
        $managerRole->syncPermissions($managerPermissions);

        // Editor permissions
        $editorPermissions = Permission::active()
            ->whereIn('module', ['dashboard', 'products', 'categories', 'orders', 'customers'])
            ->whereIn('action', ['view', 'create', 'update'])
            ->pluck('id')->toArray();
        $editorRole->syncPermissions($editorPermissions);

        // Viewer permissions
        $viewerPermissions = Permission::active()
            ->whereIn('module', ['dashboard', 'products', 'categories', 'orders', 'customers', 'reports'])
            ->where('action', 'view')
            ->pluck('id')->toArray();
        $viewerRole->syncPermissions($viewerPermissions);
    }

    /**
     * Assign admin role to first user
     */
    protected function assignAdminToFirstUser(): void
    {
        $firstUser = User::first();
        $adminRole = Role::where('slug', 'admin')->first();

        if ($firstUser && $adminRole && $firstUser->roles->isEmpty()) {
            $firstUser->assignRole($adminRole);
            $this->command->info("Admin role assigned to user: {$firstUser->email}");
        }
    }
}

