<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ActivityLog;

class CheckProjectAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Kullanıcı giriş yapmamışsa
        if (!$user) {
            return redirect()->route('login');
        }

        // Admin ve HR her projeye erişebilir
        if (in_array($user->user_type, ['admin', 'hr'])) {
            return $next($request);
        }

        // Route parametrelerinden project_id al
        $projectId = $request->route('project')
            ?? $request->route('project_id')
            ?? $request->input('project_id');

        // Project model instance ise ID'yi al
        if (is_object($projectId) && method_exists($projectId, 'getKey')) {
            $projectId = $projectId->getKey();
        }

        // Project ID yoksa devam et (genel sayfalar için)
        if (!$projectId) {
            return $next($request);
        }

        // Proje erişim kontrolü
        if (!$user->canAccessProject($projectId)) {
            // Activity log kaydet
            ActivityLog::log(
                'access_denied',
                'Proje erişimi engellendi',
                null,
                [
                    'project_id' => $projectId,
                    'user_id' => $user->id,
                    'route' => $request->route()->getName(),
                ],
                'warning'
            );

            abort(403, 'Bu projeye erişim yetkiniz bulunmamaktadır.');
        }

        return $next($request);
    }
}
