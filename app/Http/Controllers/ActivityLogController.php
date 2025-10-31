<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with(['user', 'project'])
            ->orderBy('logged_at', 'desc');

        // Filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('logged_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('logged_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('action', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $logs = $query->paginate(50);

        // Statistics
        $statistics = [
            'total_logs' => ActivityLog::count(),
            'today_logs' => ActivityLog::today()->count(),
            'warning_count' => ActivityLog::bySeverity('warning')->recent(7)->count(),
            'error_count' => ActivityLog::bySeverity('error')->recent(7)->count(),
            'critical_count' => ActivityLog::bySeverity('critical')->recent(7)->count(),
        ];

        return Inertia::render('ActivityLogs/Index', [
            'logs' => $logs,
            'statistics' => $statistics,
            'filters' => $request->only(['user_id', 'project_id', 'activity_type', 'severity', 'date_from', 'date_to', 'search']),
            'users' => User::select('id', 'name')->get(),
            'projects' => Project::select('id', 'name', 'project_code')->get(),
            'activityTypes' => $this->getActivityTypes(),
            'severityLevels' => $this->getSeverityLevels(),
        ]);
    }

    public function show(ActivityLog $activityLog)
    {
        $activityLog->load(['user', 'project', 'subject']);

        return Inertia::render('ActivityLogs/Show', [
            'log' => $activityLog,
        ]);
    }

    public function userActivity(User $user, Request $request)
    {
        $query = ActivityLog::where('user_id', $user->id)
            ->orderBy('logged_at', 'desc');

        if ($request->filled('days')) {
            $query->where('logged_at', '>=', now()->subDays($request->days));
        }

        $logs = $query->paginate(30);

        return Inertia::render('ActivityLogs/UserActivity', [
            'user' => $user,
            'logs' => $logs,
        ]);
    }

    public function projectActivity(Project $project, Request $request)
    {
        $query = ActivityLog::where('project_id', $project->id)
            ->orderBy('logged_at', 'desc');

        if ($request->filled('days')) {
            $query->where('logged_at', '>=', now()->subDays($request->days));
        }

        $logs = $query->paginate(30);

        return Inertia::render('ActivityLogs/ProjectActivity', [
            'project' => $project,
            'logs' => $logs,
        ]);
    }

    public function export(Request $request)
    {
        // TODO: Implement export functionality
        return back()->with('info', 'Export özelliği yakında eklenecek.');
    }

    private function getActivityTypes(): array
    {
        return [
            ['value' => 'created', 'label' => 'Oluşturuldu'],
            ['value' => 'updated', 'label' => 'Güncellendi'],
            ['value' => 'deleted', 'label' => 'Silindi'],
            ['value' => 'viewed', 'label' => 'Görüntülendi'],
            ['value' => 'logged_in', 'label' => 'Giriş Yapıldı'],
            ['value' => 'logged_out', 'label' => 'Çıkış Yapıldı'],
            ['value' => 'access_denied', 'label' => 'Erişim Engellendi'],
            ['value' => 'custom', 'label' => 'Özel'],
        ];
    }

    private function getSeverityLevels(): array
    {
        return [
            ['value' => 'info', 'label' => 'Bilgi', 'color' => 'blue'],
            ['value' => 'warning', 'label' => 'Uyarı', 'color' => 'yellow'],
            ['value' => 'error', 'label' => 'Hata', 'color' => 'orange'],
            ['value' => 'critical', 'label' => 'Kritik', 'color' => 'red'],
        ];
    }
}
