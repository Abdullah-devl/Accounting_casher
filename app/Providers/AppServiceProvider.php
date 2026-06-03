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
        if (config('app.env') === 'production' 
            || env('RENDER') === 'true'
            || env('RENDER') === true
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') 
            || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
            || str_starts_with(config('app.url'), 'https://')
            || env('FORCE_HTTPS', false)
        ) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
