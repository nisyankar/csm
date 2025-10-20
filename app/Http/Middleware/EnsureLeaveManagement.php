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

        if (!PermissionHelper::canAccessLeaveManagement(auth()->user())) {
            abort(403, 'İzin yönetimine erişim yetkiniz bulunmamaktadır.');
        }

        return $next($request);
    }
}