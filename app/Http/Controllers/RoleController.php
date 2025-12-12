<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    /**
     * Display roles management page.
     */
    public function index()
    {
        return view('admin.roles.index');
    }

    /**
     * Get roles data for DataTables.
     */
    public function getData(Request $request)
    {
        $query = Role::withCount(['users', 'permissions']);

        // Search
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Total records
        $totalRecords = Role::count();
        $filteredRecords = $query->count();

        // Sorting
        if ($request->has('order')) {
            $orderColumn = $request->columns[$request->order[0]['column']]['data'];
            $orderDir = $request->order[0]['dir'];
            $query->orderBy($orderColumn, $orderDir);
        } else {
            $query->latest();
        }

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $roles = $query->skip($start)->take($length)->get();

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $roles
        ]);
    }

    /**
     * Get single role for editing.
     */
    public function edit(Request $request)
    {
        $role = Role::with('permissions')->findOrFail($request->id);
        return response()->json([
            'success' => true,
            'role' => $role,
            'permission_ids' => $role->permissions->pluck('id')->toArray()
        ]);
    }

    /**
     * Store a new role.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $role = Role::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        // Attach permissions if provided
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'role' => $role->load('permissions')
        ]);
    }

    /**
     * Update an existing role.
     */
    public function update(Request $request)
    {
        $role = Role::findOrFail($request->id);

        // If only permissions are being updated (no name provided), skip name validation
        $rules = [
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ];
        
        if ($request->has('name')) {
            $rules['name'] = 'required|string|max:255|unique:roles,name,' . $role->id;
            $rules['description'] = 'nullable|string';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Update name and description only if provided
        if ($request->has('name')) {
            $role->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
            ]);
        }

        // Sync permissions (always update permissions if provided)
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        } else if (!$request->has('name')) {
            // If only permissions modal is open and no permissions sent, clear them
            $role->permissions()->sync([]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'role' => $role->load('permissions')
        ]);
    }

    /**
     * Delete a role.
     */
    public function delete(Request $request)
    {
        $role = Role::findOrFail($request->id);

        // Check if role has users
        if ($role->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete role that has users assigned to it.'
            ], 422);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully'
        ]);
    }

    /**
     * Get all permissions grouped by module for assignment.
     */
    public function getPermissions()
    {
        $permissions = Permission::where('is_active', true)
            ->orderBy('module')
            ->orderBy('sort_order')
            ->get();
        
        // Extract module and action from slug/name if module is null
        $permissions = $permissions->map(function($permission) {
            if (empty($permission->module)) {
                // Try to extract from slug - handle both formats:
                // New format: "module.resource.action" or "module.action"
                // Old format: "action-resource" or "action-module"
                
                $slug = $permission->slug;
                
                // Check if slug uses dots (new format)
                if (strpos($slug, '.') !== false) {
                    $slugParts = explode('.', $slug);
                    if (count($slugParts) >= 2) {
                        $permission->module = ucfirst($slugParts[0]);
                        $permission->resource = ucfirst($slugParts[1] ?? $slugParts[0]);
                        $permission->action = $slugParts[2] ?? $slugParts[1] ?? 'view';
                    }
                } else {
                    // Old format: "action-resource" (e.g., "view-categories", "create-products")
                    $slugParts = explode('-', $slug);
                    if (count($slugParts) >= 2) {
                        $permission->action = strtolower($slugParts[0]);
                        // Combine remaining parts as resource/module
                        $resource = ucfirst(implode(' ', array_slice($slugParts, 1)));
                        $permission->module = $resource;
                        $permission->resource = $resource;
                    } else {
                        // Fallback: extract from name
                        $nameParts = explode(' ', $permission->name);
                        if (count($nameParts) >= 2) {
                            $permission->action = strtolower($nameParts[0]);
                            $permission->module = ucfirst(implode(' ', array_slice($nameParts, 1)));
                            $permission->resource = $permission->module;
                        } else {
                            $permission->module = 'Other';
                            $permission->resource = 'Other';
                            $permission->action = 'view';
                        }
                    }
                }
            }
            
            // Set resource if empty
            if (empty($permission->resource)) {
                $permission->resource = $permission->module;
            }
            
            // Set action if empty
            if (empty($permission->action)) {
                // Try to extract from name (format: "Action Resource")
                $nameParts = explode(' ', $permission->name);
                if (count($nameParts) > 0) {
                    $permission->action = strtolower($nameParts[0]);
                } else {
                    $permission->action = 'view';
                }
            }
            
            return $permission;
        });
        
        // Group by module and resource
        $grouped = [];
        foreach ($permissions as $permission) {
            $module = $permission->module ?? 'Other';
            $resource = $permission->resource ?? $module;
            $key = $module . '.' . $resource;
            
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'module' => $module,
                    'resource' => $resource,
                    'permissions' => []
                ];
            }
            
            $grouped[$key]['permissions'][] = [
                'id' => $permission->id,
                'name' => $permission->name,
                'slug' => $permission->slug,
                'module' => $permission->module,
                'resource' => $permission->resource,
                'action' => $permission->action,
                'description' => $permission->description,
            ];
        }
        
        return response()->json([
            'success' => true,
            'permissions' => $permissions->map(function($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'module' => $p->module,
                    'resource' => $p->resource,
                    'action' => $p->action,
                    'description' => $p->description,
                ];
            }),
            'grouped' => array_values($grouped)
        ]);
    }

    /**
     * Assign role to users.
     */
    public function assignUsers(Request $request)
    {
        $role = Role::findOrFail($request->role_id);

        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->user_ids as $userId) {
            $user = User::find($userId);
            $user->assignRole($role);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role assigned to users successfully'
        ]);
    }
}
