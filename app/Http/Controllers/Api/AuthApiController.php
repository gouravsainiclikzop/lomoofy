<?php

namespace App\Http\Controllers\Api;

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

            // Generate API token (using Sanctum)
            $token = $customer->createToken('auth-token')->plainTextToken;

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
                    'token' => $token,
                    'token_type' => 'Bearer',
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
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

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

        try {
            $customer = Customer::where('email', $request->email)->first();

            if (!$customer || !Hash::check($request->password, $customer->password)) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INVALID_CREDENTIALS',
                        'message' => 'Invalid email or password',
                    ],
                ], 401);
            }

            if (!$customer->is_active) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'ACCOUNT_INACTIVE',
                        'message' => 'Your account is inactive. Please contact support.',
                    ],
                ], 403);
            }

            // Generate API token
            $token = $customer->createToken('auth-token')->plainTextToken;

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
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ]);
        } catch (\Exception $e) {
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
            $customer = $request->user();

            if (!$customer || !($customer instanceof Customer)) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHENTICATED',
                        'message' => 'Not authenticated',
                    ],
                ], 401);
            }

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
            // Revoke current token
            $request->user()->currentAccessToken()->delete();

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
