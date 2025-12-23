<?php

namespace App\Http\Controllers;

use App\Models\ServiceHighlight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceHighlightController extends Controller
{
    /**
     * Display the service highlights page.
     */
    public function index()
    {
        $serviceHighlight = ServiceHighlight::getInstance();
        return view('admin.service-highlights.index', compact('serviceHighlight'));
    }

    /**
     * Update the service highlights.
     */
    public function update(Request $request)
    {
        $serviceHighlight = ServiceHighlight::getInstance();

        $validator = Validator::make($request->all(), [
            'highlight1_title' => 'nullable|string|max:255',
            'highlight1_text' => 'nullable|string|max:255',
            'highlight1_icon' => 'nullable|string|max:255',
            'highlight1_active' => 'nullable|boolean',
            'highlight2_title' => 'nullable|string|max:255',
            'highlight2_text' => 'nullable|string|max:255',
            'highlight2_icon' => 'nullable|string|max:255',
            'highlight2_active' => 'nullable|boolean',
            'highlight3_title' => 'nullable|string|max:255',
            'highlight3_text' => 'nullable|string|max:255',
            'highlight3_icon' => 'nullable|string|max:255',
            'highlight3_active' => 'nullable|boolean',
            'highlight4_title' => 'nullable|string|max:255',
            'highlight4_text' => 'nullable|string|max:255',
            'highlight4_icon' => 'nullable|string|max:255',
            'highlight4_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only([
            'highlight1_title',
            'highlight1_text',
            'highlight1_icon',
            'highlight1_active',
            'highlight2_title',
            'highlight2_text',
            'highlight2_icon',
            'highlight2_active',
            'highlight3_title',
            'highlight3_text',
            'highlight3_icon',
            'highlight3_active',
            'highlight4_title',
            'highlight4_text',
            'highlight4_icon',
            'highlight4_active',
        ]);

        // Convert active fields to boolean
        foreach (['highlight1_active', 'highlight2_active', 'highlight3_active', 'highlight4_active'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = (bool)($data[$field] === '1' || $data[$field] === 1 || $data[$field] === true || $data[$field] === 'on');
            } else {
                $data[$field] = false;
            }
        }

        $serviceHighlight->update($data);
        $serviceHighlight->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Service highlights updated successfully',
            'data' => [
                'id' => $serviceHighlight->id,
                'highlight1_title' => $serviceHighlight->highlight1_title,
                'highlight1_text' => $serviceHighlight->highlight1_text,
                'highlight1_icon' => $serviceHighlight->highlight1_icon,
                'highlight1_active' => $serviceHighlight->highlight1_active,
                'highlight2_title' => $serviceHighlight->highlight2_title,
                'highlight2_text' => $serviceHighlight->highlight2_text,
                'highlight2_icon' => $serviceHighlight->highlight2_icon,
                'highlight2_active' => $serviceHighlight->highlight2_active,
                'highlight3_title' => $serviceHighlight->highlight3_title,
                'highlight3_text' => $serviceHighlight->highlight3_text,
                'highlight3_icon' => $serviceHighlight->highlight3_icon,
                'highlight3_active' => $serviceHighlight->highlight3_active,
                'highlight4_title' => $serviceHighlight->highlight4_title,
                'highlight4_text' => $serviceHighlight->highlight4_text,
                'highlight4_icon' => $serviceHighlight->highlight4_icon,
                'highlight4_active' => $serviceHighlight->highlight4_active,
            ]
        ]);
    }
}
