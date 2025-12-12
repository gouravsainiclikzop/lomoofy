<?php

namespace App\Http\Controllers;

use App\Models\FieldManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Database\Seeders\FieldManagementSeeder;

class FieldManagementController extends Controller
{
    /**
     * Display the field management page
     */
    public function index()
    {
        return view('admin.field-management.index');
    }

    /**
     * Get all fields for DataTables
     */
    public function getData(Request $request)
    {
        $query = FieldManagement::query();

        // Search
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('field_key', 'like', "%{$search}%")
                  ->orWhere('label', 'like', "%{$search}%")
                  ->orWhere('field_group', 'like', "%{$search}%");
            });
        }

        $totalRecords = $query->count();
        $fields = $query->orderBy('is_system', 'desc') // System fields first
                       ->orderBy('is_active', 'desc')
                       ->orderBy('sort_order', 'asc')
                       ->orderBy('id', 'asc')
                       ->skip($request->start)
                       ->take($request->length)
                       ->get();

        $data = $fields->map(function($field) {
            // Always use isSystemField() to ensure system fields are properly identified
            $isSystem = $field->isSystemField();
            
            return [
                'id' => $field->id,
                'field_key' => $field->field_key,
                'label' => $field->label,
                'input_type' => $field->input_type,
                'field_group' => $field->field_group ?? '-',
                'is_required' => $field->is_required,
                'is_visible' => $field->is_visible,
                'sort_order' => $field->sort_order,
                'is_active' => $field->is_active,
                'is_system' => $isSystem,
                'created_at' => $field->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => FieldManagement::count(),
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ]);
    }

    /**
     * Get fields for customer form (only visible and active)
     */
    public function getFieldsForForm()
    {
        $fields = FieldManagement::active()
            ->visible()
            ->ordered()
            ->get()
            ->map(function($field) {
                return [
                    'field_key' => $field->field_key,
                    'label' => $field->label,
                    'input_type' => $field->input_type,
                    'placeholder' => $field->placeholder,
                    'is_required' => $field->is_required,
                    'field_group' => $field->field_group,
                    'options' => $field->options,
                    'conditional_rules' => $field->conditional_rules,
                    'validation_rules' => $field->validation_rules,
                    'help_text' => $field->help_text,
                    'sort_order' => $field->sort_order,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $fields
        ]);
    }

    /**
     * Get all fields for preview (including inactive)
     */
    public function getAllFieldsForPreview()
    {
        $fields = FieldManagement::orderBy('is_active', 'desc')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function($field) {
                return [
                    'field_key' => $field->field_key,
                    'label' => $field->label,
                    'input_type' => $field->input_type,
                    'placeholder' => $field->placeholder,
                    'is_required' => $field->is_required,
                    'is_visible' => $field->is_visible,
                    'is_active' => $field->is_active,
                    'field_group' => $field->field_group,
                    'options' => $field->options,
                    'conditional_rules' => $field->conditional_rules,
                    'validation_rules' => $field->validation_rules,
                    'help_text' => $field->help_text,
                    'sort_order' => $field->sort_order,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $fields
        ]);
    }

    /**
     * Store a new field
     */
    public function store(Request $request)
    {
        // Convert string booleans to actual booleans before validation
        $data = $request->all();
        
        // Handle boolean conversion - handle various formats
        if (isset($data['is_required'])) {
            if ($data['is_required'] === '1' || $data['is_required'] === 1 || $data['is_required'] === true || $data['is_required'] === 'true') {
                $data['is_required'] = true;
            } else {
                $data['is_required'] = false;
            }
        } else {
            $data['is_required'] = false;
        }
        
        if (isset($data['is_visible'])) {
            if ($data['is_visible'] === '1' || $data['is_visible'] === 1 || $data['is_visible'] === true || $data['is_visible'] === 'true') {
                $data['is_visible'] = true;
            } else {
                $data['is_visible'] = false;
            }
        } else {
            $data['is_visible'] = true;
        }
        
        if (isset($data['is_active'])) {
            if ($data['is_active'] === '1' || $data['is_active'] === 1 || $data['is_active'] === true || $data['is_active'] === 'true') {
                $data['is_active'] = true;
            } else {
                $data['is_active'] = false;
            }
        } else {
            $data['is_active'] = true;
        }
        
        $validator = Validator::make($data, [
            'field_key' => 'required|string|max:255|unique:field_management_fields,field_key',
            'label' => 'required|string|max:255',
            'input_type' => 'required|string|in:text,email,password,select,textarea,date,file,checkbox,radio,number,tel',
            'placeholder' => 'nullable|string|max:255',
            'is_required' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'field_group' => 'nullable|string|max:255',
            'options' => 'nullable|array',
            'conditional_rules' => 'nullable|array',
            'validation_rules' => 'nullable|string',
            'help_text' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $field = FieldManagement::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Field created successfully',
            'data' => $field
        ]);
    }

    /**
     * Update a field
     */
    public function update(Request $request, $id)
    {
        $field = FieldManagement::findOrFail($id);

        // Prevent editing system fields
        if ($field->isSystemField()) {
            return response()->json([
                'success' => false,
                'message' => 'System fields cannot be edited. These are core required fields.',
                'errors' => ['field_key' => ['This is a system field and cannot be modified.']]
            ], 403);
        }

        // Convert string booleans to actual booleans before validation
        $data = $request->all();
        
        // Remove is_system from data if present (cannot be changed)
        unset($data['is_system']);
        
        // Handle boolean conversion - handle various formats
        if (isset($data['is_required'])) {
            if ($data['is_required'] === '1' || $data['is_required'] === 1 || $data['is_required'] === true || $data['is_required'] === 'true') {
                $data['is_required'] = true;
            } else {
                $data['is_required'] = false;
            }
        } else {
            $data['is_required'] = false;
        }
        
        if (isset($data['is_visible'])) {
            if ($data['is_visible'] === '1' || $data['is_visible'] === 1 || $data['is_visible'] === true || $data['is_visible'] === 'true') {
                $data['is_visible'] = true;
            } else {
                $data['is_visible'] = false;
            }
        } else {
            $data['is_visible'] = true;
        }
        
        if (isset($data['is_active'])) {
            if ($data['is_active'] === '1' || $data['is_active'] === 1 || $data['is_active'] === true || $data['is_active'] === 'true') {
                $data['is_active'] = true;
            } else {
                $data['is_active'] = false;
            }
        } else {
            $data['is_active'] = true;
        }
        
        // For system fields, only allow updating non-critical fields
        $validationRules = [
            'field_key' => 'required|string|max:255|unique:field_management_fields,field_key,' . $id,
            'label' => 'required|string|max:255',
            'input_type' => 'required|string|in:text,email,password,select,textarea,date,file,checkbox,radio,number,tel',
            'placeholder' => 'nullable|string|max:255',
            'is_required' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'field_group' => 'nullable|string|max:255',
            'options' => 'nullable|array',
            'conditional_rules' => 'nullable|array',
            'validation_rules' => 'nullable|string',
            'help_text' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];

        $validator = Validator::make($data, $validationRules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $field->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Field updated successfully',
            'data' => $field
        ]);
    }

    /**
     * Delete a field
     */
    public function destroy($id)
    {
        $field = FieldManagement::findOrFail($id);

        // Prevent deleting system fields
        if ($field->isSystemField()) {
            return response()->json([
                'success' => false,
                'message' => 'System fields cannot be deleted. These are core required fields.',
                'errors' => ['field_key' => ['This is a system field and cannot be deleted.']]
            ], 403);
        }

        $field->delete();

        return response()->json([
            'success' => true,
            'message' => 'Field deleted successfully'
        ]);
    }

    /**
     * Get a single field for editing
     */
    public function edit($id)
    {
        $field = FieldManagement::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $field
        ]);
    }

    /**
     * Seed initial field data
     */
    public function seedInitialData()
    {
        try {
            $seeder = new FieldManagementSeeder();
            $seeder->run();
            
            return response()->json([
                'success' => true,
                'message' => 'Initial field data seeded successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error seeding data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle field status (is_active)
     */
    public function toggleStatus(Request $request, $id)
    {
        $field = FieldManagement::findOrFail($id);
        
        // Prevent toggling system fields
        if ($field->isSystemField()) {
            return response()->json([
                'success' => false,
                'message' => 'System fields cannot be deactivated. These are core required fields.',
            ], 403);
        }
        
        $field->is_active = $request->is_active;
        $field->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => $field
        ]);
    }

    /**
     * Toggle field visibility (is_visible)
     */
    public function toggleVisible(Request $request, $id)
    {
        $field = FieldManagement::findOrFail($id);
        
        // Prevent hiding system fields
        if ($field->isSystemField() && !$request->is_visible) {
            return response()->json([
                'success' => false,
                'message' => 'System fields cannot be hidden. These are core required fields.',
            ], 403);
        }
        
        $field->is_visible = $request->is_visible;
        $field->save();

        return response()->json([
            'success' => true,
            'message' => 'Visibility updated successfully',
            'data' => $field
        ]);
    }

    /**
     * Toggle field required status (is_required)
     */
    public function toggleRequired(Request $request, $id)
    {
        $field = FieldManagement::findOrFail($id);
        
        // Prevent making system fields optional
        if ($field->isSystemField() && !$request->is_required) {
            return response()->json([
                'success' => false,
                'message' => 'System fields must remain required. These are core required fields.',
            ], 403);
        }
        
        $field->is_required = $request->is_required;
        $field->save();

        return response()->json([
            'success' => true,
            'message' => 'Required status updated successfully',
            'data' => $field
        ]);
    }

    /**
     * Update field order and group
     */
    public function updateOrder(Request $request, $fieldKey)
    {
        $field = FieldManagement::where('field_key', $fieldKey)->firstOrFail();
        
        if ($request->has('field_group')) {
            $field->field_group = $request->field_group;
        }
        
        if ($request->has('sort_order')) {
            $field->sort_order = $request->sort_order;
        }
        
        $field->save();

        return response()->json([
            'success' => true,
            'message' => 'Field order updated successfully',
            'data' => $field
        ]);
    }

    /**
     * Sync system fields in database
     * Ensures all system fields have is_system = true in the database
     */
    public function syncSystemFields()
    {
        try {
            $systemFieldKeys = FieldManagement::getSystemFields();
            
            foreach ($systemFieldKeys as $fieldKey) {
                FieldManagement::where('field_key', $fieldKey)
                    ->update([
                        'is_system' => true,
                        'is_active' => true,
                        'is_visible' => true,
                    ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'System fields synced successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error syncing system fields: ' . $e->getMessage(),
            ], 500);
        }
    }
}
