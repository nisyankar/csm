<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Bildirim listesi - Kullanıcının tüm bildirimleri
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = $user->notifications();

        // Sadece okunmayanlar
        if ($request->boolean('unread_only')) {
            $query->whereNull('read_at');
        }

        // Sıralama
        $query->orderBy('created_at', 'desc');

        // Sayfalama
        $perPage = $request->query('per_page', 20);
        $notifications = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $this->getNotificationType($notification->type),
                    'title' => $notification->data['title'] ?? 'Bildirim',
                    'message' => $notification->data['message'] ?? '',
                    'data' => $notification->data,
                    'is_read' => $notification->read_at !== null,
                    'read_at' => $notification->read_at?->format('Y-m-d H:i:s'),
                    'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'unread_count' => $user->unreadNotifications()->count(),
            ],
        ]);
    }

    /**
     * Okunmamış bildirimler
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function unread(Request $request): JsonResponse
    {
        $user = Auth::user();

        $perPage = $request->query('per_page', 20);
        $notifications = $user->unreadNotifications()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $this->getNotificationType($notification->type),
                    'title' => $notification->data['title'] ?? 'Bildirim',
                    'message' => $notification->data['message'] ?? '',
                    'data' => $notification->data,
                    'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * Bildirim detayı
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $notification->id,
                'type' => $this->getNotificationType($notification->type),
                'title' => $notification->data['title'] ?? 'Bildirim',
                'message' => $notification->data['message'] ?? '',
                'data' => $notification->data,
                'is_read' => $notification->read_at !== null,
                'read_at' => $notification->read_at?->format('Y-m-d H:i:s'),
                'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Bildirimi okundu olarak işaretle
     *
     * @param string $id
     * @return JsonResponse
     */
    public function markAsRead(string $id): JsonResponse
    {
        $user = Auth::user();
        $notification = $user->unreadNotifications()->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Bildirim okundu olarak işaretlendi.',
            'data' => [
                'id' => $notification->id,
                'read_at' => $notification->read_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Tüm bildirimleri okundu olarak işaretle
     *
     * @return JsonResponse
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        $count = $user->unreadNotifications()->count();

        $user->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => "{$count} bildirim okundu olarak işaretlendi.",
            'data' => [
                'marked_count' => $count,
            ],
        ]);
    }

    /**
     * Bildirimi sil
     *
     * @param string $id
     * @return JsonResponse
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
     * Okunmamış bildirim sayısı
     *
     * @return JsonResponse
     */
    public function unreadCount(): JsonResponse
    {
        $user = Auth::user();
        $count = $user->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count,
            ],
        ]);
    }

    /**
     * FCM Token Kaydet (Push notification için)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function registerDevice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string',
            'device_name' => 'nullable|string|max:255',
            'platform' => 'required|in:android,ios',
        ]);

        $user = Auth::user();

        // FCM token'ı user'a kaydet (user tablosuna eklenebilir veya ayrı devices tablosu kullanılabilir)
        $user->update([
            'fcm_token' => $validated['fcm_token'],
            'device_platform' => $validated['platform'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cihaz başarıyla kaydedildi.',
        ]);
    }

    /**
     * FCM Token Sil (Logout'ta kullanılır)
     *
     * @return JsonResponse
     */
    public function unregisterDevice(): JsonResponse
    {
        $user = Auth::user();

        $user->update([
            'fcm_token' => null,
            'device_platform' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cihaz kaydı silindi.',
        ]);
    }

    /**
     * Bildirim tipini Türkçe'ye çevir
     *
     * @param string $type
     * @return string
     */
    private function getNotificationType(string $type): string
    {
        // Class name'den son kısmı al
        $parts = explode('\\', $type);
        $className = end($parts);

        return match ($className) {
            'ProgressPaymentApproved' => 'progress_payment_approved',
            'ProgressPaymentRejected' => 'progress_payment_rejected',
            'TimesheetApproved' => 'timesheet_approved',
            'TimesheetRejected' => 'timesheet_rejected',
            'LeaveRequestApproved' => 'leave_request_approved',
            'LeaveRequestRejected' => 'leave_request_rejected',
            'ProjectAssigned' => 'project_assigned',
            'MaterialLowStock' => 'material_low_stock',
            default => 'general',
        };
    }
}
