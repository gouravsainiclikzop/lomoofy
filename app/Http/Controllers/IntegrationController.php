<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    /**
     * Display the integrations management page
     */
    public function index()
    {
        // Get all integrations or create default empty ones
        $integrations = [
            'email' => Integration::byType('email')->first(),
            'razorpay' => Integration::byType('payment')->where('provider', 'razorpay')->first(),
            'otp' => Integration::byType('otp')->first(),
            'whatsapp' => Integration::byType('whatsapp')->first(),
            'shipping' => Integration::byType('shipping')->first(),
            'google_review' => Integration::byType('google_review')->first(),
            'analytics' => Integration::byType('analytics')->first(),
        ];

        return view('admin.integrations.index', compact('integrations'));
    }

    /**
     * Store or update an integration
     */
    public function store(Request $request)
    {
        try {
            $integrationType = $request->input('integration_type');
            $provider = $request->input('provider');
            $status = $request->has('status') ? true : false;
 
            $configuration = $request->except(['_token', 'integration_type', 'provider', 'status', 'integration_id']);
 
            if ($request->has('integration_id') && $request->integration_id) {
                $integration = Integration::findOrFail($request->integration_id);
                
                $existingConfig = $integration->configuration ?? [];
                foreach ($configuration as $key => $value) {
                    // If the value is the mask (••••••••), preserve the original value
                    if ($value === '••••••••' && isset($existingConfig[$key])) {
                        $configuration[$key] = $existingConfig[$key];
                    }
                }
                
                $integration->update([
                    'configuration' => $configuration,
                    'status' => $status,
                ]);
                
                $message = 'Integration updated successfully!';
            } else {
                $integration = Integration::create([
                    'integration_type' => $integrationType,
                    'provider' => $provider,
                    'configuration' => $configuration,
                    'status' => $status,
                ]);
                
                $message = 'Integration saved successfully!';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'integration' => $integration,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save integration: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get integration data for editing
     */
    public function show($id)
    {
        try {
            $integration = Integration::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'integration' => [
                    'id' => $integration->id,
                    'integration_type' => $integration->integration_type,
                    'provider' => $integration->provider,
                    'configuration' => $integration->getMaskedConfiguration(),
                    'status' => $integration->status,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Integration not found',
            ], 404);
        }
    }

    /**
     * Delete an integration
     */
    public function destroy($id)
    {
        try {
            $integration = Integration::findOrFail($id);
            $integration->delete();

            return response()->json([
                'success' => true,
                'message' => 'Integration deleted successfully!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete integration',
            ], 500);
        }
    }

    /**
     * Send test email
     */
    public function testEmail(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);

            $emailService = app(\App\Services\EmailService::class);

            if (!$emailService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email service is not configured or not enabled.',
                ], 400);
            }

            // Refresh configuration from database to ensure latest settings
            $emailService->refresh();

            // Send test email
            $emailService->sendTestEmail(
                $request->email,
                'Test Email from ' . config('app.name'),
                'This is a test email to verify your SMTP configuration is working correctly.'
            );

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully!',
            ]);

        } catch (\Exception $e) {
            \Log::error('Test email failed', [
                'error' => $e->getMessage(),
                'email' => $request->email ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
            ], 500);
        }
    }
}
