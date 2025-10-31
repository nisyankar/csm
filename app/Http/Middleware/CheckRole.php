<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Kullanıcı giriş yapmış mı?
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Kimlik doğrulama gereklidir.'], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Kullanıcı aktif mi?
        if (!$user->is_active) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Hesabınız devre dışı bırakılmış.'], 403);
            }
            
            Auth::logout();
            return redirect()->route('login')->with('error', 'Hesabınız devre dışı bırakılmış.');
        }

        // Erişim süresi dolmuş mu?
        if ($user->isAccessExpired()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Erişim süreniz dolmuş.'], 403);
            }
            
            Auth::logout();
            return redirect()->route('login')->with('error', 'Erişim süreniz dolmuş.');
        }

        // Hesap kilitli mi?
        if ($user->getIsLockedAttribute()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Hesabınız geçici olarak kilitlenmiş.',
                    'locked_until' => $user->account_locked_until
                ], 403);
            }
            
            Auth::logout();
            return redirect()->route('login')->with('error', 'Hesabınız geçici olarak kilitlenmiş.');
        }

        // Roller kontrolü
        if (!empty($roles)) {
            // Rolleri ayır (| veya , ile ayrılmış olabilir)
            $allowedRoles = [];
            foreach ($roles as $role) {
                if (str_contains($role, '|')) {
                    $allowedRoles = array_merge($allowedRoles, explode('|', $role));
                } elseif (str_contains($role, ',')) {
                    $allowedRoles = array_merge($allowedRoles, explode(',', $role));
                } else {
                    $allowedRoles[] = $role;
                }
            }

            // Boşlukları temizle
            $allowedRoles = array_map('trim', $allowedRoles);

            $hasRole = false;

            foreach ($allowedRoles as $role) {
                if ($user->hasRole($role)) {
                    $hasRole = true;
                    break;
                }
            }

            if (!$hasRole) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Bu işlem için yetkiniz bulunmamaktadır.',
                        'required_roles' => $allowedRoles,
                        'user_type' => $user->user_type
                    ], 403);
                }

                abort(403, 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
            }
        }

        return $next($request);
    }
}