<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
   
    public function index()
    {
        return view('admin.permissions.index');
    }

   
    public function getData(Request $request)
    {
        $query = Permission::withCount('roles');

        // Search
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%");
            });
        }

        // Total records
        $totalRecords = Permission::count();
        $filteredRecords = $query->count();

        // Sorting
        if ($request->has('order') && !empty($request->order)) {
            $orderColumn = $request->columns[$request->order[0]['column']]['data'];
            $orderDir = $request->order[0]['dir'];
            
            // Handle sort_no column ordering
            if ($orderColumn === 'sort_no') {
                $query->orderBy('sort_no', $orderDir);
            } elseif (Schema::hasColumn('permissions', $orderColumn)) {
                $query->orderBy($orderColumn, $orderDir);
            } else {
                // Fallback to name if column doesn't exist
                $query->orderBy('name', $orderDir);
            }
        } else {
            // Default ordering by sort_no, then by name
            $query->orderBy('sort_no', 'asc')->orderBy('name', 'asc');
        }
 
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $permissions = $query->skip($start)->take($length)->get();
 
        $formattedData = $permissions->map(function($permission) {
            $data = $permission->toArray();
            
            // Ensure sort_no is included (default to 0 if not set)
            $data['sort_no'] = $data['sort_no'] ?? 0;
            
            // Parse actions from action field (stored as JSON)
            if (!empty($data['action'])) {
                // Try to parse as JSON
                $actions = json_decode($data['action'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($actions)) {
                    $data['actions'] = $actions;
                } else {
                    // If not JSON, treat as single action or comma-separated
                    $data['actions'] = strpos($data['action'], ',') !== false 
                        ? array_map('trim', explode(',', $data['action']))
                        : [$data['action']];
                }
            } else {
                $data['actions'] = [];
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

 
    public function edit(Request $request)
    {
        $permission = Permission::withCount('roles')->findOrFail($request->id);
        
        $permissionData = $permission->toArray();
        
        // Parse actions from action field (stored as JSON)
        if (!empty($permissionData['action'])) {
            $actions = json_decode($permissionData['action'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($actions)) {
                $permissionData['actions'] = $actions;
            } else {
                // If not JSON, treat as single action or comma-separated
                $permissionData['actions'] = strpos($permissionData['action'], ',') !== false 
                    ? array_map('trim', explode(',', $permissionData['action']))
                    : [$permissionData['action']];
            }
        } else {
            $permissionData['actions'] = [];
        }
        
        return response()->json([
            'success' => true,
            'permission' => $permissionData
        ]);
    }


  
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'actions' => 'required|array|min:1',
            'actions.*' => 'required|string|max:255',
            'sort_no' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate slug if not provided
        $slug = $request->slug ?? strtolower(preg_replace('/[^a-z0-9]+/i', '_', $request->name));
        
        // Check if permission already exists
        $existingPermission = Permission::where('slug', $slug)->first();
        if ($existingPermission) {
            return response()->json([
                'success' => false,
                'message' => 'Permission with this slug already exists.'
            ], 422);
        }

        // Store actions as JSON string
        $actionsJson = json_encode($request->actions);

        // Create permission
        $permission = Permission::create([
            'name' => $request->name,
            'slug' => $slug,
            'action' => $actionsJson, // Store actions as JSON
            'is_active' => true,
            'sort_no' => $request->sort_no ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permission created successfully',
            'permission' => $permission
        ]);
    }
 
    public function update(Request $request)
    {
        $permission = Permission::findOrFail($request->id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'actions' => 'required|array|min:1',
            'actions.*' => 'string|max:255',
            'is_active' => 'nullable|boolean',
            'sort_no' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $name = $request->name;
        $actions = $request->actions;
        $actionsJson = json_encode($actions);

        // Create a unique slug based on name and actions
        $slug = Str::slug($name . ' ' . implode(' ', $actions));

        // Check if permission with this slug already exists (excluding current permission)
        $existingPermission = Permission::where('slug', $slug)
            ->where('id', '!=', $permission->id)
            ->first();
            
        if ($existingPermission) {
            return response()->json([
                'success' => false,
                'message' => 'A permission with these details already exists.'
            ], 422);
        }

        // Update permission
        $updateData = [
            'name' => $name,
            'slug' => $slug,
            'action' => $actionsJson, // Store actions as JSON string
            'sort_no' => $request->sort_no ?? $permission->sort_no ?? 0,
        ];
        
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
     * Update sort order for multiple permissions
     */
    public function updateSort(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*.id' => 'required|exists:permissions,id',
            'permissions.*.sort_no' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data provided',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
            
            foreach ($request->permissions as $permissionData) {
                $permission = Permission::findOrFail($permissionData['id']);
                
                // Update sort_no
                $permission->sort_no = $permissionData['sort_no'];
                $permission->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Sort order updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sort order: ' . $e->getMessage()
            ], 500);
        }
    }

   
    public function delete(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Permissions cannot be deleted. They are defined in code and managed by developers.'
        ], 403);
    }

    /**
     * Get permission structure (tabs, rows, actions)
     */
    public function getStructure()
    {
        $structureFile = storage_path('app/permission_structure.json');
        
        if (file_exists($structureFile)) {
            $structure = json_decode(file_get_contents($structureFile), true);
        } else {
            // Default structure
            $structure = [
                'tabs' => [
                    [
                        'key' => 'product',
                        'label' => 'Product',
                        'rows' => [
                            ['key' => 'product', 'label' => 'Product', 'actions' => ['view', 'create', 'update', 'delete', 'export']]
                        ]
                    ],
                    [
                        'key' => 'product_categorizations',
                        'label' => 'Product Categorizations',
                        'rows' => [
                            ['key' => 'brand', 'label' => 'Brands', 'actions' => ['view', 'create', 'update', 'delete']],
                            ['key' => 'category', 'label' => 'Categories', 'actions' => ['view', 'create', 'update', 'delete']],
                            ['key' => 'unit', 'label' => 'Units', 'actions' => ['view', 'create', 'update', 'delete']]
                        ]
                    ]
                ]
            ];
        }

        return response()->json([
            'success' => true,
            'structure' => $structure
        ]);
    }

    /**
     * Save permission structure
     */
    public function saveStructure(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'structure' => 'required|array',
            'structure.tabs' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $structureFile = storage_path('app/permission_structure.json');
        $structure = $request->structure;
        
        // Ensure directory exists
        $directory = dirname($structureFile);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($structureFile, json_encode($structure, JSON_PRETTY_PRINT));

        return response()->json([
            'success' => true,
            'message' => 'Permission structure saved successfully'
        ]);
    }

}
