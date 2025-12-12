<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\FieldManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Display customers listing page
     */
    public function index()
    {
        return view('admin.customers.index');
    }

    /**
     * Get fields for customer form dynamically
     * Always includes system fields even if they're inactive
     */
    public function getFields()
    {
        // Get all active visible fields
        $fields = FieldManagement::active()
            ->visible()
            ->ordered()
            ->get();

        // Also include system fields even if they're inactive (they should always be shown)
        $systemFields = FieldManagement::whereIn('field_key', FieldManagement::getSystemFields())
            ->get()
            ->keyBy('field_key');

        // Merge system fields with regular fields, ensuring system fields are always included
        $allFields = $fields->keyBy('field_key');
        foreach ($systemFields as $key => $systemField) {
            $allFields->put($key, $systemField);
        }

        $fieldsData = $allFields->values()->map(function($field) {
            return [
                'field_key' => $field->field_key,
                'label' => $field->label,
                'input_type' => $field->input_type,
                'placeholder' => $field->placeholder,
                'is_required' => $field->is_required || $field->is_system,
                'field_group' => $field->field_group,
                'options' => $field->options,
                'conditional_rules' => $field->conditional_rules,
                'validation_rules' => $field->validation_rules,
                'help_text' => $field->help_text,
                'sort_order' => $field->sort_order,
                'is_system' => $field->is_system ?? false,
            ];
        })->sortBy('sort_order')->values();

        return response()->json([
            'success' => true,
            'data' => $fieldsData
        ]);
    }

    /**
     * Get customers data for DataTables
     */
    public function getData(Request $request)
    {
        $query = Customer::with('addresses');

        // Search
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $totalRecords = $query->count();
        $customers = $query->orderBy('created_at', 'desc')
                          ->skip($request->start)
                          ->take($request->length)
                          ->get();

        $data = $customers->map(function($customer) {
            $defaultAddress = $customer->addresses->where('is_default', true)->first();
            return [
                'id' => $customer->id,
                'full_name' => $customer->full_name,
                'email' => $customer->email,
                'phone' => $customer->phone ?? '-',
                'address' => $defaultAddress ? 
                    ($defaultAddress->city . ', ' . $defaultAddress->state) : '-',
                'profile_image' => $customer->profile_image,
                'is_active' => $customer->is_active,
                'created_at' => $customer->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => Customer::count(),
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ]);
    }

    /**
     * Store a new customer
     */
    public function store(Request $request)
    {
        // Get field definitions - include system fields even if inactive
        $fields = FieldManagement::active()->visible()->get();
        
        // Also include system fields (they should always be available)
        $systemFields = FieldManagement::whereIn('field_key', FieldManagement::getSystemFields())->get();
        $fields = $fields->merge($systemFields)->unique('field_key');
        
        // Build validation rules dynamically
        $rules = [
            'email' => 'required|email|unique:customers,email',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ];

        $fieldRules = [];
        foreach ($fields as $field) {
            $fieldKey = $field->field_key;
            $rule = [];
            
            // Add custom validation rules first if provided
            if ($field->validation_rules && !empty(trim($field->validation_rules))) {
                $customRules = array_filter(explode('|', $field->validation_rules), function($r) {
                    return !empty(trim($r));
                });
                $rule = array_merge($rule, $customRules);
            }
            
            // Add required/nullable based on field configuration (override if not in custom rules)
            $hasRequired = in_array('required', $rule);
            $hasNullable = in_array('nullable', $rule);
            
            if (!$hasRequired && !$hasNullable) {
                if ($field->is_required) {
                    $rule[] = 'required';
                } else {
                    $rule[] = 'nullable';
                }
            }

            // Add type-specific rules (only if not already present)
            switch ($field->input_type) {
                case 'email':
                    if (!in_array('email', $rule)) {
                        $rule[] = 'email';
                    }
                    break;
                case 'number':
                    if (!in_array('numeric', $rule)) {
                        $rule[] = 'numeric';
                    }
                    break;
                case 'file':
                    if (!in_array('file', $rule)) {
                        $rule[] = 'file';
                    }
                    if ($field->field_key === 'profile_image' && !in_array('image', $rule)) {
                        $rule[] = 'image|mimes:jpeg,png,jpg,gif|max:2048';
                    }
                    break;
                case 'date':
                    if (!in_array('date', $rule)) {
                        $rule[] = 'date';
                    }
                    break;
            }

            // Remove duplicates while preserving order
            $rule = array_values(array_unique($rule));
            
            $fieldRules[$fieldKey] = implode('|', $rule);
        }

        // Merge field rules, but preserve email unique constraint
        $rules = array_merge($rules, $fieldRules);
        
        // Ensure email has unique constraint even if field management overrides it
        if (isset($fieldRules['email'])) {
            // Merge unique constraint with field rules
            $emailRules = explode('|', $fieldRules['email']);
            if (!in_array('unique:customers,email', $emailRules)) {
                $emailRules[] = 'unique:customers,email';
            }
            $rules['email'] = implode('|', $emailRules);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Prepare customer data
            $customerData = [
                'full_name' => $request->full_name ?? '',
                'phone' => $request->phone ?? null,
                'alternate_phone' => $request->alternate_phone ?? null,
                'email' => $request->email,
                'password' => $request->password,
                'date_of_birth' => $request->date_of_birth ?? null,
                'gender' => $request->gender ?? null,
                'preferred_contact_method' => $request->preferred_contact_method ?? null,
                'preferred_payment_method' => $request->preferred_payment_method ?? null,
                'preferred_delivery_slot' => $request->preferred_delivery_slot ?? null,
                'newsletter_opt_in' => $request->has('newsletter_opt_in') ? true : false,
                'tags' => $request->tags ? json_decode($request->tags, true) : null,
                'risk_flags' => $request->risk_flags ? json_decode($request->risk_flags, true) : null,
                'notes' => $request->notes ?? null,
            ];

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $customerData['profile_image'] = $request->file('profile_image')->store('customer-profiles', 'public');
            }

            // Store custom field data
            $customData = [];
            foreach ($fields as $field) {
                $fieldKey = $field->field_key;
                if ($request->has($fieldKey) && !in_array($fieldKey, ['full_name', 'phone', 'alternate_phone', 'email', 'password', 'date_of_birth', 'gender', 'profile_image', 'preferred_contact_method', 'preferred_payment_method', 'preferred_delivery_slot', 'newsletter_opt_in', 'tags', 'risk_flags', 'notes'])) {
                    $customData[$fieldKey] = $request->$fieldKey;
                }
            }
            if (!empty($customData)) {
                $customerData['custom_data'] = $customData;
            }

            $customer = Customer::create($customerData);

            // Handle address
            if ($request->has('address_type')) {
                $addressData = [
                    'customer_id' => $customer->id,
                    'address_type' => $request->address_type ?? 'home',
                    'full_address' => $request->full_address ?? '',
                    'landmark' => $request->landmark ?? null,
                    'state' => $request->state ?? '',
                    'city' => $request->city ?? '',
                    'pincode' => $request->pincode ?? '',
                    'delivery_instructions' => $request->delivery_instructions ?? null,
                    'is_default' => $request->has('make_default_address') ? true : false,
                ];
                CustomerAddress::create($addressData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'data' => $customer->load('addresses')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a single customer for editing
     */
    public function edit($id)
    {
        $customer = Customer::with('addresses')->findOrFail($id);
        
        // Format customer data for form
        $customerData = [
            'id' => $customer->id,
            'full_name' => $customer->full_name,
            'phone' => $customer->phone,
            'alternate_phone' => $customer->alternate_phone,
            'email' => $customer->email,
            'date_of_birth' => $customer->date_of_birth ? $customer->date_of_birth->format('Y-m-d') : null,
            'gender' => $customer->gender,
            'preferred_contact_method' => $customer->preferred_contact_method,
            'preferred_payment_method' => $customer->preferred_payment_method,
            'preferred_delivery_slot' => $customer->preferred_delivery_slot,
            'newsletter_opt_in' => $customer->newsletter_opt_in,
            'tags' => $customer->tags ? implode(',', $customer->tags) : null,
            'risk_flags' => $customer->risk_flags ? implode(',', $customer->risk_flags) : null,
            'notes' => $customer->notes,
            'addresses' => $customer->addresses->map(function($addr) {
                return [
                    'id' => $addr->id,
                    'address_type' => $addr->address_type,
                    'full_address' => $addr->full_address,
                    'landmark' => $addr->landmark,
                    'state' => $addr->state,
                    'city' => $addr->city,
                    'pincode' => $addr->pincode,
                    'delivery_instructions' => $addr->delivery_instructions,
                    'is_default' => $addr->is_default,
                ];
            }),
            'custom_data' => $customer->custom_data ?? [],
        ];
        
        return response()->json([
            'success' => true,
            'data' => $customerData
        ]);
    }

    /**
     * Update a customer
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $fields = FieldManagement::active()->visible()->get();

        $rules = [
            'email' => 'required|email|unique:customers,email,' . $id,
        ];

        // Password is optional on update
        if ($request->has('password') && $request->password) {
            $rules['password'] = 'string|min:8|confirmed';
        }

        $fieldRules = [];
        foreach ($fields as $field) {
            $fieldKey = $field->field_key;
            $rule = [];
            
            // Add custom validation rules first if provided
            if ($field->validation_rules && !empty(trim($field->validation_rules))) {
                $customRules = array_filter(explode('|', $field->validation_rules), function($r) {
                    return !empty(trim($r));
                });
                $rule = array_merge($rule, $customRules);
            }
            
            // Add required/nullable based on field configuration (override if not in custom rules)
            $hasRequired = in_array('required', $rule);
            $hasNullable = in_array('nullable', $rule);
            
            if (!$hasRequired && !$hasNullable) {
                if ($field->is_required) {
                    $rule[] = 'required';
                } else {
                    $rule[] = 'nullable';
                }
            }

            // Add type-specific rules (only if not already present)
            switch ($field->input_type) {
                case 'email':
                    if (!in_array('email', $rule)) {
                        $rule[] = 'email';
                    }
                    break;
                case 'number':
                    if (!in_array('numeric', $rule)) {
                        $rule[] = 'numeric';
                    }
                    break;
                case 'file':
                    if (!in_array('file', $rule)) {
                        $rule[] = 'file';
                    }
                    if ($field->field_key === 'profile_image' && !in_array('image', $rule)) {
                        $rule[] = 'image|mimes:jpeg,png,jpg,gif|max:2048';
                    }
                    break;
                case 'date':
                    if (!in_array('date', $rule)) {
                        $rule[] = 'date';
                    }
                    break;
            }

            // Remove duplicates while preserving order
            $rule = array_values(array_unique($rule));

            $fieldRules[$fieldKey] = implode('|', $rule);
        }

        // Merge field rules, but preserve email unique constraint
        $rules = array_merge($rules, $fieldRules);
        
        // Ensure email has unique constraint even if field management overrides it
        if (isset($fieldRules['email'])) {
            // Merge unique constraint with field rules, excluding current customer
            $emailRules = explode('|', $fieldRules['email']);
            $uniqueRule = 'unique:customers,email,' . $id;
            if (!in_array($uniqueRule, $emailRules) && !in_array('unique:customers,email', $emailRules)) {
                $emailRules[] = $uniqueRule;
            }
            $rules['email'] = implode('|', $emailRules);
        }
        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $customerData = [
                'full_name' => $request->full_name ?? $customer->full_name,
                'phone' => $request->phone ?? $customer->phone,
                'alternate_phone' => $request->alternate_phone ?? $customer->alternate_phone,
                'email' => $request->email,
                'date_of_birth' => $request->date_of_birth ?? $customer->date_of_birth,
                'gender' => $request->gender ?? $customer->gender,
                'preferred_contact_method' => $request->preferred_contact_method ?? $customer->preferred_contact_method,
                'preferred_payment_method' => $request->preferred_payment_method ?? $customer->preferred_payment_method,
                'preferred_delivery_slot' => $request->preferred_delivery_slot ?? $customer->preferred_delivery_slot,
                'newsletter_opt_in' => $request->has('newsletter_opt_in') ? true : false,
                'tags' => $request->tags ? json_decode($request->tags, true) : $customer->tags,
                'risk_flags' => $request->risk_flags ? json_decode($request->risk_flags, true) : $customer->risk_flags,
                'notes' => $request->notes ?? $customer->notes,
            ];

            if ($request->has('password') && $request->password) {
                $customerData['password'] = $request->password;
            }

            if ($request->hasFile('profile_image')) {
                // Delete old image
                if ($customer->profile_image) {
                    Storage::disk('public')->delete($customer->profile_image);
                }
                $customerData['profile_image'] = $request->file('profile_image')->store('customer-profiles', 'public');
            }

            // Update custom data
            $customData = $customer->custom_data ?? [];
            foreach ($fields as $field) {
                $fieldKey = $field->field_key;
                if ($request->has($fieldKey) && !in_array($fieldKey, ['full_name', 'phone', 'alternate_phone', 'email', 'password', 'date_of_birth', 'gender', 'profile_image', 'preferred_contact_method', 'preferred_payment_method', 'preferred_delivery_slot', 'newsletter_opt_in', 'tags', 'risk_flags', 'notes'])) {
                    $customData[$fieldKey] = $request->$fieldKey;
                }
            }
            if (!empty($customData)) {
                $customerData['custom_data'] = $customData;
            }

            $customer->update($customerData);

            // Update or create address
            if ($request->has('address_type')) {
                $address = $customer->addresses->first();
                $addressData = [
                    'address_type' => $request->address_type ?? 'home',
                    'full_address' => $request->full_address ?? '',
                    'landmark' => $request->landmark ?? null,
                    'state' => $request->state ?? '',
                    'city' => $request->city ?? '',
                    'pincode' => $request->pincode ?? '',
                    'delivery_instructions' => $request->delivery_instructions ?? null,
                    'is_default' => $request->has('make_default_address') ? true : false,
                ];
                
                if ($address) {
                    $address->update($addressData);
                } else {
                    $addressData['customer_id'] = $customer->id;
                    CustomerAddress::create($addressData);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully',
                'data' => $customer->load('addresses')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a customer
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        
        // Delete profile image
        if ($customer->profile_image) {
            Storage::disk('public')->delete($customer->profile_image);
        }
        
        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully'
        ]);
    }
}
