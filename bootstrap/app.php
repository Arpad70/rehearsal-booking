<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register custom rate limiters
        RateLimiter::for('qr-reader', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });
        
        RateLimiter::for('access-validation', function (Request $request) {
            $maxAttempts = (int) config('reservations.api_access_rate_limit', 60);
            return Limit::perMinute($maxAttempts)->by($request->ip());
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
