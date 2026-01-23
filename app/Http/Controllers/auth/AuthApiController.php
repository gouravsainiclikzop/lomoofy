<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerOtp;
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
     * Get registration fields dynamically
     * GET /api/auth/register-fields
     */
    public function getRegistrationFields()
    {
        try {
            // Get system fields for registration
            $systemFieldKeys = FieldManagement::getRegistrationSystemFields();
            
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
                    'message' => 'Failed to fetch registration fields: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Send OTP to email for registration
     * POST /api/auth/send-otp
     */
    public function sendOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:customers,email',
                'purpose' => 'nullable|string|in:registration,login,password_reset',
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

            $purpose = $request->input('purpose', 'registration');

            // Generate and send OTP
            CustomerOtp::generateAndSend($request->email, $purpose);

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully to your email',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'OTP_SEND_ERROR',
                    'message' => 'Failed to send OTP: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Verify OTP
     * POST /api/auth/verify-otp
     */
    public function verifyOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'otp' => 'required|string|size:6',
                'purpose' => 'nullable|string|in:registration,login,password_reset',
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

            $purpose = $request->input('purpose', 'registration');

            // Verify OTP
            $isValid = CustomerOtp::verify($request->email, $request->otp, $purpose);

            if ($isValid) {
                return response()->json([
                    'success' => true,
                    'message' => 'OTP verified successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_OTP',
                    'message' => 'Invalid or expired OTP',
                ],
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'OTP_VERIFY_ERROR',
                    'message' => 'Failed to verify OTP: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Register a new customer using dynamic field management
     * POST /api/auth/register
     * Accepts fields based on field_management_fields configuration
     * Requires OTP verification before registration
     */
    public function register(Request $request)
    {
        try {
            // First, verify that OTP was verified for this email
            $otpVerified = CustomerOtp::where('email', $request->email)
                ->where('purpose', 'registration')
                ->where('is_verified', true)
                ->where('expires_at', '>', now())
                ->exists();

            if (!$otpVerified) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'OTP_NOT_VERIFIED',
                        'message' => 'Please verify your email with OTP before registering',
                    ],
                ], 400);
            }

            // Get registration fields from field management
            $systemFieldKeys = FieldManagement::getRegistrationSystemFields();
            $fields = FieldManagement::whereIn('field_key', $systemFieldKeys)
                ->active()
                ->visible()
                ->get();

            // Build dynamic validation rules
            $rules = [];
            $customMessages = [];

            foreach ($fields as $field) {
                $fieldRules = [];
                
                if ($field->is_required) {
                    $fieldRules[] = 'required';
                } else {
                    $fieldRules[] = 'nullable';
                }
                
                // Add field-specific validation rules
                if ($field->validation_rules) {
                    $fieldRules[] = $field->validation_rules;
                }
                
                // Add input type specific rules
                switch ($field->input_type) {
                    case 'email':
                        $fieldRules[] = 'email';
                        if ($field->field_key === 'email') {
                            $fieldRules[] = 'unique:customers,email';
                        }
                        break;
                    case 'tel':
                        $fieldRules[] = 'string|max:20';
                        if ($field->field_key === 'phone') {
                            $fieldRules[] = 'unique:customers,phone';
                        }
                        break;
                    case 'password':
                        if ($field->field_key === 'password') {
                            $fieldRules[] = 'min:8|confirmed';
                        }
                        break;
                    case 'text':
                        $fieldRules[] = 'string|max:255';
                        break;
                }
                
                $rules[$field->field_key] = implode('|', $fieldRules);
            }

            // Special validation: require either email OR phone (not both required, but at least one)
            $emailField = $fields->where('field_key', 'email')->first();
            $phoneField = $fields->where('field_key', 'phone')->first();
            
            if ($emailField && $phoneField) {
                // Custom validation: at least one of email or phone must be provided
                $rules['email'] = str_replace('required|', 'nullable|', $rules['email'] ?? 'nullable');
                $rules['phone'] = str_replace('required|', 'nullable|', $rules['phone'] ?? 'nullable');
                
                // Add custom validation rule
        $validator = Validator::make($request->all(), $rules);
                
                $validator->after(function ($validator) use ($request) {
                    if (empty($request->email) && empty($request->phone)) {
                        $validator->errors()->add('email', 'Either email or phone number is required.');
                        $validator->errors()->add('phone', 'Either email or phone number is required.');
                    }
                });
            } else {
                $validator = Validator::make($request->all(), $rules);
            }

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
            
            // Prepare customer data dynamically
            $customerData = ['is_active' => true];
            
            foreach ($fields as $field) {
                if ($field->field_key === 'password_confirmation') {
                    continue; // Skip confirmation field
                }
                
                $value = $request->input($field->field_key);
                if ($value !== null) {
                    $customerData[$field->field_key] = $value;
                }
            }

            // Create customer
            $customer = Customer::create($customerData);

            // Auto-login customer after registration
            Auth::guard('customer')->login($customer, $request->boolean('remember'));

            // Merge guest cart with customer cart after successful registration and login
            $sessionId = $request->input('session_id') 
                      ?? $request->query('session_id') 
                      ?? $request->header('X-Session-ID') 
                      ?? session()->getId();

            if ($sessionId) {
                \App\Models\Cart::mergeGuestCartWithCustomerCart($customer->id, $sessionId);
            }

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
        // Support login with email OR phone
        $loginField = $request->input('email') ?: $request->input('phone');
        $loginFieldType = $request->has('email') && $request->email ? 'email' : 'phone';
        
        

        $rules = [
            'password' => 'required|string',
        ];
        
        if ($loginFieldType === 'email') {
            $rules['email'] = 'required|email';
        } else {
            $rules['phone'] = 'required|string';
        }
        
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

        try {
            // Prepare credentials for authentication (email OR phone)
            $credentials = ['password' => $request->password];
            if ($loginFieldType === 'email') {
                $credentials['email'] = $request->email;
            } else {
                $credentials['phone'] = $request->phone;
            }
            
            $remember = $request->boolean('remember');

            // Attempt login with customer guard
            if (Auth::guard('customer')->attempt($credentials, $remember)) {
                $request->session()->regenerate();
                
                $customer = Auth::guard('customer')->user();

                // Merge guest cart with customer cart after successful login
                $sessionId = $request->input('session_id') 
                          ?? $request->query('session_id') 
                          ?? $request->header('X-Session-ID') 
                          ?? session()->getId();


                // Always attempt cart merge - the method now has fallback logic
                \App\Models\Cart::mergeGuestCartWithCustomerCart($customer->id, $sessionId);

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
 
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'Invalid email or password',
                ],
            ], 401);
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
            // Logout only the customer guard without affecting admin guard
            Auth::guard('customer')->logout();
            
            // Don't invalidate the entire session - just regenerate the CSRF token
            // This allows admin to stay logged in
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

    /**
     * Send OTP for password reset
     * POST /api/auth/forgot-password/send-otp
     */
    public function forgotPasswordSendOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:customers,email',
            ], [
                'email.exists' => 'No account found with this email address.',
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

            // Double-check customer exists before sending OTP 
            $customer = Customer::where('email', $request->email)->first();  
            
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'EMAIL_NOT_FOUND',
                        'message' => 'No account found with this email address.',
                        'errors' => [
                            'email' => ['No account found with this email address.']
                        ],
                    ],
                ], 422);
            }

            // Generate and send OTP
            CustomerOtp::generateAndSend($request->email, 'password_reset');

            return response()->json([
                'success' => true,
                'message' => 'Verification code sent to your email',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'OTP_SEND_ERROR',
                    'message' => 'Failed to send verification code: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Verify OTP for password reset
     * POST /api/auth/forgot-password/verify-otp
     */
    public function forgotPasswordVerifyOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:customers,email',
                'otp' => 'required|string|size:6',
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

            // Verify OTP
            $isValid = CustomerOtp::verify($request->email, $request->otp, 'password_reset');

            if ($isValid) {
                return response()->json([
                    'success' => true,
                    'message' => 'Verification code verified successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_OTP',
                    'message' => 'Invalid or expired verification code',
                ],
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'OTP_VERIFY_ERROR',
                    'message' => 'Failed to verify code: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Reset password after OTP verification
     * POST /api/auth/forgot-password/reset
     */
    public function resetPassword(Request $request)
    {
        try {
            // First, verify that OTP was verified for this email
            $otpVerified = CustomerOtp::where('email', $request->email)
                ->where('purpose', 'password_reset')
                ->where('is_verified', true)
                ->where('expires_at', '>', now())
                ->exists();

            if (!$otpVerified) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'OTP_NOT_VERIFIED',
                        'message' => 'Please verify your email with OTP before resetting password',
                    ],
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:customers,email',
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|string|min:8',
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

            // Find customer and update password
            $customer = Customer::where('email', $request->email)->first();
            
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'CUSTOMER_NOT_FOUND',
                        'message' => 'Customer not found',
                    ],
                ], 404);
            }

            // Update password
            $customer->password = Hash::make($request->password);
            $customer->save();

            // Invalidate all OTPs for this email
            CustomerOtp::where('email', $request->email)
                ->where('purpose', 'password_reset')
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'PASSWORD_RESET_ERROR',
                    'message' => 'Failed to reset password: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

}
