<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Permission;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Product::class => \App\Policies\ProductPolicy::class, 
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    { 
        $this->registerPermissionGates(); 
        $this->registerCapabilityGates();
    }

    /**
     * Register gates for all permissions dynamically.
     */
    protected function registerPermissionGates(): void
    { 
        Gate::before(function (User $user, string $ability) { 
            if ($user->hasPermission($ability)) {
                return true;
            }

            return null;  
        });
    }

    /**
     * Register capability-based gates.
     * 
     * These gates use the pattern: can.{module}.{resource}.{action}
     * Example: can.products.view, can.orders.create
     */
    protected function registerCapabilityGates(): void
    { 
        Gate::define('can', function (User $user, string $module, ?string $resource = null, ?string $action = null) {
            return $user->canDo($module, $resource ?? $module, $action);
        });

        // Register common action gates
        $actions = ['view', 'create', 'update', 'delete', 'export', 'import'];
        
        foreach ($actions as $action) {
            Gate::define("can.{$action}", function (User $user, string $module, ?string $resource = null) use ($action) {
                return $user->canDo($module, $resource ?? $module, $action);
            });
        }
    }
}

