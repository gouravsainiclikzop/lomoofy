<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = config('services.master_panel.api_key');

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'API_KEY_NOT_CONFIGURED',
                    'message' => 'API authentication is not configured.',
                ],
            ], 500);
        }

        $token = $request->bearerToken()
            ?? $request->header('X-API-Key')
            ?? $request->header('Authorization');

        if ($token) {
            $token = preg_replace('/^Bearer\s+/i', '', $token);
        }

        if (!$token || !hash_equals($apiKey, $token)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Invalid or missing API token.',
                ],
            ], 401);
        }

        return $next($request);
    }
}
