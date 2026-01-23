<?php

namespace App\Http\Controllers;

use App\Models\LegalPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LegalPageController extends Controller
{
    /**
     * Display the legal pages management page.
     */
    public function index()
    {
        $legalPages = LegalPage::getInstance();
        return view('admin.legal-pages.index', compact('legalPages'));
    }

    /**
     * Update the legal pages.
     */
    public function update(Request $request)
    {
        $legalPages = LegalPage::getInstance();

        $validator = Validator::make($request->all(), [
            'terms_conditions' => 'nullable|string',
            'terms_conditions_status' => 'nullable|boolean',
            'shipping' => 'nullable|string',
            'shipping_status' => 'nullable|boolean',
            'cancellation_refund' => 'nullable|string',
            'cancellation_refund_status' => 'nullable|boolean',
            'return_refund_policy' => 'nullable|string',
            'return_refund_policy_status' => 'nullable|boolean',
            'privacy_policy' => 'nullable|string',
            'privacy_policy_status' => 'nullable|boolean',
            'disclaimer' => 'nullable|string',
            'disclaimer_status' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = [
            'terms_conditions' => $request->input('terms_conditions'),
            'terms_conditions_status' => $request->has('terms_conditions_status') ? true : false,
            'shipping' => $request->input('shipping'),
            'shipping_status' => $request->has('shipping_status') ? true : false,
            'cancellation_refund' => $request->input('cancellation_refund'),
            'cancellation_refund_status' => $request->has('cancellation_refund_status') ? true : false,
            'return_refund_policy' => $request->input('return_refund_policy'),
            'return_refund_policy_status' => $request->has('return_refund_policy_status') ? true : false,
            'privacy_policy' => $request->input('privacy_policy'),
            'privacy_policy_status' => $request->has('privacy_policy_status') ? true : false,
            'disclaimer' => $request->input('disclaimer'),
            'disclaimer_status' => $request->has('disclaimer_status') ? true : false,
        ];

        $legalPages->update($data);
        $legalPages->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Legal pages updated successfully',
            'data' => $legalPages
        ]);
    }
}
