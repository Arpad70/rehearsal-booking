<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;  
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

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
        Vite::macro('image', fn(string $asset) => Vite::asset("resources/images/{$asset}"));  

        // Register model observers
        \App\Models\Reservation::observe(\App\Observers\ReservationObserver::class);
        \App\Models\ServiceAccess::observe(\App\Observers\ServiceAccessObserver::class);
        \App\Models\Equipment::observe(\App\Observers\EquipmentObserver::class);

        // Register custom rate limiters
        RateLimiter::for('qr-reader', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });
        
        RateLimiter::for('access-validation', function (Request $request) {
            $maxAttempts = (int) config('reservations.api_access_rate_limit', 60);
            return Limit::perMinute($maxAttempts)->by($request->ip());
        });
    }
}