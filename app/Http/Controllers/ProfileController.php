<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\CompanySetting;

class ProfileController extends Controller
{
    /**
     * Show the profile page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $companySettings = CompanySetting::getSettings();
        return view('admin.profile.index', compact('companySettings'));
    }

    /**
     * Update user's profile image.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Delete old image if exists
        if ($user->image && Storage::disk('public')->exists($user->image)) {
            Storage::disk('public')->delete($user->image);
        }

        // Store new image
        $imagePath = $request->file('image')->store('profile-images', 'public');

        // Update user
        $user->image = $imagePath;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile image updated successfully',
            'image_url' => asset('storage/' . $imagePath)
        ]);
    }

    /**
     * Update user's name.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateName(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $user->name = $request->name;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Name updated successfully',
            'name' => $user->name
        ]);
    }

    /**
     * Update user's email.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateEmail(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'current_password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'current_password' => ['The current password is incorrect.']
                ]
            ], 422);
        }

        $user->email = $request->email;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Email updated successfully',
            'email' => $user->email
        ]);
    }

    /**
     * Update user's password.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'current_password' => ['The current password is incorrect.']
                ]
            ], 422);
        }

        // Check if new password is same as current password
        if (Hash::check($request->new_password, $user->password)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'new_password' => ['The new password must be different from the current password.']
                ]
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);
    }

    /**
     * Update company settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCompanySettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'company_logo_text' => 'nullable|string|max:255',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'secondary_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'phone' => 'nullable|string|max:255',
            'customer_care_phone' => 'nullable|string|max:255',
            'careers_phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'secondary_email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'pincode' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pan_no' => 'nullable|string|max:20|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'gst_registration_no' => 'nullable|string|max:50|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            'authorized_signatory' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'whatsapp_url' => 'nullable|url|max:255',
        ], [
            'pan_no.regex' => 'PAN number must be in valid format (e.g., ABCDE1234F)',
            'gst_registration_no.regex' => 'GST registration number must be in valid format (e.g., 22AAAAA0000A1Z5)',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $settings = CompanySetting::getSettings();
        $data = $request->only([
            'company_name',
            'company_logo_text',
            'phone',
            'customer_care_phone',
            'careers_phone',
            'email',
            'secondary_email',
            'address',
            'pincode',
            'city',
            'state',
            'pan_no',
            'gst_registration_no',
            'facebook_url',
            'twitter_url',
            'youtube_url',
            'instagram_url',
            'linkedin_url',
            'whatsapp_url',
        ]);
        
        // Handle signature upload
        if ($request->hasFile('authorized_signatory')) {
            // Delete old signature if exists
            if ($settings->authorized_signatory && Storage::disk('public')->exists($settings->authorized_signatory)) {
                Storage::disk('public')->delete($settings->authorized_signatory);
            }

            // Store new signature
            $signaturePath = $request->file('authorized_signatory')->store('company-signatures', 'public');
            $data['authorized_signatory'] = $signaturePath;
        }
        
        // Convert PAN and GST to uppercase
        if (!empty($data['pan_no'])) {
            $data['pan_no'] = strtoupper($data['pan_no']);
        }
        if (!empty($data['gst_registration_no'])) {
            $data['gst_registration_no'] = strtoupper($data['gst_registration_no']);
        }

        // Handle logo upload
        if ($request->hasFile('company_logo')) {
            // Delete old logo if exists
            if ($settings->company_logo && Storage::disk('public')->exists($settings->company_logo)) {
                Storage::disk('public')->delete($settings->company_logo);
            }

            // Store new logo
            $logoPath = $request->file('company_logo')->store('company-logos', 'public');
            $data['company_logo'] = $logoPath;
        }

        // Handle secondary logo upload
        if ($request->hasFile('secondary_logo')) {
            // Delete old secondary logo if exists
            if ($settings->secondary_logo && Storage::disk('public')->exists($settings->secondary_logo)) {
                Storage::disk('public')->delete($settings->secondary_logo);
            }

            // Store new secondary logo
            $secondaryLogoPath = $request->file('secondary_logo')->store('company-logos', 'public');
            $data['secondary_logo'] = $secondaryLogoPath;
        }

        $settings->update($data);

        // Include logo and signature URLs in response
        $settings->logo_url = $settings->company_logo ? asset('storage/' . $settings->company_logo) : null;
        $settings->secondary_logo_url = $settings->secondary_logo ? asset('storage/' . $settings->secondary_logo) : null;
        $settings->signature_url = $settings->authorized_signatory ? asset('storage/' . $settings->authorized_signatory) : null;

        return response()->json([
            'success' => true,
            'message' => 'Company settings updated successfully',
            'settings' => $settings
        ]);
    }

    /**
     * Toggle coming soon setting.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleComingSoon(Request $request)
    {
        $settings = CompanySetting::getSettings();
        $settings->coming_soon = !$settings->coming_soon;
        $settings->save();

        return response()->json([
            'success' => true,
            'message' => $settings->coming_soon ? 'Coming soon mode enabled' : 'Coming soon mode disabled',
            'coming_soon' => $settings->coming_soon
        ]);
    }
}

