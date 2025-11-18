<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleAccessValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $key = "access-validate:{$ip}";
        
        // Check IP whitelist if enabled
        if (config('reservations.ip_whitelist_enabled', false)) {
            $allowedIPs = explode(',', env('ACCESS_ALLOWED_IPS', ''));
            $allowedIPs = array_map('trim', $allowedIPs);
            
            if (!in_array($ip, $allowedIPs)) {
                return response()->json(
                    ['allowed' => false, 'reason' => 'ip_not_whitelisted'],
                    403
                );
            }
        }
        
        // Apply rate limiting
        $maxAttempts = config('reservations.api_access_rate_limit', 60);
        $decayMinutes = config('reservations.api_access_rate_window', 1);
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json(
                ['allowed' => false, 'reason' => 'rate_limit_exceeded', 'retry_after' => $seconds],
                429
            );
        }
        
        RateLimiter::hit($key, $decayMinutes * 60);
        
        return $next($request);
    }
}
