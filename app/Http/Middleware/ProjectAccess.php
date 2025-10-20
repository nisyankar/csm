<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Project;

class ProjectAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $projectParam = 'project'): Response
    {
        // Kullanıcı giriş yapmış mı?
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Kimlik doğrulama gereklidir.'], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Admin ve HR her proje erişebilir
        if ($user->hasRole(['admin', 'hr'])) {
            return $next($request);
        }

        // Proje ID'sini al
        $projectId = $request->route($projectParam);
        
        if (!$projectId) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Proje ID belirtilmemiş.'], 400);
            }
            abort(400, 'Proje ID belirtilmemiş.');
        }

        // Proje var mı?
        $project = Project::find($projectId);
        if (!$project) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Proje bulunamadı.'], 404);
            }
            abort(404, 'Proje bulunamadı.');
        }

        // Erişim kontrolü
        if (!$this->canAccessProject($user, $project)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Bu projeye erişim yetkiniz bulunmamaktadır.',
                    'project_id' => $projectId,
                    'project_name' => $project->name
                ], 403);
            }
            
            abort(403, 'Bu projeye erişim yetkiniz bulunmamaktadır.');
        }

        return $next($request);
    }

    /**
     * Kullanıcının projeye erişim yetkisi var mı?
     */
    private function canAccessProject($user, Project $project): bool
    {
        // Proje yöneticisi
        if ($project->project_manager_id === $user->employee_id) {
            return true;
        }

        // Şantiye şefi
        if ($project->site_manager_id === $user->employee_id) {
            return true;
        }

        // Çalışan bu projede çalışıyor mu?
        if ($user->employee && $user->employee->current_project_id === $project->id) {
            return true;
        }

        // Kullanıcının project_access listesinde var mı?
        if ($user->project_access && in_array($project->id, $user->project_access)) {
            return true;
        }

        // Çalışan bu projeye atanmış mı? (many-to-many)
        if ($user->employee) {
            $isAssigned = $user->employee->projects()->where('project_id', $project->id)->exists();
            if ($isAssigned) {
                return true;
            }
        }

        return false;
    }
}