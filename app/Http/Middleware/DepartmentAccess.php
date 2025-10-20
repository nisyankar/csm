<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Department;

class DepartmentAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $departmentParam = 'department'): Response
    {
        // Kullanıcı giriş yapmış mı?
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Kimlik doğrulama gereklidir.'], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Admin ve HR her departmana erişebilir
        if ($user->hasRole(['admin', 'hr'])) {
            return $next($request);
        }

        // Departman ID'sini al
        $departmentId = $request->route($departmentParam);
        
        if (!$departmentId) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Departman ID belirtilmemiş.'], 400);
            }
            abort(400, 'Departman ID belirtilmemiş.');
        }

        // Departman var mı?
        $department = Department::find($departmentId);
        if (!$department) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Departman bulunamadı.'], 404);
            }
            abort(404, 'Departman bulunamadı.');
        }

        // Erişim kontrolü
        if (!$this->canAccessDepartment($user, $department)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Bu departmana erişim yetkiniz bulunmamaktadır.',
                    'department_id' => $departmentId,
                    'department_name' => $department->name
                ], 403);
            }
            
            abort(403, 'Bu departmana erişim yetkiniz bulunmamaktadır.');
        }

        return $next($request);
    }

    /**
     * Kullanıcının departmana erişim yetkisi var mı?
     */
    private function canAccessDepartment($user, Department $department): bool
    {
        // Departman müdürü
        if ($department->manager_id === $user->employee_id) {
            return true;
        }

        // Çalışan bu departmanda çalışıyor mu?
        if ($user->employee && $user->employee->department_id === $department->id) {
            return true;
        }

        // Proje yöneticisi bu departmanın projesine erişimi var mı?
        if ($user->hasRole(['project_manager', 'site_manager'])) {
            if ($department->project) {
                $project = $department->project;
                
                // Proje yöneticisi
                if ($project->project_manager_id === $user->employee_id) {
                    return true;
                }
                
                // Şantiye şefi
                if ($project->site_manager_id === $user->employee_id) {
                    return true;
                }
            }
        }

        // Üst departman müdürü (hiyerarşi kontrolü)
        if ($this->isParentDepartmentManager($user, $department)) {
            return true;
        }

        return false;
    }

    /**
     * Kullanıcı bu departmanın üst departmanının müdürü mü?
     */
    private function isParentDepartmentManager($user, Department $department): bool
    {
        $parentDepartment = $department->parent;
        
        while ($parentDepartment) {
            if ($parentDepartment->manager_id === $user->employee_id) {
                return true;
            }
            $parentDepartment = $parentDepartment->parent;
        }
        
        return false;
    }
}