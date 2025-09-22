<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Force HTTPS in production
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
        
        // Force HTTPS if behind a load balancer/proxy
        if (request()->header('X-Forwarded-Proto') === 'https' || request()->header('X-Forwarded-SSL') === 'on') {
            URL::forceScheme('https');
        }
    }
}
