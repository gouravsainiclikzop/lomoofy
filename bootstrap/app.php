<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Replace default CSRF middleware with our custom one
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
        
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class, 
            'refreshStorage' => \App\Http\Middleware\RefreshStorage::class,
            'customer.auth' => \App\Http\Middleware\EnsureCustomerIsAuthenticated::class,
            'checkout.prerequisites' => \App\Http\Middleware\CheckoutPrerequisites::class,
            'simple.checkout.prerequisites' => \App\Http\Middleware\SimpleCheckoutPrerequisites::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Redirect unauthenticated users to admin.login instead of login
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $exception, \Illuminate\Http\Request $request) {
            if ($exception instanceof \Illuminate\Auth\AuthenticationException && !$request->expectsJson() && !$request->is('api/*')) {
                return redirect()->route('admin.login');
            }
            
            return $response;
        });
    })->create();
