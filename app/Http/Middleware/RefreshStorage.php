<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RefreshStorage
{
    /**
     * Handle the request (this runs BEFORE response is sent)
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Do nothing here (or minimal work)
        return $next($request);
    }

    /**
     * Terminate method runs AFTER the response is sent to the browser
     */
    public function terminate($request, $response): void
    {
        $src = storage_path('app/public');
        $dst = public_path('storage');

        if (is_dir($src)) {
            try {
                // Create destination directory if it doesn't exist
                if (!is_dir($dst)) {
                    File::makeDirectory($dst, 0755, true);
                    $this->copyAllFiles($src, $dst);
                } else {
                    // Check with cache to avoid frequent copying
                    $cacheKey = 'storage_refresh_check';
                    $shouldCheck = Cache::get($cacheKey, true);

                    if ($shouldCheck) {
                        $this->syncDirectories($src, $dst);
                        Cache::put($cacheKey, false, 5);
                    }
                }
            } catch (\Exception $e) {
                // Log the error for debugging
                Log::error('Storage refresh failed: ' . $e->getMessage());
                Log::error('Storage refresh stack trace: ' . $e->getTraceAsString());
            }
        }
    }

    /**
     * Copy all files from source to destination
     */
    protected function copyAllFiles(string $src, string $dst): void
    {
        File::copyDirectory($src, $dst);
    }

    /**
     * Sync only changed files between directories
     */
    protected function syncDirectories(string $src, string $dst): void
    {
        // Validate source directory
        if (!is_dir($src) || !is_readable($src)) {
            Log::error('RefreshStorage: Source directory is not accessible: ' . $src);
            return;
        }

        // Validate destination directory
        if (!is_dir($dst)) {
            if (!File::makeDirectory($dst, 0755, true)) {
                Log::error('RefreshStorage: Failed to create destination directory: ' . $dst);
                return;
            }
        }

        try {
            $files = File::allFiles($src);
        } catch (\Exception $e) {
            Log::error('RefreshStorage: Failed to get files from source directory: ' . $e->getMessage());
            return;
        }

        foreach ($files as $file) {
            try {
                $relativePath = $file->getRelativePathname();
                $srcFile = $file->getRealPath();
                
                // Skip if source file path is empty or invalid
                if (empty($srcFile) || !file_exists($srcFile) || !is_readable($srcFile)) {
                    continue;
                }
                
                $dstFile = $dst . DIRECTORY_SEPARATOR . $relativePath;

                if (!file_exists($dstFile) || filemtime($srcFile) > filemtime($dstFile)) {
                    $dstDir = dirname($dstFile);

                    if (!is_dir($dstDir)) {
                        File::makeDirectory($dstDir, 0755, true);
                    }

                    File::copy($srcFile, $dstFile);
                }
            } catch (\Exception $e) {
                // Log the error but continue with other files
                Log::error('Failed to process file in RefreshStorage: ' . $e->getMessage() . ' - File: ' . ($file->getPathname() ?? 'unknown'));
            }
        }
    }
}
