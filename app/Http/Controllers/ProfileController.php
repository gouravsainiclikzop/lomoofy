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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
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
            'company_logo_text' => 'required|string|max:255',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
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
            'email',
            'address',
        ]);

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

        $settings->update($data);

        // Include logo URL in response
        $settings->logo_url = $settings->company_logo ? asset('storage/' . $settings->company_logo) : null;

        return response()->json([
            'success' => true,
            'message' => 'Company settings updated successfully',
            'settings' => $settings
        ]);
    }
}

