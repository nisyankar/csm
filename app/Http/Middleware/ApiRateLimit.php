<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimit
{
    /**
     * Default rate limits per role
     */
    protected array $defaultLimits = [
        'admin' => 1000,        // 1000 requests per hour
        'hr' => 500,           // 500 requests per hour
        'project_manager' => 300, // 300 requests per hour
        'site_manager' => 300,   // 300 requests per hour
        'foreman' => 200,       // 200 requests per hour
        'employee' => 100,      // 100 requests per hour
        'guest' => 50,          // 50 requests per hour for unauthenticated
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $customLimit = null): Response
    {
        // Development ortamında rate limiting'i devre dışı bırak
        if (config('app.env') === 'local' || config('app.debug') === true) {
            return $next($request);
        }

        $identifier = $this->getIdentifier($request);
        $limit = $this->getLimit($request, $customLimit);

        $cacheKey = "api_rate_limit:{$identifier}";
        $attempts = Cache::get($cacheKey, 0);

        // Limit aşıldı mı?
        if ($attempts >= $limit) {
            return response()->json([
                'message' => 'API rate limit exceeded.',
                'limit' => $limit,
                'attempts' => $attempts,
                'reset_time' => now()->addHour()->timestamp,
                'retry_after' => 3600 // 1 hour in seconds
            ], 429);
        }

        // Sayacı artır
        Cache::put($cacheKey, $attempts + 1, now()->addHour());

        $response = $next($request);

        // Rate limit bilgilerini header'a ekle
        $response->headers->set('X-RateLimit-Limit', $limit);
        $response->headers->set('X-RateLimit-Remaining', max(0, $limit - $attempts - 1));
        $response->headers->set('X-RateLimit-Reset', now()->addHour()->timestamp);

        return $response;
    }

    /**
     * Get unique identifier for rate limiting
     */
    private function getIdentifier(Request $request): string
    {
        if (Auth::check()) {
            return 'user:' . Auth::id();
        }
        
        // For guests, use IP address
        return 'ip:' . $request->ip();
    }

    /**
     * Get rate limit for current user
     */
    private function getLimit(Request $request, ?int $customLimit): int
    {
        // Custom limit belirtildiyse onu kullan
        if ($customLimit) {
            return $customLimit;
        }
        
        if (Auth::check()) {
            $user = Auth::user();
            
            // Kullanıcının özel limit'i var mı?
            if ($user->api_rate_limit) {
                return $user->api_rate_limit;
            }
            
            // Rol bazlı limit
            $userRoles = $user->roles->pluck('name')->toArray();
            
            foreach (['admin', 'hr', 'project_manager', 'site_manager', 'foreman', 'employee'] as $role) {
                if (in_array($role, $userRoles)) {
                    return $this->defaultLimits[$role];
                }
            }
            
            return $this->defaultLimits['employee']; // Default for authenticated users
        }
        
        return $this->defaultLimits['guest']; // Default for guests
    }
}