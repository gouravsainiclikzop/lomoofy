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
        // Load email configuration from database
        try {
            $emailService = app(\App\Services\EmailService::class);
            if ($emailService->isConfigured()) {
                $emailService->applyMailConfiguration();
            }
        } catch (\Exception $e) {
            // Silently fail during migration or when database is not available
            \Log::debug('Email configuration not loaded: ' . $e->getMessage());
        }

        // Share data globally for footer and other views
        view()->composer('*', function ($view) {
            $serviceHighlight = \App\Models\ServiceHighlight::getInstance();
            $legalPages = \App\Models\LegalPage::getInstance();
            $categories = \App\Models\Category::where('parent_id', null)
                ->where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->limit(6)
                ->get();
            
            $view->with([
                'serviceHighlight' => $serviceHighlight,
                'legalPages' => $legalPages,
                'categories' => $categories
            ]);
        });
    }
}
