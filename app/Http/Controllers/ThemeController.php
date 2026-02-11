<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ThemeController extends Controller
{
    /**
     * Display the themes listing (admin).
     * Themes are only shown when software_id is set in company_settings.
     */
    public function index(Request $request)
    {
        $settings = CompanySetting::getSettings();
        $softwareId = $settings->software_id ?? null;

        $themes = collect();
        if (!empty($softwareId)) {
            $themes = Theme::query()
                ->bySoftware($softwareId)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('admin.themes.index', [
            'themes' => $themes,
            'softwareId' => $softwareId,
        ]);
    }

    /**
     * Update software_id in company_settings.
     */
    public function updateSoftwareId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'software_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->route('themes.index')
                ->withErrors($validator)
                ->withInput();
        }

        $settings = CompanySetting::getSettings();
        $settings->software_id = $request->software_id;
        $settings->save();

        return redirect()->route('themes.index')->with('success', 'Software ID saved. Themes for this store will now appear here.');
    }
}
