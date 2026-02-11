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
        
        // Get permissions with their selected actions
        $permissionsData = $role->permissions->map(function($permission) {
            $actions = [];
            if ($permission->pivot->actions) {
                $actions = json_decode($permission->pivot->actions, true);
                if (!is_array($actions)) {
                    $actions = [];
                }
            }
            return [
                'id' => $permission->id,
                'actions' => $actions
            ];
        });
        
        return response()->json([
            'success' => true,
            'role' => $role,
            'permission_ids' => $role->permissions->pluck('id')->toArray(),
            'permissions_data' => $permissionsData
        ]);
    }

    /**
     * Store a new role.
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string',
        ];
        
        // Only validate permissions if they are provided
        if ($request->has('permissions') && is_array($request->permissions) && count($request->permissions) > 0) {
            $rules['permissions'] = 'array';
            $rules['permissions.*.id'] = 'required|exists:permissions,id';
            $rules['permissions.*.actions'] = 'nullable|array';
            $rules['permissions.*.actions.*'] = 'string';
        }
        
        $validator = Validator::make($request->all(), $rules);

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

        // Attach permissions with actions if provided
        if ($request->has('permissions')) {
            $syncData = [];
            foreach ($request->permissions as $permData) {
                // Ensure permData is an array and has an id
                if (!is_array($permData) || !isset($permData['id'])) {
                    continue; // Skip invalid entries
                }
                
                $permissionId = $permData['id'];
                $actions = isset($permData['actions']) && is_array($permData['actions']) ? $permData['actions'] : [];
                
                $syncData[$permissionId] = [
                    'actions' => json_encode($actions)
                ];
            }
            $role->permissions()->sync($syncData);
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
 
        $rules = [];
        
        if ($request->has('name')) {
            $rules['name'] = 'required|string|max:255|unique:roles,name,' . $role->id;
            $rules['description'] = 'nullable|string';
        }

        // Only validate permissions if they are provided
        if ($request->has('permissions')) {
            $rules['permissions'] = 'nullable|array';
            $rules['permissions.*.id'] = 'required|exists:permissions,id';
            $rules['permissions.*.actions'] = 'nullable|array';
            $rules['permissions.*.actions.*'] = 'string';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
 
        if ($request->has('name')) {
            $role->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
            ]);
        }

        if ($request->has('permissions')) {
            $syncData = [];
            foreach ($request->permissions as $permData) {
                // Ensure permData is an array and has an id
                if (!is_array($permData) || !isset($permData['id'])) {
                    continue; // Skip invalid entries
                }
                
                $permissionId = $permData['id'];
                $actions = isset($permData['actions']) && is_array($permData['actions']) ? $permData['actions'] : [];
                
                $syncData[$permissionId] = [
                    'actions' => json_encode($actions)
                ];
            }
            $role->permissions()->sync($syncData);
        } else if (!$request->has('name')) { 
            $role->permissions()->sync([]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'role' => $role->load('permissions')
        ]);
    }
 
    public function delete(Request $request)
    {
        $role = Role::findOrFail($request->id);
 
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
 
   
    public function getPermissions()
    {
        // Order by sort_no, then by name
        $permissions = Permission::where('is_active', true)
            ->orderBy('sort_no', 'asc')
            ->orderBy('name', 'asc')
            ->get();
        
        $allPermissions = [];
        $grouped = [];

        foreach ($permissions as $permission) {
            // Parse actions from action field (stored as JSON)
            $actions = [];
            if (!empty($permission->action)) {
                $parsedActions = json_decode($permission->action, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($parsedActions)) {
                    $actions = $parsedActions;
                } else {
                    // If not JSON, treat as single action or comma-separated
                    $actions = strpos($permission->action, ',') !== false 
                        ? array_map('trim', explode(',', $permission->action))
                        : [$permission->action];
                }
            }

            // Create permission data with actions
            $permissionData = [
                'id' => $permission->id,
                'name' => $permission->name,
                'slug' => $permission->slug,
                'actions' => $actions,
            ];
            
            $allPermissions[] = $permissionData;
            
            // Group by permission name (for display purposes)
            $groupKey = $permission->name;
            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [
                    'name' => $permission->name,
                    'permissions' => []
                ];
            }
            $grouped[$groupKey]['permissions'][] = $permissionData;
        }

        return response()->json([
            'success' => true,
            'permissions' => $allPermissions,
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
