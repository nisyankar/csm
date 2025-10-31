<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProgressPaymentResource;
use App\Models\ProgressPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgressPaymentController extends Controller
{
    /**
     * Hakediş listesi (Mobil için)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = ProgressPayment::with(['project', 'subcontractor', 'workItem']);

        // Proje filtresi
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Taşeron filtresi
        if ($request->has('subcontractor_id')) {
            $query->where('subcontractor_id', $request->subcontractor_id);
        }

        // Durum filtresi
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Tarih aralığı filtresi
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('payment_date', [$request->start_date, $request->end_date]);
        }

        // Dönem filtresi (yıl-ay)
        if ($request->has('period_year')) {
            $query->where('period_year', $request->period_year);
        }
        if ($request->has('period_month')) {
            $query->where('period_month', $request->period_month);
        }

        // Kullanıcı yetkisine göre filtreleme
        $user = $request->user();
        if (!in_array($user->user_type, ['admin', 'hr', 'project_manager'])) {
            // Normal kullanıcılar sadece erişebildikleri projeleri görür
            $accessibleProjectIds = $user->getAccessibleProjects()->pluck('id')->toArray();
            $query->whereIn('project_id', $accessibleProjectIds);
        }

        // Sıralama
        $query->orderBy('payment_date', 'desc');

        // Sayfalama
        $perPage = $request->input('per_page', 15);
        $progressPayments = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => ProgressPaymentResource::collection($progressPayments),
            'meta' => [
                'current_page' => $progressPayments->currentPage(),
                'last_page' => $progressPayments->lastPage(),
                'per_page' => $progressPayments->perPage(),
                'total' => $progressPayments->total(),
            ],
        ], 200);
    }

    /**
     * Hakediş detayı
     *
     * @param ProgressPayment $progressPayment
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, ProgressPayment $progressPayment)
    {
        // Yetki kontrolü
        $user = $request->user();
        if (!in_array($user->user_type, ['admin', 'hr', 'project_manager'])) {
            if (!$user->canAccessProject($progressPayment->project_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu hakediş kaydına erişim yetkiniz yok.',
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => new ProgressPaymentResource($progressPayment->load([
                'project',
                'subcontractor',
                'workItem',
                'projectStructure',
                'projectFloor',
                'projectUnit',
                'approvedBy'
            ])),
        ], 200);
    }

    /**
     * Hakediş onaylama
     *
     * @param Request $request
     * @param ProgressPayment $progressPayment
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve(Request $request, ProgressPayment $progressPayment)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();

        // Yetki kontrolü - Sadece admin, project_manager, site_manager onaylayabilir
        if (!in_array($user->user_type, ['admin', 'project_manager', 'site_manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hakediş onaylama yetkiniz yok.',
            ], 403);
        }

        // Proje erişim kontrolü
        if (!$user->canAccessProject($progressPayment->project_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Bu projeye erişim yetkiniz yok.',
            ], 403);
        }

        // Status kontrolü - Sadece "completed" statüsündekiler onaylanabilir
        if ($progressPayment->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Sadece tamamlanmış hakedişler onaylanabilir.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            $progressPayment->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'notes' => $request->notes ?? $progressPayment->notes,
            ]);

            // Proje schedule'ı otomatik güncelleme (auto_update_schedule true ise)
            if ($progressPayment->auto_update_schedule && $progressPayment->project_schedule_id) {
                $progressPayment->syncScheduleProgress();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hakediş başarıyla onaylandı',
                'data' => new ProgressPaymentResource($progressPayment->load([
                    'project',
                    'subcontractor',
                    'workItem',
                    'approvedBy'
                ])),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Hakediş onaylanırken bir hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hakediş reddetme
     *
     * @param Request $request
     * @param ProgressPayment $progressPayment
     * @return \Illuminate\Http\JsonResponse
     */
    public function reject(Request $request, ProgressPayment $progressPayment)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $user = $request->user();

        // Yetki kontrolü
        if (!in_array($user->user_type, ['admin', 'project_manager', 'site_manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hakediş reddetme yetkiniz yok.',
            ], 403);
        }

        // Proje erişim kontrolü
        if (!$user->canAccessProject($progressPayment->project_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Bu projeye erişim yetkiniz yok.',
            ], 403);
        }

        // Status kontrolü - Sadece "completed" veya "approved" statüsündekiler reddedilebilir
        if (!in_array($progressPayment->status, ['completed', 'approved'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bu hakediş reddedilemez.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            $rejectionNote = ($progressPayment->notes ? $progressPayment->notes . "\n\n" : '')
                . "RED SEBEBİ: " . $request->rejection_reason;

            $progressPayment->update([
                'status' => 'rejected',
                'notes' => $rejectionNote,
                'approved_by' => null,
                'approved_at' => null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hakediş reddedildi',
                'data' => new ProgressPaymentResource($progressPayment->load([
                    'project',
                    'subcontractor',
                    'workItem'
                ])),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Hakediş reddedilirken bir hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Onay bekleyen hakedişler (completed status)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pending(Request $request)
    {
        $user = $request->user();

        $query = ProgressPayment::with(['project', 'subcontractor', 'workItem'])
            ->where('status', 'completed');

        // Kullanıcı yetkisine göre filtreleme
        if (!in_array($user->user_type, ['admin', 'hr', 'project_manager'])) {
            $accessibleProjectIds = $user->getAccessibleProjects()->pluck('id')->toArray();
            $query->whereIn('project_id', $accessibleProjectIds);
        }

        // Proje filtresi
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $query->orderBy('payment_date', 'desc');

        $perPage = $request->input('per_page', 15);
        $progressPayments = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => ProgressPaymentResource::collection($progressPayments),
            'meta' => [
                'current_page' => $progressPayments->currentPage(),
                'last_page' => $progressPayments->lastPage(),
                'per_page' => $progressPayments->perPage(),
                'total' => $progressPayments->total(),
            ],
        ], 200);
    }

    /**
     * İstatistikler
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics(Request $request)
    {
        $user = $request->user();

        $query = ProgressPayment::query();

        // Kullanıcı yetkisine göre filtreleme
        if (!in_array($user->user_type, ['admin', 'hr', 'project_manager'])) {
            $accessibleProjectIds = $user->getAccessibleProjects()->pluck('id')->toArray();
            $query->whereIn('project_id', $accessibleProjectIds);
        }

        // Proje filtresi
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $stats = [
            'total_count' => $query->count(),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
            'approved_count' => (clone $query)->where('status', 'approved')->count(),
            'rejected_count' => (clone $query)->where('status', 'rejected')->count(),
            'total_amount' => (clone $query)->sum(DB::raw('completed_quantity * unit_price')),
            'approved_amount' => (clone $query)->where('status', 'approved')->sum(DB::raw('completed_quantity * unit_price')),
            'pending_amount' => (clone $query)->where('status', 'pending')->sum(DB::raw('completed_quantity * unit_price')),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ], 200);
    }
}