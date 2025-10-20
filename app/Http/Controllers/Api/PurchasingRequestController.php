<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchasingRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchasingRequestController extends Controller
{
    /**
     * Satınalma taleplerini listele
     */
    public function index(Request $request): JsonResponse
    {
        $query = PurchasingRequest::with([
            'requestedBy:id,name,email',
            'project:id,name,code',
            'department:id,name',
            'items',
            'supervisorApproval:id,name',
            'managerApproval:id,name'
        ]);

        // Filtreleme
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('urgency')) {
            $query->where('urgency', $request->urgency);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Tarih aralığı filtresi
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Arama
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('request_code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sıralama
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $requests = $query->paginate($perPage);

        return response()->json($requests);
    }

    /**
     * Yeni satınalma talebi oluştur
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'department_id' => 'nullable|exists:departments,id',
            'urgency' => 'required|in:low,normal,high,urgent',
            'category' => 'required|in:material,equipment,service,other',
            'required_date' => 'required|date|after:today',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.estimated_unit_price' => 'required|numeric|min:0',
            'items.*.category' => 'nullable|string',
            'items.*.specification' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Talep oluştur
            $purchasingRequest = PurchasingRequest::create([
                'request_code' => PurchasingRequest::generateRequestCode(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'requested_by' => Auth::id(),
                'project_id' => $validated['project_id'],
                'department_id' => $validated['department_id'] ?? null,
                'status' => 'draft',
                'urgency' => $validated['urgency'],
                'category' => $validated['category'],
                'required_date' => $validated['required_date'],
            ]);

            // Kalemleri oluştur
            foreach ($validated['items'] as $item) {
                $purchasingRequest->items()->create($item);
            }

            // Tahmini toplamı hesapla ve güncelle
            $estimatedTotal = $purchasingRequest->calculateEstimatedTotal();
            $purchasingRequest->update(['estimated_total' => $estimatedTotal]);

            DB::commit();

            return response()->json([
                'message' => 'Satınalma talebi başarıyla oluşturuldu.',
                'data' => $purchasingRequest->load(['items', 'requestedBy', 'project'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Satınalma talebi oluşturulamadı.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Satınalma talebini görüntüle
     */
    public function show(PurchasingRequest $purchasingRequest): JsonResponse
    {
        $purchasingRequest->load([
            'requestedBy:id,name,email',
            'project:id,name,code',
            'department:id,name',
            'items',
            'quotations.supplier',
            'purchaseOrder',
            'supervisorApproval:id,name',
            'managerApproval:id,name'
        ]);

        return response()->json([
            'data' => $purchasingRequest
        ]);
    }

    /**
     * Satınalma talebini güncelle
     */
    public function update(Request $request, PurchasingRequest $purchasingRequest): JsonResponse
    {
        // Sadece draft durumundaki talepler güncellenebilir
        if ($purchasingRequest->status !== 'draft') {
            return response()->json([
                'message' => 'Sadece taslak durumundaki talepler güncellenebilir.'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'sometimes|required|exists:projects,id',
            'department_id' => 'nullable|exists:departments,id',
            'urgency' => 'sometimes|required|in:low,normal,high,urgent',
            'category' => 'sometimes|required|in:material,equipment,service,other',
            'required_date' => 'sometimes|required|date|after:today',
            'items' => 'sometimes|required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.estimated_unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $purchasingRequest->update($validated);

            // Eğer items varsa, mevcut items'ı sil ve yenilerini ekle
            if (isset($validated['items'])) {
                $purchasingRequest->items()->delete();
                foreach ($validated['items'] as $item) {
                    $purchasingRequest->items()->create($item);
                }

                // Tahmini toplamı yeniden hesapla
                $estimatedTotal = $purchasingRequest->calculateEstimatedTotal();
                $purchasingRequest->update(['estimated_total' => $estimatedTotal]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Satınalma talebi başarıyla güncellendi.',
                'data' => $purchasingRequest->load(['items', 'requestedBy', 'project'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Satınalma talebi güncellenemedi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Satınalma talebini sil
     */
    public function destroy(PurchasingRequest $purchasingRequest): JsonResponse
    {
        // Sadece draft veya rejected durumundaki talepler silinebilir
        if (!in_array($purchasingRequest->status, ['draft', 'rejected'])) {
            return response()->json([
                'message' => 'Sadece taslak veya reddedilmiş talepler silinebilir.'
            ], 403);
        }

        try {
            $purchasingRequest->delete();

            return response()->json([
                'message' => 'Satınalma talebi başarıyla silindi.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Satınalma talebi silinemedi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Talebi onaya gönder
     */
    public function submit(PurchasingRequest $purchasingRequest): JsonResponse
    {
        if ($purchasingRequest->status !== 'draft') {
            return response()->json([
                'message' => 'Sadece taslak durumundaki talepler onaya gönderilebilir.'
            ], 403);
        }

        if ($purchasingRequest->items()->count() === 0) {
            return response()->json([
                'message' => 'Talebin en az bir kalemi olmalıdır.'
            ], 422);
        }

        try {
            $purchasingRequest->submit();

            return response()->json([
                'message' => 'Talep başarıyla onaya gönderildi.',
                'data' => $purchasingRequest
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Talep onaya gönderilemedi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Şef onayı
     */
    public function approveBySupervisor(Request $request, PurchasingRequest $purchasingRequest): JsonResponse
    {
        if ($purchasingRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Sadece beklemedeki talepler onaylanabilir.'
            ], 403);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $purchasingRequest->approveBySupervisor(Auth::id(), $validated['notes'] ?? null);

            return response()->json([
                'message' => 'Talep şef tarafından onaylandı.',
                'data' => $purchasingRequest
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Talep onaylanamadı.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Yönetici onayı
     */
    public function approveByManager(Request $request, PurchasingRequest $purchasingRequest): JsonResponse
    {
        if ($purchasingRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Sadece beklemedeki talepler onaylanabilir.'
            ], 403);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $purchasingRequest->approveByManager(Auth::id(), $validated['notes'] ?? null);

            return response()->json([
                'message' => 'Talep yönetici tarafından onaylandı.',
                'data' => $purchasingRequest
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Talep onaylanamadı.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Talebi reddet
     */
    public function reject(Request $request, PurchasingRequest $purchasingRequest): JsonResponse
    {
        if ($purchasingRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Sadece beklemedeki talepler reddedilebilir.'
            ], 403);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        try {
            $purchasingRequest->reject($validated['rejection_reason']);

            return response()->json([
                'message' => 'Talep reddedildi.',
                'data' => $purchasingRequest
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Talep reddedilemedi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Talebi iptal et
     */
    public function cancel(PurchasingRequest $purchasingRequest): JsonResponse
    {
        // İptal edilebilecek durumlar
        $cancellableStatuses = ['draft', 'pending', 'approved'];

        if (!in_array($purchasingRequest->status, $cancellableStatuses)) {
            return response()->json([
                'message' => 'Bu durumdaki talep iptal edilemez.'
            ], 403);
        }

        try {
            $purchasingRequest->cancel();

            return response()->json([
                'message' => 'Talep iptal edildi.',
                'data' => $purchasingRequest
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Talep iptal edilemedi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * İstatistikler
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = PurchasingRequest::query();

        // Tarih filtresi
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $stats = [
            'total' => $query->count(),
            'by_status' => [
                'draft' => (clone $query)->where('status', 'draft')->count(),
                'pending' => (clone $query)->where('status', 'pending')->count(),
                'approved' => (clone $query)->where('status', 'approved')->count(),
                'ordered' => (clone $query)->where('status', 'ordered')->count(),
                'delivered' => (clone $query)->where('status', 'delivered')->count(),
                'rejected' => (clone $query)->where('status', 'rejected')->count(),
                'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
            ],
            'by_urgency' => [
                'low' => (clone $query)->where('urgency', 'low')->count(),
                'normal' => (clone $query)->where('urgency', 'normal')->count(),
                'high' => (clone $query)->where('urgency', 'high')->count(),
                'urgent' => (clone $query)->where('urgency', 'urgent')->count(),
            ],
            'by_category' => [
                'material' => (clone $query)->where('category', 'material')->count(),
                'equipment' => (clone $query)->where('category', 'equipment')->count(),
                'service' => (clone $query)->where('category', 'service')->count(),
                'other' => (clone $query)->where('category', 'other')->count(),
            ],
            'total_estimated_amount' => (clone $query)->sum('estimated_total'),
            'total_actual_amount' => (clone $query)->sum('actual_total'),
        ];

        return response()->json($stats);
    }
}