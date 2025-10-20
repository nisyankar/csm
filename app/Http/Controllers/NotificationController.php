<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Timesheet;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Display notifications page
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        
        // Get user notifications
        $query = $user->notifications();

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by read status
        if ($request->filled('read')) {
            if ($request->read === 'unread') {
                $query->whereNull('read_at');
            } elseif ($request->read === 'read') {
                $query->whereNotNull('read_at');
            }
        }

        // Filter by date range
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->date_from)->startOfDay(),
                Carbon::parse($request->date_to)->endOfDay(),
            ]);
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get notification summary
        $summary = [
            'unread_count' => $user->unreadNotifications()->count(),
            'total_count' => $user->notifications()->count(),
            'today_count' => $user->notifications()->whereDate('created_at', today())->count(),
            'types' => $this->getNotificationTypes($user),
        ];

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'summary' => $summary,
            'filters' => $request->only(['type', 'read', 'date_from', 'date_to']),
            'notificationTypes' => $this->getAvailableNotificationTypes(),
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Bildirim okundu olarak işaretlendi.',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        $user->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Tüm bildirimler okundu olarak işaretlendi.',
        ]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(Request $request, string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);

        $notification->update(['read_at' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Bildirim okunmadı olarak işaretlendi.',
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy(string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bildirim silindi.',
        ]);
    }

    /**
     * Bulk mark notifications as read
     */
    public function bulkMarkAsRead(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'notification_ids' => 'required|array|min:1',
            'notification_ids.*' => 'required|string',
        ]);

        $user = Auth::user();
        $notifications = $user->notifications()
            ->whereIn('id', $validated['notification_ids'])
            ->whereNull('read_at')
            ->get();

        foreach ($notifications as $notification) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => count($notifications) . ' bildirim okundu olarak işaretlendi.',
        ]);
    }

    /**
     * Bulk delete notifications
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'notification_ids' => 'required|array|min:1',
            'notification_ids.*' => 'required|string',
        ]);

        $user = Auth::user();
        $deletedCount = $user->notifications()
            ->whereIn('id', $validated['notification_ids'])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => $deletedCount . ' bildirim silindi.',
        ]);
    }

    /**
     * Get notification settings
     */
    public function settings(): Response
    {
        $user = Auth::user();
        
        // Get current notification preferences
        $preferences = $user->notification_preferences ?? [
            'email' => [
                'timesheet_approved' => true,
                'timesheet_rejected' => true,
                'leave_approved' => true,
                'leave_rejected' => true,
                'project_assigned' => true,
                'deadline_reminder' => true,
                'weekly_summary' => true,
            ],
            'push' => [
                'timesheet_approved' => true,
                'timesheet_rejected' => true,
                'leave_approved' => true,
                'leave_rejected' => true,
                'project_assigned' => false,
                'deadline_reminder' => true,
                'weekly_summary' => false,
            ],
            'sms' => [
                'emergency_only' => true,
                'leave_approved' => false,
                'leave_rejected' => false,
                'deadline_reminder' => false,
            ],
        ];

        return Inertia::render('Notifications/Settings', [
            'preferences' => $preferences,
            'availableChannels' => $this->getAvailableChannels(),
            'notificationTypes' => $this->getSettingsNotificationTypes(),
        ]);
    }

    /**
     * Update notification settings
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'preferences' => 'required|array',
            'preferences.email' => 'nullable|array',
            'preferences.push' => 'nullable|array',
            'preferences.sms' => 'nullable|array',
        ]);

        $user = Auth::user();
        $user->update([
            'notification_preferences' => $validated['preferences'],
        ]);

        return back()->with('success', 'Bildirim ayarları güncellendi.');
    }

    /**
     * Get unread notifications count for header
     */
    public function unreadCount(): JsonResponse
    {
        $user = Auth::user();
        $count = $user->unreadNotifications()->count();

        return response()->json([
            'unread_count' => $count,
        ]);
    }

    /**
     * Get recent notifications for dropdown
     */
    public function recent(): JsonResponse
    {
        $user = Auth::user();
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Send test notification
     */
    public function sendTest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:email,push,sms',
            'message' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $message = $validated['message'] ?? 'Bu bir test bildirimidir.';

        try {
            switch ($validated['type']) {
                case 'email':
                    $this->sendTestEmail($user, $message);
                    break;
                case 'push':
                    $this->sendTestPush($user, $message);
                    break;
                case 'sms':
                    $this->sendTestSms($user, $message);
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Test bildirimi gönderildi.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test bildirimi gönderilemedi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send notification to user(s)
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|in:info,success,warning,error',
            'channels' => 'required|array|min:1',
            'channels.*' => 'required|in:database,email,push,sms',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $user = Auth::user();
        
        // Check if user can send notifications
        if (!$user->hasAnyRole(['admin', 'project_manager', 'site_manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bildirim gönderme yetkiniz yok.',
            ], 403);
        }

        $recipients = User::whereIn('id', $validated['recipients'])->get();
        $sentCount = 0;
        $errors = [];

        foreach ($recipients as $recipient) {
            try {
                $this->sendCustomNotification(
                    $recipient,
                    $validated['title'],
                    $validated['message'],
                    $validated['type'],
                    $validated['channels'],
                    $validated['priority'] ?? 'normal',
                    $validated['scheduled_at'] ?? null
                );
                $sentCount++;
            } catch (\Exception $e) {
                $errors[] = [
                    'user' => $recipient->name,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$sentCount} kişiye bildirim gönderildi." . 
                        (count($errors) > 0 ? " " . count($errors) . " hata oluştu." : ""),
            'sent_count' => $sentCount,
            'errors' => $errors,
        ]);
    }

    /**
     * Get notification templates
     */
    public function templates(): JsonResponse
    {
        $templates = [
            'meeting_reminder' => [
                'title' => 'Toplantı Hatırlatması',
                'message' => 'Yarın saat {time} da {location} konumunda {subject} konulu toplantımız bulunmaktadır.',
                'type' => 'info',
                'variables' => ['time', 'location', 'subject'],
            ],
            'deadline_warning' => [
                'title' => 'Proje Deadline Uyarısı',
                'message' => '{project_name} projesi için deadline {days} gün sonra. Lütfen ilerlemenizi kontrol edin.',
                'type' => 'warning',
                'variables' => ['project_name', 'days'],
            ],
            'timesheet_reminder' => [
                'title' => 'Puantaj Hatırlatması',
                'message' => 'Bu hafta için puantaj girişi yapmayı unutmayın. Son giriş tarihi: {deadline}',
                'type' => 'info',
                'variables' => ['deadline'],
            ],
            'leave_approval' => [
                'title' => 'İzin Onayı',
                'message' => '{start_date} - {end_date} tarihleri arasındaki izin talebiniz onaylanmıştır.',
                'type' => 'success',
                'variables' => ['start_date', 'end_date'],
            ],
            'project_assignment' => [
                'title' => 'Yeni Proje Ataması',
                'message' => '{project_name} projesine {role} olarak atandınız. Proje başlangıç tarihi: {start_date}',
                'type' => 'info',
                'variables' => ['project_name', 'role', 'start_date'],
            ],
        ];

        return response()->json(['templates' => $templates]);
    }

    /**
     * Schedule bulk notifications
     */
    public function scheduleBulk(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'template' => 'required|string',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'required|exists:users,id',
            'variables' => 'nullable|array',
            'scheduled_at' => 'required|date|after:now',
            'channels' => 'required|array|min:1',
            'channels.*' => 'required|in:database,email,push,sms',
        ]);

        $user = Auth::user();
        
        if (!$user->hasAnyRole(['admin', 'project_manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Toplu bildirim planlama yetkiniz yok.',
            ], 403);
        }

        // This would typically use a job queue system
        // For now, we'll cache the scheduled notifications
        $scheduleId = 'bulk_notification_' . now()->timestamp;
        
        Cache::put($scheduleId, [
            'template' => $validated['template'],
            'recipients' => $validated['recipients'],
            'variables' => $validated['variables'] ?? [],
            'scheduled_at' => $validated['scheduled_at'],
            'channels' => $validated['channels'],
            'created_by' => $user->id,
            'status' => 'scheduled',
        ], Carbon::parse($validated['scheduled_at'])->addHours(24)); // Keep for 24 hours after schedule

        return response()->json([
            'success' => true,
            'message' => 'Toplu bildirim planlandı.',
            'schedule_id' => $scheduleId,
            'scheduled_at' => $validated['scheduled_at'],
            'recipient_count' => count($validated['recipients']),
        ]);
    }

    /**
     * Get notification statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period' => 'nullable|in:week,month,quarter,year',
        ]);

        $user = Auth::user();
        $period = $validated['period'] ?? 'month';

        // Calculate date range
        $dateRange = $this->getDateRange($period);

        $notifications = $user->notifications()
            ->whereBetween('created_at', $dateRange)
            ->get();

        $statistics = [
            'total_received' => $notifications->count(),
            'total_read' => $notifications->whereNotNull('read_at')->count(),
            'total_unread' => $notifications->whereNull('read_at')->count(),
            'read_rate' => $notifications->count() > 0 ? 
                round(($notifications->whereNotNull('read_at')->count() / $notifications->count()) * 100, 2) : 0,
            'by_type' => $notifications->groupBy('type')->map->count(),
            'daily_breakdown' => $this->getDailyNotificationBreakdown($notifications, $dateRange),
            'average_read_time' => $this->calculateAverageReadTime($notifications),
        ];

        return response()->json([
            'statistics' => $statistics,
            'period' => $period,
            'date_range' => $dateRange,
        ]);
    }

    /**
     * Export notifications
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'format' => 'required|in:csv,json',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'type' => 'nullable|string',
            'read_status' => 'nullable|in:read,unread,all',
        ]);

        $user = Auth::user();
        $query = $user->notifications();

        // Apply filters
        if (!empty($validated['date_from']) && !empty($validated['date_to'])) {
            $query->whereBetween('created_at', [
                Carbon::parse($validated['date_from'])->startOfDay(),
                Carbon::parse($validated['date_to'])->endOfDay(),
            ]);
        }

        if (!empty($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        if (!empty($validated['read_status'])) {
            if ($validated['read_status'] === 'read') {
                $query->whereNotNull('read_at');
            } elseif ($validated['read_status'] === 'unread') {
                $query->whereNull('read_at');
            }
        }

        $notifications = $query->orderBy('created_at', 'desc')->get();

        $filename = 'notifications_' . now()->format('Y_m_d_H_i_s');

        if ($validated['format'] === 'csv') {
            return $this->exportCsv($notifications, $filename);
        } else {
            return $this->exportJson($notifications, $filename);
        }
    }

    // Private helper methods

    private function getNotificationTypes(User $user): array
    {
        return $user->notifications()
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }

    private function getAvailableNotificationTypes(): array
    {
        return [
            'App\\Notifications\\TimesheetApproved' => 'Puantaj Onayı',
            'App\\Notifications\\TimesheetRejected' => 'Puantaj Reddi',
            'App\\Notifications\\LeaveApproved' => 'İzin Onayı',
            'App\\Notifications\\LeaveRejected' => 'İzin Reddi',
            'App\\Notifications\\ProjectAssigned' => 'Proje Ataması',
            'App\\Notifications\\DeadlineReminder' => 'Deadline Hatırlatması',
            'App\\Notifications\\WeeklySummary' => 'Haftalık Özet',
            'App\\Notifications\\SystemNotification' => 'Sistem Bildirimi',
        ];
    }

    private function getSettingsNotificationTypes(): array
    {
        return [
            'timesheet_approved' => 'Puantaj Onaylandı',
            'timesheet_rejected' => 'Puantaj Reddedildi',
            'leave_approved' => 'İzin Onaylandı',
            'leave_rejected' => 'İzin Reddedildi',
            'project_assigned' => 'Proje Atandı',
            'deadline_reminder' => 'Deadline Hatırlatması',
            'weekly_summary' => 'Haftalık Özet',
            'emergency_only' => 'Sadece Acil Durumlar',
        ];
    }

    private function getAvailableChannels(): array
    {
        return [
            'email' => [
                'name' => 'E-posta',
                'icon' => 'mail',
                'enabled' => true,
            ],
            'push' => [
                'name' => 'Push Bildirim',
                'icon' => 'bell',
                'enabled' => true,
            ],
            'sms' => [
                'name' => 'SMS',
                'icon' => 'message-square',
                'enabled' => config('services.sms.enabled', false),
            ],
        ];
    }

    private function sendTestEmail(User $user, string $message): void
    {
        // This would use a proper Mail class
        // For demo purposes, we'll create a database notification
        $user->notify(new \App\Notifications\TestNotification([
            'title' => 'Test E-posta',
            'message' => $message,
            'type' => 'test',
            'channel' => 'email',
        ]));
    }

    private function sendTestPush(User $user, string $message): void
    {
        // This would use a push notification service
        $user->notify(new \App\Notifications\TestNotification([
            'title' => 'Test Push',
            'message' => $message,
            'type' => 'test',
            'channel' => 'push',
        ]));
    }

    private function sendTestSms(User $user, string $message): void
    {
        // This would use an SMS service
        $user->notify(new \App\Notifications\TestNotification([
            'title' => 'Test SMS',
            'message' => $message,
            'type' => 'test',
            'channel' => 'sms',
        ]));
    }

    private function sendCustomNotification(
        User $user, 
        string $title, 
        string $message, 
        string $type, 
        array $channels, 
        string $priority,
        ?string $scheduledAt
    ): void {
        $notificationData = [
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'priority' => $priority,
            'channels' => $channels,
            'scheduled_at' => $scheduledAt,
        ];

        if ($scheduledAt && Carbon::parse($scheduledAt)->isFuture()) {
            // Schedule notification (would use job queue in real app)
            Cache::put(
                "scheduled_notification_{$user->id}_" . now()->timestamp,
                $notificationData,
                Carbon::parse($scheduledAt)->addHours(24)
            );
        } else {
            // Send immediately
            $user->notify(new \App\Notifications\CustomNotification($notificationData));
        }
    }

    private function getDateRange(string $period): array
    {
        switch ($period) {
            case 'week':
                return [now()->startOfWeek(), now()->endOfWeek()];
            case 'month':
                return [now()->startOfMonth(), now()->endOfMonth()];
            case 'quarter':
                return [now()->startOfQuarter(), now()->endOfQuarter()];
            case 'year':
                return [now()->startOfYear(), now()->endOfYear()];
            default:
                return [now()->startOfMonth(), now()->endOfMonth()];
        }
    }

    private function getDailyNotificationBreakdown($notifications, array $dateRange): array
    {
        $breakdown = [];
        $current = Carbon::parse($dateRange[0]);
        $end = Carbon::parse($dateRange[1]);

        while ($current <= $end) {
            $dayNotifications = $notifications->filter(function ($notification) use ($current) {
                return Carbon::parse($notification->created_at)->isSameDay($current);
            });

            $breakdown[] = [
                'date' => $current->format('Y-m-d'),
                'total' => $dayNotifications->count(),
                'read' => $dayNotifications->whereNotNull('read_at')->count(),
                'unread' => $dayNotifications->whereNull('read_at')->count(),
            ];

            $current->addDay();
        }

        return $breakdown;
    }

    private function calculateAverageReadTime($notifications): float
    {
        $readNotifications = $notifications->whereNotNull('read_at');
        
        if ($readNotifications->isEmpty()) {
            return 0;
        }

        $totalMinutes = $readNotifications->sum(function ($notification) {
            return Carbon::parse($notification->created_at)->diffInMinutes(Carbon::parse($notification->read_at));
        });

        return round($totalMinutes / $readNotifications->count(), 2);
    }

    private function exportCsv($notifications, string $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function() use ($notifications) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Tarih', 'Tür', 'Başlık', 'Mesaj', 'Okunma Durumu', 'Okunma Tarihi'
            ]);

            // Data
            foreach ($notifications as $notification) {
                $data = $notification->data;
                fputcsv($file, [
                    $notification->created_at->format('d.m.Y H:i'),
                    class_basename($notification->type),
                    $data['title'] ?? '',
                    $data['message'] ?? '',
                    $notification->read_at ? 'Okundu' : 'Okunmadı',
                    $notification->read_at ? $notification->read_at->format('d.m.Y H:i') : '',
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportJson($notifications, string $filename)
    {
        $data = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => class_basename($notification->type),
                'data' => $notification->data,
                'created_at' => $notification->created_at->toISOString(),
                'read_at' => $notification->read_at ? $notification->read_at->toISOString() : null,
            ];
        });

        $headers = [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}.json\"",
        ];

        return response()->json($data, 200, $headers);
    }
}