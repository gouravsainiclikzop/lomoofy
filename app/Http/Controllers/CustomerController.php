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
        $systemFieldKeys = FieldManagement::getSystemFields();
        $systemFields = FieldManagement::whereIn('field_key', $systemFieldKeys)
            ->get()
            ->keyBy('field_key');
        
        // Create missing system fields on the fly
        foreach ($systemFieldKeys as $fieldKey) {
            if (!isset($systemFields[$fieldKey])) {
                // Create the missing system field
                $fieldData = $this->getSystemFieldDefaults($fieldKey);
                if ($fieldData) {
                    $newField = FieldManagement::create($fieldData);
                    $systemFields[$fieldKey] = $newField;
                }
            }
        }

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
        $query = Customer::with(['addresses', 'orders', 'carts.items']);

        // Search
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by Date Range
        if ($request->filled('date_range')) {
            $dateRange = $request->date_range;
            $now = now();
            
            switch ($dateRange) {
                case 'today':
                    $query->whereDate('created_at', $now->toDateString());
                    break;
                case 'last_7_days':
                    $query->where('created_at', '>=', $now->copy()->subDays(7));
                    break;
                case 'last_30_days':
                    $query->where('created_at', '>=', $now->copy()->subDays(30));
                    break;
                case 'last_90_days':
                    $query->where('created_at', '>=', $now->copy()->subDays(90));
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', $now->month)
                          ->whereYear('created_at', $now->year);
                    break;
                case 'last_month':
                    $lastMonth = $now->copy()->subMonth();
                    $query->whereMonth('created_at', $lastMonth->month)
                          ->whereYear('created_at', $lastMonth->year);
                    break;
                case 'this_year':
                    $query->whereYear('created_at', $now->year);
                    break;
            }
        }

        // Filter by Area (State or City)
        if ($request->filled('area')) {
            $area = $request->area;
            $query->whereHas('addresses', function($q) use ($area) {
                $q->where('state', $area)
                  ->orWhere('city', $area);
            });
        }

        $totalRecords = $query->count();
        
        $customers = $query->orderBy('created_at', 'desc')
                          ->skip($request->start)
                          ->take($request->length)
                          ->get();

        $data = $customers->map(function($customer) {
            $defaultAddress = $customer->addresses->where('is_default', true)->first();
            $ordersCount = $customer->orders->count();
            $ordersTotal = $customer->orders->sum('total_amount');
            $pendingOrdersCount = $customer->orders->where('status', 'pending')->count();
            $cartItemsCount = $customer->carts->sum(function($cart) {
                return $cart->items->sum('quantity');
            });
            
            return [
                'id' => $customer->id,
                'full_name' => $customer->full_name,
                'email' => $customer->email,
                'phone' => $customer->phone ?? '-',
                'alternate_phone' => $customer->alternate_phone ?? '-',
                'profile_image' => $customer->profile_image ? asset('storage/' . $customer->profile_image) : asset('assets/images/placeholder.jpg'),
                'addresses' => $customer->addresses->map(function($addr) {
                    return [
                        'id' => $addr->id,
                        'address_type' => $addr->address_type,
                        'address_line1' => $addr->address_line1,
                        'address_line2' => $addr->address_line2,
                        'country' => $addr->country ?? '',
                        'city' => $addr->city,
                        'state' => $addr->state,
                        'pincode' => $addr->pincode,
                        'is_default' => $addr->is_default,
                    ];
                }),
                'addresses_count' => $customer->addresses->count(),
                'default_address' => $defaultAddress ? 
                    (trim(($defaultAddress->address_line1 ?? '') . ' ' . ($defaultAddress->address_line2 ?? '')) . ', ' . $defaultAddress->city . ', ' . $defaultAddress->state . ' - ' . $defaultAddress->pincode) : '-',
                'default_address_full' => $defaultAddress ? [
                    'address_line1' => $defaultAddress->address_line1,
                    'address_line2' => $defaultAddress->address_line2,
                    'country' => $defaultAddress->country ?? '',
                    'city' => $defaultAddress->city,
                    'state' => $defaultAddress->state,
                    'pincode' => $defaultAddress->pincode,
                    'landmark' => $defaultAddress->landmark,
                    'address_type' => $defaultAddress->address_type,
                ] : null,
                'orders_count' => $ordersCount,
                'orders_total' => number_format($ordersTotal, 2),
                'orders_total_raw' => $ordersTotal, // For sorting
                'pending_orders_count' => $pendingOrdersCount,
                'cart_items_count' => $cartItemsCount,
                'is_active' => $customer->is_active,
                'created_at' => $customer->created_at->format('Y-m-d H:i:s'),
            ];
        });
        
        // Sort by order count or order amount if requested
        if ($request->filled('order_count_sort')) {
            $direction = $request->order_count_sort === 'highest' ? 'desc' : 'asc';
            $data = $data->sortBy('orders_count', SORT_REGULAR, $direction === 'desc')->values();
        }
        
        if ($request->filled('order_amount_sort')) {
            $direction = $request->order_amount_sort === 'highest' ? 'desc' : 'asc';
            $data = $data->sortBy('orders_total_raw', SORT_REGULAR, $direction === 'desc')->values();
        }

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
                    'address_line1' => $request->address_line1 ?? '',
                    'address_line2' => $request->address_line2 ?? null,
                    'landmark' => $request->landmark ?? null,
                    'country' => $request->country ?? '',
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
                    'address_line1' => $addr->address_line1,
                    'address_line2' => $addr->address_line2,
                    'landmark' => $addr->landmark,
                    'country' => $addr->country ?? '',
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

        // Address fields that should be excluded from field management validation
        $addressFields = ['address_type', 'address_line1', 'address_line2', 'landmark', 'country', 'state', 'city', 'pincode', 'delivery_instructions', 'make_default_address'];
        
        $fieldRules = [];
        foreach ($fields as $field) {
            $fieldKey = $field->field_key;
            
            // Skip address fields - they're handled separately
            if (in_array($fieldKey, $addressFields)) {
                continue;
            }
            
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
        
        // Make address fields conditionally required (only if address_type is provided)
        if ($request->has('address_type') && $request->address_type) {
            // Address fields are required only if address_type is provided
            if (!isset($rules['state']) || !str_contains($rules['state'], 'required')) {
                $rules['state'] = 'required|string';
            }
            if (!isset($rules['city']) || !str_contains($rules['city'], 'required')) {
                $rules['city'] = 'required|string';
            }
            if (!isset($rules['pincode']) || !str_contains($rules['pincode'], 'required')) {
                $rules['pincode'] = 'required|string';
            }
        } else {
            // Address fields are optional if address_type is not provided
            $rules['address_type'] = 'nullable|string';
            $rules['state'] = 'nullable|string';
            $rules['city'] = 'nullable|string';
            $rules['pincode'] = 'nullable|string';
            $rules['address_line1'] = 'nullable|string';
            $rules['address_line2'] = 'nullable|string';
            $rules['landmark'] = 'nullable|string';
            $rules['delivery_instructions'] = 'nullable|string';
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
                    'address_line1' => $request->address_line1 ?? '',
                    'address_line2' => $request->address_line2 ?? null,
                    'landmark' => $request->landmark ?? null,
                    'country' => $request->country ?? '',
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

    /**
     * Get customer addresses
     */
    public function getAddresses($id)
    {
        $customer = Customer::findOrFail($id);
        $addresses = $customer->addresses;
        
        return response()->json([
            'success' => true,
            'data' => $addresses->map(function($addr) {
                return [
                    'id' => $addr->id,
                    'address_type' => $addr->address_type,
                    'address_line1' => $addr->address_line1,
                    'address_line2' => $addr->address_line2,
                    'city' => $addr->city,
                    'state' => $addr->state,
                    'pincode' => $addr->pincode,
                    'is_default' => $addr->is_default,
                ];
            })
        ]);
    }

    /**
     * Get customer orders
     */
    public function getOrders(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $query = $customer->orders()->with('items');
        
        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $orders->map(function($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'total_amount' => number_format($order->total_amount, 2),
                    'payment_status' => $order->payment_status,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                ];
            })
        ]);
    }

    /**
     * Get customer cart items
     */
    public function getCartItems($id)
    {
        $customer = Customer::findOrFail($id);
        $carts = $customer->carts()->with(['items.product', 'items.variant'])->get();
        
        $allItems = [];
        foreach ($carts as $cart) {
            foreach ($cart->items as $item) {
                $allItems[] = [
                    'id' => $item->id,
                    'product_name' => $item->product->name ?? '-',
                    'variant_name' => $item->variant->name ?? '-',
                    'sku' => $item->variant->sku ?? $item->product->sku ?? '-',
                    'quantity' => $item->quantity,
                    'unit_price' => number_format($item->unit_price, 2),
                    'total_price' => number_format($item->total_price, 2),
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => $allItems
        ]);
    }

    /**
     * Get unique states and cities for area filter
     */
    public function getAreas()
    {
        $states = CustomerAddress::distinct()
            ->whereNotNull('state')
            ->where('state', '!=', '')
            ->orderBy('state')
            ->pluck('state')
            ->map(function($state) {
                return ['value' => $state, 'label' => $state, 'type' => 'state'];
            });
        
        $cities = CustomerAddress::distinct()
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->orderBy('city')
            ->pluck('city')
            ->map(function($city) {
                return ['value' => $city, 'label' => $city, 'type' => 'city'];
            });
        
        return response()->json([
            'success' => true,
            'data' => [
                'states' => $states,
                'cities' => $cities,
                'all' => $states->concat($cities)->sortBy('label')->values()
            ]
        ]);
    }

    /**
     * Get countries from JSON file
     */
    public function getCountries()
    {
        try {
            $jsonPath = public_path('location-json/countries.json');
            
            if (!file_exists($jsonPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Countries JSON file not found'
                ], 404);
            }
            
            $jsonContent = file_get_contents($jsonPath);
            $data = json_decode($jsonContent, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error parsing JSON: ' . json_last_error_msg()
                ], 500);
            }
            
            // Find the data array (skip header/metadata)
            $countries = [];
            foreach ($data as $item) {
                if (isset($item['type']) && $item['type'] === 'table' && isset($item['data'])) {
                    $countries = $item['data'];
                    break;
                }
            }
            
            // Format for Select2
            $formatted = array_map(function($country) {
                return [
                    'id' => $country['id'],
                    'text' => $country['name'],
                    'iso2' => $country['iso2'] ?? '',
                    'iso3' => $country['iso3'] ?? '',
                ];
            }, $countries);
            
            return response()->json([
                'success' => true,
                'data' => $formatted
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading countries: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get states from JSON file filtered by country
     */
    public function getStates(Request $request)
    {
        try {
            $countryId = $request->get('country_id');
            
            if (!$countryId) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }
            
            $jsonPath = public_path('location-json/states.json');
            
            if (!file_exists($jsonPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'States JSON file not found'
                ], 404);
            }
            
            $jsonContent = file_get_contents($jsonPath);
            $data = json_decode($jsonContent, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error parsing JSON: ' . json_last_error_msg()
                ], 500);
            }
            
            // Find the data array
            $states = [];
            foreach ($data as $item) {
                if (isset($item['type']) && $item['type'] === 'table' && isset($item['data'])) {
                    $states = $item['data'];
                    break;
                }
            }
            
            // Filter by country_id
            $filteredStates = array_filter($states, function($state) use ($countryId) {
                return isset($state['country_id']) && $state['country_id'] == $countryId;
            });
            
            // Format for Select2
            $formatted = array_map(function($state) {
                return [
                    'id' => $state['id'],
                    'text' => $state['name'],
                    'country_id' => $state['country_id'] ?? '',
                ];
            }, $filteredStates);
            
            return response()->json([
                'success' => true,
                'data' => array_values($formatted)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading states: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cities from JSON file filtered by state
     */
    public function getCities(Request $request)
    {
        try {
            $stateId = $request->get('state_id');
            
            if (!$stateId) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }
            
            $jsonPath = public_path('location-json/cities.json');
            
            if (!file_exists($jsonPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cities JSON file not found'
                ], 404);
            }
            
            $jsonContent = file_get_contents($jsonPath);
            $data = json_decode($jsonContent, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error parsing JSON: ' . json_last_error_msg()
                ], 500);
            }
            
            // Find the data array
            $cities = [];
            foreach ($data as $item) {
                if (isset($item['type']) && $item['type'] === 'table' && isset($item['data'])) {
                    $cities = $item['data'];
                    break;
                }
            }
            
            // Filter by state_id
            $filteredCities = array_filter($cities, function($city) use ($stateId) {
                return isset($city['state_id']) && $city['state_id'] == $stateId;
            });
            
            // Format for Select2
            $formatted = array_map(function($city) {
                return [
                    'id' => $city['id'],
                    'text' => $city['name'],
                    'state_id' => $city['state_id'] ?? '',
                ];
            }, $filteredCities);
            
            return response()->json([
                'success' => true,
                'data' => array_values($formatted)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading cities: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get default data for system fields
     */
    private function getSystemFieldDefaults($fieldKey)
    {
        $defaults = [
            'country' => [
                'field_key' => 'country',
                'label' => 'Country',
                'input_type' => 'select',
                'placeholder' => 'Select Country',
                'is_required' => true,
                'is_visible' => true,
                'sort_order' => 23,
                'field_group' => 'address',
                'options' => null,
                'conditional_rules' => null,
                'validation_rules' => 'required',
                'help_text' => null,
                'is_active' => true,
                'is_system' => true,
            ],
        ];

        return $defaults[$fieldKey] ?? null;
    }
}
