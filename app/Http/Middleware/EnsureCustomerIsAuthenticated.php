<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;

class EnsureCustomerIsAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * Check if customer is authenticated via Laravel session.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if customer is authenticated using session
        if (!Auth::guard('customer')->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHENTICATED',
                        'message' => 'Customer authentication required',
                    ],
                ], 401);
            }
            
            // For Blade routes, redirect to home with a flag to show login modal
            return redirect()->route('frontend.index')
                ->with('show_login', true)
                ->with('redirect_after_login', $request->fullUrl());
        }
        
        // Verify it's a Customer model, not a User (admin)
        $customer = Auth::guard('customer')->user();
        if (!$customer || !($customer instanceof Customer)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'UNAUTHENTICATED',
                        'message' => 'Customer authentication required',
                    ],
                ], 401);
            }
            
            return redirect()->route('frontend.index')
                ->with('show_login', true)
                ->with('redirect_after_login', $request->fullUrl());
        }
        
        // Customer is authenticated, proceed
        return $next($request);
    }
}
