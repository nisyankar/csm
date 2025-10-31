<?php

namespace App\Http\Controllers;

use App\Models\UserProjectRole;
use App\Models\User;
use App\Models\Project;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserProjectRoleController extends Controller
{
    public function index(Request $request)
    {
        $query = UserProjectRole::with(['user', 'project', 'assignedBy'])
            ->orderBy('created_at', 'desc');

        // Filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $roles = $query->paginate(20);

        // Statistics
        $statistics = [
            'total_assignments' => UserProjectRole::count(),
            'active_assignments' => UserProjectRole::active()->count(),
            'unique_users' => UserProjectRole::active()->distinct('user_id')->count('user_id'),
            'unique_projects' => UserProjectRole::active()->distinct('project_id')->count('project_id'),
        ];

        return Inertia::render('UserProjectRoles/Index', [
            'roles' => $roles,
            'statistics' => $statistics,
            'filters' => $request->only(['user_id', 'project_id', 'role', 'is_active']),
            'users' => User::active()->select('id', 'name')->get(),
            'projects' => Project::select('id', 'name', 'project_code')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('UserProjectRoles/Create', [
            'users' => User::active()->select('id', 'name', 'email', 'user_type')->get(),
            'projects' => Project::select('id', 'name', 'project_code', 'status')->get(),
            'availableRoles' => $this->getAvailableRoles(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'role' => 'required|string|in:project_manager,site_manager,engineer,foreman,viewer,inspector,safety_officer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'responsibilities' => 'nullable|array',
            'permissions' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $validated['assigned_by'] = auth()->id();
        $validated['assigned_at'] = now();

        $projectRole = UserProjectRole::create($validated);

        // Activity log
        ActivityLog::logCreated($projectRole, [
            'user_name' => $projectRole->user->name,
            'project_name' => $projectRole->project->name,
            'role' => $projectRole->role,
        ]);

        return redirect()->route('user-project-roles.index')
            ->with('success', 'Proje rolü başarıyla atandı.');
    }

    public function edit(UserProjectRole $userProjectRole)
    {
        $userProjectRole->load(['user', 'project', 'assignedBy']);

        return Inertia::render('UserProjectRoles/Edit', [
            'userProjectRole' => $userProjectRole,
            'users' => User::active()->select('id', 'name', 'email', 'user_type')->get(),
            'projects' => Project::select('id', 'name', 'project_code', 'status')->get(),
            'availableRoles' => $this->getAvailableRoles(),
        ]);
    }

    public function update(Request $request, UserProjectRole $userProjectRole)
    {
        $validated = $request->validate([
            'role' => 'required|string|in:project_manager,site_manager,engineer,foreman,viewer,inspector,safety_officer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'responsibilities' => 'nullable|array',
            'permissions' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $oldValues = $userProjectRole->only(['role', 'start_date', 'end_date', 'is_active']);

        $userProjectRole->update($validated);

        // Activity log
        ActivityLog::logUpdated($userProjectRole, $oldValues, $validated);

        return redirect()->route('user-project-roles.index')
            ->with('success', 'Proje rolü başarıyla güncellendi.');
    }

    public function destroy(UserProjectRole $userProjectRole)
    {
        $user = $userProjectRole->user->name;
        $project = $userProjectRole->project->name;

        // Activity log
        ActivityLog::logDeleted($userProjectRole);

        $userProjectRole->delete();

        return redirect()->route('user-project-roles.index')
            ->with('success', "$user kullanıcısının $project projesindeki rolü kaldırıldı.");
    }

    public function activate(UserProjectRole $userProjectRole)
    {
        $userProjectRole->activate();

        ActivityLog::logCustom(
            'Proje rolü aktif hale getirildi',
            "Kullanıcı: {$userProjectRole->user->name}, Proje: {$userProjectRole->project->name}"
        );

        return back()->with('success', 'Rol aktif hale getirildi.');
    }

    public function deactivate(UserProjectRole $userProjectRole)
    {
        $userProjectRole->deactivate();

        ActivityLog::logCustom(
            'Proje rolü deaktif edildi',
            "Kullanıcı: {$userProjectRole->user->name}, Proje: {$userProjectRole->project->name}",
            'warning'
        );

        return back()->with('success', 'Rol deaktif edildi.');
    }

    public function byUser(User $user)
    {
        $roles = UserProjectRole::with(['project', 'assignedBy'])
            ->where('user_id', $user->id)
            ->active()
            ->get();

        return response()->json($roles);
    }

    public function byProject(Project $project)
    {
        $roles = UserProjectRole::with(['user', 'assignedBy'])
            ->where('project_id', $project->id)
            ->active()
            ->get();

        return response()->json($roles);
    }

    private function getAvailableRoles(): array
    {
        return [
            ['value' => 'project_manager', 'label' => 'Proje Yöneticisi'],
            ['value' => 'site_manager', 'label' => 'Şantiye Şefi'],
            ['value' => 'engineer', 'label' => 'Mühendis'],
            ['value' => 'foreman', 'label' => 'Forman'],
            ['value' => 'viewer', 'label' => 'Görüntüleyici'],
            ['value' => 'inspector', 'label' => 'Denetçi'],
            ['value' => 'safety_officer', 'label' => 'İSG Uzmanı'],
        ];
    }
}
