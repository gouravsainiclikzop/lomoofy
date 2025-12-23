<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\FieldManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AuthApiController extends Controller
{
    /**
     * Get login fields dynamically
     * GET /api/auth/login-fields
     * Only returns system fields: email, password
     */
    public function getLoginFields()
    {
        try {
            // Only get system fields for login
            $systemFieldKeys = FieldManagement::getLoginSystemFields();
            
            $fields = FieldManagement::whereIn('field_key', $systemFieldKeys)
                ->active()
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
                        'help_text' => $field->help_text,
                        'validation_rules' => $field->validation_rules,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'fields' => $fields,
                    'field_groups' => $fields->groupBy('field_group'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FIELDS_FETCH_ERROR',
                    'message' => 'Failed to fetch login fields: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Register a new customer
     * POST /api/auth/register
     * Only accepts: full_name, phone, email, password, password_confirmation
     */
    public function register(Request $request)
    {
        // Validation rules for only these 5 fields
        $rules = [
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:customers,email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ],
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Prepare customer data with only these fields
            $customerData = [
                'full_name' => $request->full_name,
                'phone' => $request->phone ?? null,
                'email' => $request->email,
                'password' => $request->password, // Will be hashed by model mutator
                'is_active' => true,
            ];

            // Create customer
            $customer = Customer::create($customerData);

            // Auto-login customer after registration using session
            Auth::guard('customer')->login($customer, $request->boolean('remember'));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'customer' => [
                        'id' => $customer->id,
                        'full_name' => $customer->full_name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                    ],
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'REGISTRATION_ERROR',
                    'message' => 'Failed to register: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Login customer
     * POST /api/auth/login
     */
    public function login(Request $request)
    { 
        // Debug logging
        \Log::info('Customer Login Attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            \Log::warning('Customer Login Validation Failed', [
                'errors' => $validator->errors()->toArray(),
                'email' => $request->email,
            ]);
            
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ],
            ], 422);
        }

        try {
            // Use Laravel's standard authentication attempt
            $credentials = $request->only('email', 'password');
            $remember = $request->boolean('remember');

            // Attempt login with customer guard
            if (Auth::guard('customer')->attempt($credentials, $remember)) {
                $request->session()->regenerate();
                
                $customer = Auth::guard('customer')->user();

                \Log::info('Customer Login Successful', [
                    'customer_id' => $customer->id,
                    'email' => $customer->email,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'data' => [
                        'customer' => [
                            'id' => $customer->id,
                            'full_name' => $customer->full_name,
                            'email' => $customer->email,
                            'phone' => $customer->phone,
                            'profile_image' => $customer->profile_image ? asset('storage/' . $customer->profile_image) : null,
                        ],
                    ],
                ]);
            }

            \Log::warning('Customer Login Failed', ['email' => $request->email]);
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'Invalid email or password',
                ],
            ], 401);
        } catch (\Exception $e) {
            \Log::error('Customer Login Exception', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'LOGIN_ERROR',
                    'message' => 'Failed to login: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Get authenticated customer
     * GET /api/auth/me
     */
    public function me(Request $request)
    {
        try {
            $customer = Auth::guard('customer')->user();

            // Return null if not authenticated (instead of 401 error)
            if (!$customer || !($customer instanceof Customer)) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'error' => [
                        'code' => 'UNAUTHENTICATED',
                        'message' => 'Not authenticated',
                    ],
                ], 200); // Return 200 with success: false instead of 401
            }

            // Load addresses relationship
            $customer->load('addresses'); 
            // Get default address
            $defaultAddress = $customer->defaultAddress;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $customer->id,
                    'full_name' => $customer->full_name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'alternate_phone' => $customer->alternate_phone,
                    'date_of_birth' => $customer->date_of_birth?->format('Y-m-d'),
                    'gender' => $customer->gender,
                    'profile_image' => $customer->profile_image ? asset('storage/' . $customer->profile_image) : null,
                    'preferred_contact_method' => $customer->preferred_contact_method,
                    'preferred_payment_method' => $customer->preferred_payment_method,
                    'preferred_delivery_slot' => $customer->preferred_delivery_slot,
                    'newsletter_opt_in' => $customer->newsletter_opt_in,
                    'custom_data' => $customer->custom_data,
                    'is_active' => $customer->is_active,
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
                    'default_address' => $defaultAddress ? [
                        'id' => $defaultAddress->id,
                        'country' => $defaultAddress->country ?? '',
                        'city' => $defaultAddress->city,
                        'state' => $defaultAddress->state,
                        'pincode' => $defaultAddress->pincode,
                    ] : null,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FETCH_ERROR',
                    'message' => 'Failed to fetch user data: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Logout customer
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        try {
            // Logout using session
            Auth::guard('customer')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'LOGOUT_ERROR',
                    'message' => 'Failed to logout: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }
}
