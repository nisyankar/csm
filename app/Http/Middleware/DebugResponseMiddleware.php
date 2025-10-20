<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DebugResponseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Sadece Inertia request'leri için debug
        if ($request->header('X-Inertia')) {
            $content = $response->getContent();
            
            // Content'i logla
            \Log::info('Inertia Response Debug', [
                'url' => $request->fullUrl(),
                'content_length' => strlen($content),
                'content_preview' => substr($content, 0, 500),
                'is_valid_json' => json_decode($content) !== null,
                'json_error' => json_last_error_msg(),
                'headers' => $response->headers->all()
            ]);

            // JSON geçerli değilse detayları logla
            if (json_decode($content) === null && json_last_error() !== JSON_ERROR_NONE) {
                \Log::error('Invalid JSON Response', [
                    'url' => $request->fullUrl(),
                    'content' => $content,
                    'json_error' => json_last_error_msg()
                ]);
            }
        }

        return $response;
    }
}