<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
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

        // Yetki kontrolü
        if (!empty($permissions)) {
            $hasPermission = false;
            
            foreach ($permissions as $permission) {
                if ($user->can($permission)) {
                    $hasPermission = true;
                    break;
                }
            }
            
            if (!$hasPermission) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Bu işlem için yetkiniz bulunmamaktadır.',
                        'required_permissions' => $permissions,
                        'user_permissions' => $user->getAllPermissions()->pluck('name')->toArray()
                    ], 403);
                }
                
                abort(403, 'Bu işlem için yetkiniz bulunmamaktadır.');
            }
        }

        return $next($request);
    }
}