<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;

class EnsureLeaveManagement
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Geçici olarak tüm giriş yapmış kullanıcılara izin ver (TEST AMAÇLI)
        // TODO: Production'da bu satırı kaldır ve aşağıdaki kontrolü aktif et
        return $next($request);

        // if (!PermissionHelper::canAccessLeaveManagement(auth()->user())) {
        //     abort(403, 'İzin yönetimine erişim yetkiniz bulunmamaktadır.');
        // }
        //
        // return $next($request);
    }
}