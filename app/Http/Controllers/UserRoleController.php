<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserRoleController extends Controller
{
    /**
     * Display user roles management page.
     */
    public function index()
    {
        return view('admin.user-roles.index');
    }

    /**
     * Get users data for DataTables.
     */
    public function getData(Request $request)
    {
        $query = User::with('roles');

        // Search
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Total records
        $totalRecords = User::count();
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
        $users = $query->skip($start)->take($length)->get();

        // Format data
        $data = $users->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->implode(', '),
                'role_ids' => $user->roles->pluck('id')->toArray(),
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    /**
     * Get user roles for editing.
     */
    public function edit(Request $request)
    {
        $user = User::with('roles')->findOrFail($request->id);
        return response()->json([
            'success' => true,
            'user' => $user,
            'role_ids' => $user->roles->pluck('id')->toArray()
        ]);
    }

    /**
     * Update user roles.
     */
    public function update(Request $request)
    {
        $user = User::findOrFail($request->id);

        $validator = Validator::make($request->all(), [
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Sync roles
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        } else {
            $user->syncRoles([]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User roles updated successfully',
            'user' => $user->load('roles')
        ]);
    }

    /**
     * Get all roles for assignment.
     */
    public function getRoles()
    {
        $roles = Role::orderBy('name')->get();
        return response()->json([
            'success' => true,
            'roles' => $roles
        ]);
    }
}

