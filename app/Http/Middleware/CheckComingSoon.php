<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\CompanySetting;

class CheckComingSoon
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get coming_soon setting
        $settings = CompanySetting::first();
        $comingSoon = $settings && $settings->coming_soon;

        // If coming soon is enabled
        if ($comingSoon) {
            // Allow authenticated admin users to access admin routes and API routes
            if (Auth::check() && Auth::guard('web')->check()) {
                $path = $request->path();
                
                // Allow access to admin routes
                $adminRoutes = [
                    'admin',
                    'dashboard',
                    'profile',
                    'brands',
                    'categories',
                    'products',
                    'orders',
                    'customers',
                    'inventory',
                    'leads',
                    'coupons',
                    'carts',
                    'roles',
                    'users',
                    'sections',
                    'attributes',
                    'units',
                    'warehouses',
                    'shipping',
                    'field-management',
                    'master-data',
                    'featured-category-style',
                    'our-collection',
                    'testimonials',
                    'home-sliders',
                    'service-highlights',
                    'lead-masters',
                    'exports',
                ];
                
                // Check if route starts with any admin path
                $isAdminRoute = false;
                foreach ($adminRoutes as $route) {
                    if (str_starts_with($path, $route) || str_starts_with($path, 'api/')) {
                        $isAdminRoute = true;
                        break;
                    }
                }
                
                if ($isAdminRoute) {
                    return $next($request);
                }
            }
            
            // Block all other routes and show coming soon page
            return response()->view('coming-soon', [], 503);
        }

        return $next($request);
    }
}
