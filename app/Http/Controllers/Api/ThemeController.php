<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ThemeController extends Controller
{
   
    public function store(Request $request)
    {
        $rules = [
            'software_id' => 'required|string|max:255',
            'theme_name' => 'required|string|max:255',
            'theme_thumbnail' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'type' => 'nullable|in:internal,external',
            'status' => 'nullable|in:active,inactive',
        ];

        $type = $request->input('type', 'internal');
        if ($type === 'external') {
            $rules['preview_url'] = 'required|string|url|max:2048';
            $rules['theme_pdf'] = 'nullable';
        } else {
            $rules['theme_pdf'] = 'nullable|file|mimes:pdf|max:10240';
            $rules['preview_url'] = 'nullable|string|url|max:2048';
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

        $thumbnailPath = null;
        if ($request->hasFile('theme_thumbnail')) {
            $thumbnailPath = $request->file('theme_thumbnail')->store('themes/thumbnails', 'public');
        }

        $pdfPath = null;
        if ($type === 'internal' && $request->hasFile('theme_pdf')) {
            $pdfPath = $request->file('theme_pdf')->store('themes/pdfs', 'public');
        }

        $theme = Theme::create([
            'software_id' => $request->software_id,
            'theme_name' => $request->theme_name,
            'theme_thumbnail' => $thumbnailPath,
            'theme_pdf' => $pdfPath,
            'status' => $request->input('status', 'active'),
            'type' => $type,
            'preview_url' => $type === 'external' ? $request->preview_url : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Theme created successfully',
            'data' => $this->formatTheme($theme),
        ], 201);
    }
 
    public function index(Request $request)
    {
        $query = Theme::query();

        if ($request->filled('software_id')) {
            $query->bySoftware($request->software_id);
        }

        $themes = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $themes->map(fn ($theme) => $this->formatTheme($theme)),
        ]);
    }
 
    public function show(int $id)
    {
        $theme = Theme::find($id);

        if (!$theme) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Theme not found',
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatTheme($theme),
        ]);
    }
 
    public function update(Request $request, int $id)
    {
        $theme = Theme::find($id);

        if (!$theme) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Theme not found',
                ],
            ], 404);
        }

        $rules = [
            'software_id' => 'sometimes|required|string|max:255',
            'theme_name' => 'sometimes|required|string|max:255',
            'theme_thumbnail' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'type' => 'nullable|in:internal,external',
            'preview_url' => 'nullable|string|url|max:2048',
            'theme_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'status' => 'nullable|in:active,inactive',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($v) use ($request, $theme) {
            $type = $request->input('type', $theme->type ?? 'internal');
            if ($type === 'external' && empty($request->preview_url) && empty($theme->preview_url)) {
                $v->errors()->add('preview_url', 'preview_url is required when type is external.');
            }
        });

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

        $data = [];

        if ($request->has('software_id')) {
            $data['software_id'] = $request->software_id;
        }
        if ($request->has('theme_name')) {
            $data['theme_name'] = $request->theme_name;
        }
        if ($request->has('status')) {
            $data['status'] = $request->status;
        }
        if ($request->has('type')) {
            $data['type'] = $request->type;
        }
        $newType = $request->input('type', $theme->type ?? 'internal');
        if ($request->has('preview_url') || $request->has('type')) {
            $data['preview_url'] = $newType === 'external' ? ($request->preview_url ?? $theme->preview_url) : null;
        }

        if ($request->hasFile('theme_thumbnail')) {
            if ($theme->theme_thumbnail) {
                Storage::disk('public')->delete($theme->theme_thumbnail);
            }
            $data['theme_thumbnail'] = $request->file('theme_thumbnail')->store('themes/thumbnails', 'public');
        }

        $updateType = $request->input('type', $theme->type ?? 'internal');
        if ($request->hasFile('theme_pdf') && $updateType === 'internal') {
            if ($theme->theme_pdf) {
                Storage::disk('public')->delete($theme->theme_pdf);
            }
            $data['theme_pdf'] = $request->file('theme_pdf')->store('themes/pdfs', 'public');
            $data['preview_url'] = null;
        }

        $theme->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Theme updated successfully',
            'data' => $this->formatTheme($theme->fresh()),
        ]);
    }
 
    public function destroy(int $id)
    {
        $theme = Theme::find($id);

        if (!$theme) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Theme not found',
                ],
            ], 404);
        }

        if ($theme->theme_thumbnail) {
            Storage::disk('public')->delete($theme->theme_thumbnail);
        }
        if ($theme->theme_pdf) {
            Storage::disk('public')->delete($theme->theme_pdf);
        }

        $theme->delete();

        return response()->json([
            'success' => true,
            'message' => 'Theme deleted successfully',
        ]);
    }
 
    public function publicIndex(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'software_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'software_id is required',
                    'errors' => $validator->errors(),
                ],
            ], 422);
        }

        $themes = Theme::active()
            ->bySoftware($request->software_id)
            ->orderBy('theme_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $themes->map(function ($theme) {
                $item = [
                    'id' => $theme->id,
                    'software_id' => $theme->software_id,
                    'theme_name' => $theme->theme_name,
                    'thumbnail_url' => $theme->theme_thumbnail ? asset('storage/' . $theme->theme_thumbnail) : null,
                    'type' => $theme->type ?? 'internal',
                    'status' => $theme->status,
                ];
                if ($theme->isExternal()) {
                    $item['preview_url'] = $theme->preview_url;
                } else {
                    $item['pdf_url'] = $theme->theme_pdf ? asset('storage/' . $theme->theme_pdf) : null;
                }
                return $item;
            }),
        ]);
    }

    private function formatTheme(Theme $theme): array
    {
        $base = [
            'id' => $theme->id,
            'software_id' => $theme->software_id,
            'theme_name' => $theme->theme_name,
            'theme_thumbnail' => $theme->theme_thumbnail,
            'theme_thumbnail_url' => $theme->theme_thumbnail ? asset('storage/' . $theme->theme_thumbnail) : null,
            'type' => $theme->type ?? 'internal',
            'status' => $theme->status,
        ];

        if ($theme->isExternal()) {
            return array_merge($base, [
                'preview_url' => $theme->preview_url,
            ]);
        }

        return array_merge($base, [
            'theme_pdf' => $theme->theme_pdf,
            'theme_pdf_url' => $theme->theme_pdf ? asset('storage/' . $theme->theme_pdf) : null,
            'created_at' => $theme->created_at?->toIso8601String(),
            'updated_at' => $theme->updated_at?->toIso8601String(),
        ]);
    }
}
