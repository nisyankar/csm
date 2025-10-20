<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DeliveryController extends Controller
{
    /**
     * Teslimatları listele
     */
    public function index(Request $request): JsonResponse
    {
        $query = Delivery::with([
            'purchaseOrder.supplier:id,company_name',
            'purchaseOrder.purchasingRequest:id,title,request_code',
            'receivedBy:id,name'
        ]);

        // Filtreleme
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('purchase_order_id')) {
            $query->where('purchase_order_id', $request->purchase_order_id);
        }

        if ($request->has('is_complete')) {
            $query->where('is_complete', $request->boolean('is_complete'));
        }

        // Tarih aralığı
        if ($request->has('start_date')) {
            $query->whereDate('delivery_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('delivery_date', '<=', $request->end_date);
        }

        // Arama
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('delivery_number', 'like', "%{$search}%")
                  ->orWhere('waybill_number', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%")
                  ->orWhere('driver_name', 'like', "%{$search}%")
                  ->orWhere('vehicle_plate', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->get('sort_by', 'delivery_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $deliveries = $query->paginate($perPage);

        return response()->json($deliveries);
    }

    /**
     * Yeni teslimat kaydı oluştur
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'delivery_date' => 'required|date',
            'delivery_time' => 'nullable|date_format:H:i',
            'waybill_number' => 'required|string|max:255',
            'waybill_date' => 'required|date',
            'waybill_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'invoice_number' => 'nullable|string|max:255',
            'invoice_date' => 'nullable|date',
            'invoice_amount' => 'nullable|numeric|min:0',
            'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'delivery_address' => 'required|string',
            'driver_name' => 'required|string|max:255',
            'vehicle_plate' => 'required|string|max:50',
            'driver_phone' => 'required|string|max:50',
            'items_count' => 'required|integer|min:1',
            'is_complete' => 'boolean',
            'missing_items' => 'nullable|string',
            'notes' => 'nullable|string',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpg,jpeg,png|max:5120',
        ]);

        try {
            // Dosyaları yükle
            if ($request->hasFile('waybill_file')) {
                $validated['waybill_file_path'] = $request->file('waybill_file')
                    ->store('deliveries/waybills', 'public');
            }

            if ($request->hasFile('invoice_file')) {
                $validated['invoice_file_path'] = $request->file('invoice_file')
                    ->store('deliveries/invoices', 'public');
            }

            // Fotoğrafları yükle
            if ($request->hasFile('photos')) {
                $photoPaths = [];
                foreach ($request->file('photos') as $photo) {
                    $photoPaths[] = $photo->store('deliveries/photos', 'public');
                }
                $validated['photos'] = $photoPaths;
            }

            $validated['status'] = 'scheduled';

            $delivery = Delivery::create($validated);

            return response()->json([
                'message' => 'Teslimat kaydı başarıyla oluşturuldu.',
                'data' => $delivery->load(['purchaseOrder', 'purchaseOrder.supplier'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Teslimat kaydı oluşturulamadı.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Teslimat detayı
     */
    public function show(Delivery $delivery): JsonResponse
    {
        $delivery->load([
            'purchaseOrder.supplier',
            'purchaseOrder.purchasingRequest.items',
            'receivedBy:id,name,email'
        ]);

        return response()->json([
            'data' => $delivery
        ]);
    }

    /**
     * Teslimat güncelle
     */
    public function update(Request $request, Delivery $delivery): JsonResponse
    {
        // Tamamlanmış teslimatlar güncellenemez
        if ($delivery->status === 'completed') {
            return response()->json([
                'message' => 'Tamamlanmış teslimatlar güncellenemez.'
            ], 403);
        }

        $validated = $request->validate([
            'delivery_date' => 'sometimes|required|date',
            'delivery_time' => 'nullable|date_format:H:i',
            'waybill_number' => 'sometimes|required|string|max:255',
            'waybill_date' => 'sometimes|required|date',
            'invoice_number' => 'nullable|string|max:255',
            'invoice_date' => 'nullable|date',
            'invoice_amount' => 'nullable|numeric|min:0',
            'delivery_address' => 'sometimes|required|string',
            'driver_name' => 'sometimes|required|string|max:255',
            'vehicle_plate' => 'sometimes|required|string|max:50',
            'driver_phone' => 'sometimes|required|string|max:50',
            'items_count' => 'sometimes|required|integer|min:1',
            'is_complete' => 'boolean',
            'missing_items' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            // Dosya güncellemeleri
            if ($request->hasFile('waybill_file')) {
                // Eski dosyayı sil
                if ($delivery->waybill_file_path) {
                    Storage::disk('public')->delete($delivery->waybill_file_path);
                }
                $validated['waybill_file_path'] = $request->file('waybill_file')
                    ->store('deliveries/waybills', 'public');
            }

            if ($request->hasFile('invoice_file')) {
                // Eski dosyayı sil
                if ($delivery->invoice_file_path) {
                    Storage::disk('public')->delete($delivery->invoice_file_path);
                }
                $validated['invoice_file_path'] = $request->file('invoice_file')
                    ->store('deliveries/invoices', 'public');
            }

            $delivery->update($validated);

            return response()->json([
                'message' => 'Teslimat başarıyla güncellendi.',
                'data' => $delivery
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Teslimat güncellenemedi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Teslimat sil
     */
    public function destroy(Delivery $delivery): JsonResponse
    {
        // Sadece scheduled teslimatlar silinebilir
        if ($delivery->status !== 'scheduled') {
            return response()->json([
                'message' => 'Sadece planlanmış teslimatlar silinebilir.'
            ], 403);
        }

        try {
            // Dosyaları sil
            if ($delivery->waybill_file_path) {
                Storage::disk('public')->delete($delivery->waybill_file_path);
            }
            if ($delivery->invoice_file_path) {
                Storage::disk('public')->delete($delivery->invoice_file_path);
            }
            if ($delivery->photos) {
                foreach ($delivery->photos as $photo) {
                    Storage::disk('public')->delete($photo);
                }
            }

            $delivery->delete();

            return response()->json([
                'message' => 'Teslimat başarıyla silindi.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Teslimat silinemedi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Teslimatı teslim alındı olarak işaretle
     */
    public function markAsReceived(Request $request, Delivery $delivery): JsonResponse
    {
        if ($delivery->status === 'completed') {
            return response()->json([
                'message' => 'Teslimat zaten tamamlanmış.'
            ], 400);
        }

        $validated = $request->validate([
            'receiver_name' => 'required|string|max:255',
            'quality_check' => 'required|in:passed,failed,partial',
            'quality_notes' => 'nullable|string',
            'is_complete' => 'required|boolean',
            'missing_items' => 'nullable|required_if:is_complete,false|string',
            'damage_report' => 'nullable|string',
        ]);

        try {
            $delivery->markAsReceived(Auth::id());
            $delivery->update($validated);

            // Eğer teslimat tam ise, purchase order'ı güncelle
            if ($validated['is_complete'] && $validated['quality_check'] === 'passed') {
                $delivery->purchaseOrder->update(['status' => 'completed']);
            }

            return response()->json([
                'message' => 'Teslimat başarıyla teslim alındı.',
                'data' => $delivery
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'İşlem başarısız.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Teslimatı reddet
     */
    public function reject(Request $request, Delivery $delivery): JsonResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
            'damage_report' => 'nullable|string',
        ]);

        try {
            $delivery->update([
                'status' => 'rejected',
                'rejection_reason' => $validated['rejection_reason'],
                'damage_report' => $validated['damage_report'] ?? null,
            ]);

            return response()->json([
                'message' => 'Teslimat reddedildi.',
                'data' => $delivery
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'İşlem başarısız.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bugünkü teslimatlar
     */
    public function today(): JsonResponse
    {
        $deliveries = Delivery::with(['purchaseOrder.supplier', 'purchaseOrder.purchasingRequest'])
            ->whereDate('delivery_date', today())
            ->orderBy('delivery_time')
            ->get();

        return response()->json([
            'data' => $deliveries
        ]);
    }

    /**
     * Bekleyen teslimatlar
     */
    public function pending(): JsonResponse
    {
        $deliveries = Delivery::with(['purchaseOrder.supplier', 'purchaseOrder.purchasingRequest'])
            ->where('status', 'scheduled')
            ->where('delivery_date', '>=', today())
            ->orderBy('delivery_date')
            ->orderBy('delivery_time')
            ->get();

        return response()->json([
            'data' => $deliveries
        ]);
    }

    /**
     * Teslimat istatistikleri
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = Delivery::query();

        // Tarih filtresi
        if ($request->has('start_date')) {
            $query->whereDate('delivery_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('delivery_date', '<=', $request->end_date);
        }

        $stats = [
            'total' => $query->count(),
            'by_status' => [
                'scheduled' => (clone $query)->where('status', 'scheduled')->count(),
                'in_transit' => (clone $query)->where('status', 'in_transit')->count(),
                'completed' => (clone $query)->where('status', 'completed')->count(),
                'rejected' => (clone $query)->where('status', 'rejected')->count(),
            ],
            'complete_deliveries' => (clone $query)->where('is_complete', true)->count(),
            'incomplete_deliveries' => (clone $query)->where('is_complete', false)->count(),
            'quality_check' => [
                'passed' => (clone $query)->where('quality_check', 'passed')->count(),
                'failed' => (clone $query)->where('quality_check', 'failed')->count(),
                'partial' => (clone $query)->where('quality_check', 'partial')->count(),
            ],
            'total_invoice_amount' => (clone $query)->sum('invoice_amount'),
        ];

        return response()->json($stats);
    }

    /**
     * Sipariş bazında teslimatlar
     */
    public function byPurchaseOrder(int $purchaseOrderId): JsonResponse
    {
        $deliveries = Delivery::where('purchase_order_id', $purchaseOrderId)
            ->with('receivedBy:id,name')
            ->orderBy('delivery_date', 'desc')
            ->get();

        return response()->json([
            'data' => $deliveries
        ]);
    }
}