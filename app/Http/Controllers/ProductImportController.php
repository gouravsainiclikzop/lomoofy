<?php

namespace App\Http\Controllers;

use App\Services\ProductImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProductImportController extends Controller
{
    /**
     * Handle the product import upload.
     */
    public function store(Request $request, ProductImportService $importService): JsonResponse
    {
        try {
            $validated = $request->validate([
                'product_import_file' => [
                    'required',
                    'file',
                    function ($attribute, $value, $fail) {
                        $extension = strtolower($value->getClientOriginalExtension());
                        $mimeType = $value->getMimeType();
                        
                        $allowedExtensions = ['xlsx', 'csv'];
                        $allowedMimeTypes = [
                            // Excel files
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            // CSV files
                            'text/csv',
                            'text/plain',
                            'application/csv',
                            'application/vnd.ms-excel', // Some browsers send this for CSV
                        ];
                        
                        if (!in_array($extension, $allowedExtensions) && !in_array($mimeType, $allowedMimeTypes)) {
                            $fail('The product import file must be a CSV or XLSX file.');
                        }
                    },
                    'max:5120',
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $file = $validated['product_import_file'];
        $extension = strtolower($file->getClientOriginalExtension());
        $originalName = $file->getClientOriginalName();
        
        // Ensure imports directory exists
        $importsPath = storage_path('app/imports');
        if (!is_dir($importsPath)) {
            File::makeDirectory($importsPath, 0755, true);
        }
        
        // Store file with original extension preserved
        try {
            $filename = uniqid('import_', true) . '.' . $extension;
            $storedPath = $file->storeAs('imports', $filename);
            
            if ($storedPath === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to store uploaded file. Storage operation returned false.',
                ], 500);
            }
            
            Log::info('ProductImportController@store - File stored', [
                'original_name' => $originalName,
                'stored_path' => $storedPath,
                'filename' => $filename,
            ]);
        } catch (Throwable $storageException) {
            Log::error('ProductImportController@store - File storage failed', [
                'file' => $originalName,
                'message' => $storageException->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to store uploaded file: ' . $storageException->getMessage(),
            ], 500);
        }

        // Get the absolute path using Storage facade with explicit local disk
        try {
            $absolutePath = Storage::disk('local')->path($storedPath);
        } catch (Throwable $pathException) {
            // Fallback to manual path construction
            $absolutePath = storage_path('app/' . str_replace('\\', '/', $storedPath));
        }
        
        // Verify file exists before proceeding
        if (!file_exists($absolutePath)) {
            // Try alternative path constructions as fallback
            $alternatives = [
                storage_path('app/' . str_replace('\\', '/', $storedPath)),
                storage_path('app\\' . str_replace('/', '\\', $storedPath)),
                storage_path('app') . DIRECTORY_SEPARATOR . str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $storedPath),
            ];
            
            $found = false;
            foreach ($alternatives as $altPath) {
                if (file_exists($altPath)) {
                    $absolutePath = $altPath;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                // List files in imports directory for debugging
                $importsDir = storage_path('app/imports');
                $filesInDir = [];
                if (is_dir($importsDir)) {
                    $filesInDir = array_values(array_diff(scandir($importsDir), ['.', '..']));
                }
                
                Log::error('ProductImportController@store - Stored file not found', [
                    'expected_path' => $absolutePath,
                    'alternatives_tried' => $alternatives,
                    'stored_path' => $storedPath,
                    'storage_disk' => config('filesystems.default'),
                    'storage_root' => storage_path('app'),
                    'imports_directory' => $importsDir,
                    'files_in_imports_dir' => $filesInDir,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'File was stored but not found at expected location. Check logs for details.',
                ], 500);
            }
        }

        try {
            $summary = $importService->import($absolutePath, $extension, $originalName);
        } catch (Throwable $exception) {
            Log::error('ProductImportController@store - Import failed', [
                'file' => $originalName,
                'message' => $exception->getMessage(),
            ]);

            Storage::delete($storedPath);

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $exception->getMessage(),
            ], 500);
        }

        Storage::delete($storedPath);

        $message = $this->buildSummaryMessage($summary);
        $hasErrors = !empty($summary['errors']);
        $statusCode = $hasErrors ? 207 : 200; // 207: Multi-status (partial success)

        return response()->json([
            'success' => !$hasErrors,
            'message' => $message,
            'summary' => $summary,
        ], $statusCode);
    }

    /**
     * Build a human readable summary string.
     */
    private function buildSummaryMessage(array $summary): string
    {
        $parts = [];

        if (($summary['created'] ?? 0) > 0) {
            $parts[] = ($summary['created'] === 1)
                ? '1 product created'
                : "{$summary['created']} products created";
        }

        if (($summary['updated'] ?? 0) > 0) {
            $parts[] = ($summary['updated'] === 1)
                ? '1 product updated'
                : "{$summary['updated']} products updated";
        }

        if (($summary['skipped'] ?? 0) > 0) {
            $parts[] = ($summary['skipped'] === 1)
                ? '1 product skipped'
                : "{$summary['skipped']} products skipped";
        }

        if (empty($parts)) {
            $parts[] = 'No products were imported';
        }

        if (!empty($summary['errors'])) {
            $parts[] = count($summary['errors']) === 1
                ? '1 error occurred'
                : count($summary['errors']) . ' errors occurred';
        }

        if (!empty($summary['warnings'])) {
            $parts[] = count($summary['warnings']) === 1
                ? '1 warning generated'
                : count($summary['warnings']) . ' warnings generated';
        }

        return implode('; ', $parts) . '.';
    }
}


