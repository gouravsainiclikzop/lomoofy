<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    /**
     * Display permissions management page.
     */
    public function index()
    {
        return view('admin.permissions.index');
    }

    /**
     * Get permissions data for DataTables.
     * Permissions are pre-defined and grouped by module/page.
     */
    public function getData(Request $request)
    {
        $query = Permission::withCount('roles');

        // Check if enhanced columns exist
        $hasModule = Schema::hasColumn('permissions', 'module');
        $hasResource = Schema::hasColumn('permissions', 'resource');
        $hasAction = Schema::hasColumn('permissions', 'action');
        $hasGroup = Schema::hasColumn('permissions', 'group');
        $hasSortOrder = Schema::hasColumn('permissions', 'sort_order');

        // Search
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search, $hasModule, $hasResource, $hasAction, $hasGroup) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
                
                // Only search in enhanced columns if they exist
                if ($hasModule) {
                    $q->orWhere('module', 'like', "%{$search}%");
                }
                if ($hasResource) {
                    $q->orWhere('resource', 'like', "%{$search}%");
                }
                if ($hasAction) {
                    $q->orWhere('action', 'like', "%{$search}%");
                }
                if ($hasGroup) {
                    $q->orWhere('group', 'like', "%{$search}%");
                }
            });
        }

        // Filter by module if provided and column exists
        if ($hasModule && $request->has('module') && !empty($request->module)) {
            $query->where('module', $request->module);
        }

        // Filter by group if provided and column exists
        if ($hasGroup && $request->has('group') && !empty($request->group)) {
            $query->where('group', $request->group);
        }

        // Total records
        $totalRecords = Permission::count();
        $filteredRecords = $query->count();

        // Sorting
        if ($request->has('order') && !empty($request->order)) {
            $orderColumn = $request->columns[$request->order[0]['column']]['data'];
            $orderDir = $request->order[0]['dir'];
            
            // Only order by column if it exists
            if (Schema::hasColumn('permissions', $orderColumn)) {
                $query->orderBy($orderColumn, $orderDir);
            } else {
                // Fallback to name if column doesn't exist
                $query->orderBy('name', $orderDir);
            }
        } else {
            // Default ordering - only use columns that exist
            if ($hasModule) {
                $query->orderBy('module');
            }
            if ($hasResource) {
                $query->orderBy('resource');
            }
            if ($hasSortOrder) {
                $query->orderBy('sort_order');
            }
            if ($hasAction) {
                $query->orderBy('action');
            }
            // Fallback to name if no enhanced columns
            if (!$hasModule && !$hasResource) {
                $query->orderBy('name');
            }
        }

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $permissions = $query->skip($start)->take($length)->get();

        // Format data to ensure all expected fields are present
        $formattedData = $permissions->map(function($permission) use ($hasModule, $hasResource, $hasAction, $hasGroup) {
            $data = $permission->toArray();
            
            // If columns don't exist, try to parse from slug
            if (!$hasModule || !$data['module']) {
                $data['module'] = $this->parseModuleFromSlug($data['slug']);
            }
            if (!$hasResource || !$data['resource']) {
                $data['resource'] = $this->parseResourceFromSlug($data['slug']);
            }
            if (!$hasAction || !$data['action']) {
                $data['action'] = $this->parseActionFromSlug($data['slug']);
            }
            if (!$hasGroup || !$data['group']) {
                $data['group'] = $this->parseGroupFromSlug($data['slug'], $data['module'] ?? null);
            }
            if (!isset($data['is_active'])) $data['is_active'] = true;
            
            return $data;
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $formattedData
        ]);
    }

    /**
     * Get single permission for editing.
     */
    public function edit(Request $request)
    {
        $permission = Permission::withCount('roles')->findOrFail($request->id);
        
        // Parse module, resource, action, group from slug if columns don't exist
        $hasModule = Schema::hasColumn('permissions', 'module');
        $hasResource = Schema::hasColumn('permissions', 'resource');
        $hasAction = Schema::hasColumn('permissions', 'action');
        $hasGroup = Schema::hasColumn('permissions', 'group');
        
        $permissionData = $permission->toArray();
        
        // If columns don't exist or are empty, parse from slug
        if (!$hasModule || empty($permissionData['module'])) {
            $permissionData['module'] = $this->parseModuleFromSlug($permission->slug);
        }
        if (!$hasResource || empty($permissionData['resource'])) {
            $permissionData['resource'] = $this->parseResourceFromSlug($permission->slug);
        }
        if (!$hasAction || empty($permissionData['action'])) {
            $permissionData['action'] = $this->parseActionFromSlug($permission->slug);
        }
        if (!$hasGroup || empty($permissionData['group'])) {
            $permissionData['group'] = $this->parseGroupFromSlug($permission->slug, $permissionData['module'] ?? null);
        }
        
        return response()->json([
            'success' => true,
            'permission' => $permissionData
        ]);
    }

    /**
     * Get permissions grouped by module/page.
     */
    public function getByModule()
    {
        $query = Permission::query();
        
        if (Schema::hasColumn('permissions', 'module')) {
            $query->orderBy('module');
        }
        if (Schema::hasColumn('permissions', 'resource')) {
            $query->orderBy('resource');
        }
        if (Schema::hasColumn('permissions', 'sort_order')) {
            $query->orderBy('sort_order');
        }
        if (Schema::hasColumn('permissions', 'action')) {
            $query->orderBy('action');
        }
        
        $permissions = $query->get();
        
        if (Schema::hasColumn('permissions', 'module')) {
            $permissions = $permissions->groupBy('module');
        } else {
            $permissions = $permissions->groupBy(function($item) {
                return 'default';
            });
        }

        return response()->json([
            'success' => true,
            'permissions' => $permissions
        ]);
    }

    /**
     * Get permissions grouped by group/page.
     */
    public function getByGroup()
    {
        $query = Permission::query();
        
        if (Schema::hasColumn('permissions', 'group')) {
            $query->orderBy('group');
        }
        if (Schema::hasColumn('permissions', 'module')) {
            $query->orderBy('module');
        }
        if (Schema::hasColumn('permissions', 'sort_order')) {
            $query->orderBy('sort_order');
        }
        
        $permissions = $query->get();
        
        if (Schema::hasColumn('permissions', 'group')) {
            $permissions = $permissions->groupBy('group');
        } else {
            $permissions = $permissions->groupBy(function($item) {
                return 'default';
            });
        }

        return response()->json([
            'success' => true,
            'permissions' => $permissions
        ]);
    }

    /**
     * Store a new permission - DISABLED
     * Permissions must be defined in code using PermissionService.
     */
    public function store(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Permissions cannot be created through the UI. They must be defined in code by developers using PermissionService.'
        ], 403);
    }

    /**
     * Update an existing permission.
     * Only description and is_active can be updated.
     */
    public function update(Request $request)
    {
        $permission = Permission::findOrFail($request->id);

        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Only allow updating description and is_active
        $updateData = [];
        
        if ($request->has('description')) {
            $updateData['description'] = $request->description;
        }
        
        // Only update is_active if column exists
        if (Schema::hasColumn('permissions', 'is_active') && $request->has('is_active')) {
            $updateData['is_active'] = $request->boolean('is_active');
        }

        $permission->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Permission updated successfully',
            'permission' => $permission->fresh(['roles'])
        ]);
    }

    /**
     * Delete a permission - DISABLED
     * Permissions cannot be deleted as they are defined in code.
     */
    public function delete(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Permissions cannot be deleted. They are defined in code and managed by developers.'
        ], 403);
    }

    /**
     * Parse module from permission slug
     */
    private function parseModuleFromSlug($slug)
    {
        // Map common permission patterns to modules
        $moduleMap = [
            'dashboard' => 'dashboard',
            'users' => 'users',
            'user' => 'users',
            'roles' => 'roles',
            'role' => 'roles',
            'permissions' => 'permissions',
            'permission' => 'permissions',
            'products' => 'products',
            'product' => 'products',
            'categories' => 'categories',
            'category' => 'categories',
            'orders' => 'orders',
            'order' => 'orders',
            'customers' => 'customers',
            'customer' => 'customers',
            'leads' => 'leads',
            'lead' => 'leads',
            'inventory' => 'inventory',
            'reports' => 'reports',
            'report' => 'reports',
            'settings' => 'settings',
            'setting' => 'settings',
        ];

        $parts = explode('-', $slug);
        
        // Check if any part matches a module
        foreach ($parts as $part) {
            if (isset($moduleMap[$part])) {
                return $moduleMap[$part];
            }
        }

        // Default: use first part as module
        return !empty($parts[0]) ? $parts[0] : null;
    }

    /**
     * Parse resource from permission slug
     */
    private function parseResourceFromSlug($slug)
    {
        $parts = explode('-', $slug);
        
        // If slug has format like "create-products", resource is "products"
        // If slug has format like "assign-roles", resource is "roles"
        if (count($parts) >= 2) {
            // Skip action (first part) and get resource
            return $parts[1];
        }
        
        return null;
    }

    /**
     * Parse action from permission slug
     */
    private function parseActionFromSlug($slug)
    {
        $actionMap = [
            'view' => 'view',
            'create' => 'create',
            'edit' => 'update',
            'update' => 'update',
            'delete' => 'delete',
            'export' => 'export',
            'import' => 'import',
            'assign' => 'assign',
            'approve' => 'approve',
            'reject' => 'reject',
        ];

        $parts = explode('-', $slug);
        
        // First part is usually the action
        if (!empty($parts[0])) {
            $action = strtolower($parts[0]);
            return $actionMap[$action] ?? $action;
        }
        
        return null;
    }

    /**
     * Parse group from permission slug and module
     */
    private function parseGroupFromSlug($slug, $module = null)
    {
        $groupMap = [
            'dashboard' => 'Dashboard',
            'users' => 'User Management',
            'roles' => 'Roles & Permissions',
            'permissions' => 'Roles & Permissions',
            'products' => 'Products',
            'categories' => 'Categories',
            'orders' => 'Orders',
            'customers' => 'Customers',
            'leads' => 'Leads',
            'inventory' => 'Inventory',
            'reports' => 'Reports',
            'settings' => 'Settings',
        ];

        if ($module && isset($groupMap[$module])) {
            return $groupMap[$module];
        }

        // Try to get from slug
        $moduleFromSlug = $this->parseModuleFromSlug($slug);
        if ($moduleFromSlug && isset($groupMap[$moduleFromSlug])) {
            return $groupMap[$moduleFromSlug];
        }

        return null;
    }
}
