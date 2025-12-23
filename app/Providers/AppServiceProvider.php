<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share service highlights globally for footer
        view()->composer('*', function ($view) {
            $serviceHighlight = \App\Models\ServiceHighlight::getInstance();
            $view->with('serviceHighlight', $serviceHighlight);
        });
    }
}
