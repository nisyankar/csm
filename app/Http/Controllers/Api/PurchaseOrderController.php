<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchasingRequest;
use App\Models\SupplierQuotation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * Satın alma siparişlerini listele
     */
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseOrder::with([
            'purchasingRequest:id,request_code,title',
            'supplier:id,supplier_code,company_name,phone,email',
            'approvedBy:id,name'
        ]);

        // Filtreleme
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Tarih aralığı
        if ($request->has('start_date')) {
            $query->whereDate('order_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('order_date', '<=', $request->end_date);
        }

        // Arama
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($q) use ($search) {
                      $q->where('company_name', 'like', "%{$search}%");
                  });
            });
        }

        $sortBy = $request->get('sort_by', 'order_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $orders = $query->paginate($perPage);

        return response()->json($orders);
    }

    /**
     * Yeni sipariş oluştur
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'purchasing_request_id' => 'required|exists:purchasing_requests,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'supplier_quotation_id' => 'nullable|exists:supplier_quotations,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'required|date|after:order_date',
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer,check,credit_card',
            'payment_term_days' => 'required|integer|min:0|max:365',
            'delivery_address' => 'required|string',
            'delivery_contact' => 'required|string|max:255',
            'special_instructions' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $order = PurchaseOrder::create($validated);

            // Talebin durumunu güncelle
            $purchasingRequest = PurchasingRequest::find($validated['purchasing_request_id']);
            $purchasingRequest->markAsOrdered();

            // Eğer teklif seçildiyse, onu işaretle
            if (isset($validated['supplier_quotation_id'])) {
                $quotation = SupplierQuotation::find($validated['supplier_quotation_id']);
                $quotation->select(Auth::id());
            }

            DB::commit();

            return response()->json([
                'message' => 'Sipariş başarıyla oluşturuldu.',
                'data' => $order->load(['purchasingRequest', 'supplier'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Sipariş oluşturulamadı.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sipariş detayı
     */
    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $purchaseOrder->load([
            'purchasingRequest.items',
            'supplier',
            'supplierQuotation',
            'deliveries',
            'approvedBy:id,name'
        ]);

        return response()->json([
            'data' => $purchaseOrder
        ]);
    }

    /**
     * Sipariş güncelle
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        // Onaylanmış siparişler güncellenemez
        if (in_array($purchaseOrder->status, ['approved', 'completed', 'cancelled'])) {
            return response()->json([
                'message' => 'Bu durumdaki sipariş güncellenemez.'
            ], 403);
        }

        $validated = $request->validate([
            'expected_delivery_date' => 'sometimes|required|date',
            'subtotal' => 'sometimes|required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'total_amount' => 'sometimes|required|numeric|min:0',
            'payment_method' => 'sometimes|required|in:cash,transfer,check,credit_card',
            'payment_term_days' => 'sometimes|required|integer|min:0|max:365',
            'delivery_address' => 'sometimes|required|string',
            'delivery_contact' => 'sometimes|required|string|max:255',
            'special_instructions' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $purchaseOrder->update($validated);

            return response()->json([
                'message' => 'Sipariş başarıyla güncellendi.',
                'data' => $purchaseOrder
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Sipariş güncellenemedi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sipariş sil
     */
    public function destroy(PurchaseOrder $purchaseOrder): JsonResponse
    {
        // Sadece draft siparişler silinebilir
        if ($purchaseOrder->status !== 'draft') {
            return response()->json([
                'message' => 'Sadece taslak siparişler silinebilir.'
            ], 403);
        }

        try {
            $purchaseOrder->delete();

            return response()->json([
                'message' => 'Sipariş başarıyla silindi.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Sipariş silinemedi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Siparişi onayla
     */
    public function approve(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status !== 'pending') {
            return response()->json([
                'message' => 'Sadece beklemedeki siparişler onaylanabilir.'
            ], 403);
        }

        try {
            $purchaseOrder->approve(Auth::id());

            return response()->json([
                'message' => 'Sipariş onaylandı.',
                'data' => $purchaseOrder
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Sipariş onaylanamadı.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Siparişi iptal et
     */
    public function cancel(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500'
        ]);

        // İptal edilebilir durumlar
        if (!in_array($purchaseOrder->status, ['draft', 'pending', 'approved'])) {
            return response()->json([
                'message' => 'Bu durumdaki sipariş iptal edilemez.'
            ], 403);
        }

        try {
            $purchaseOrder->update([
                'status' => 'cancelled',
                'cancellation_reason' => $validated['cancellation_reason']
            ]);

            return response()->json([
                'message' => 'Sipariş iptal edildi.',
                'data' => $purchaseOrder
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Sipariş iptal edilemedi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ödeme durumunu güncelle
     */
    public function updatePaymentStatus(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:pending,partial,paid,overdue',
            'payment_notes' => 'nullable|string'
        ]);

        try {
            $purchaseOrder->update([
                'payment_status' => $validated['payment_status'],
                'notes' => $purchaseOrder->notes . "\n" . ($validated['payment_notes'] ?? '')
            ]);

            return response()->json([
                'message' => 'Ödeme durumu güncellendi.',
                'data' => $purchaseOrder
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Güncelleme başarısız.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * İstatistikler
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = PurchaseOrder::query();

        // Tarih filtresi
        if ($request->has('start_date')) {
            $query->whereDate('order_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('order_date', '<=', $request->end_date);
        }

        $stats = [
            'total' => $query->count(),
            'by_status' => [
                'draft' => (clone $query)->where('status', 'draft')->count(),
                'pending' => (clone $query)->where('status', 'pending')->count(),
                'approved' => (clone $query)->where('status', 'approved')->count(),
                'processing' => (clone $query)->where('status', 'processing')->count(),
                'completed' => (clone $query)->where('status', 'completed')->count(),
                'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
            ],
            'by_payment_status' => [
                'pending' => (clone $query)->where('payment_status', 'pending')->count(),
                'partial' => (clone $query)->where('payment_status', 'partial')->count(),
                'paid' => (clone $query)->where('payment_status', 'paid')->count(),
                'overdue' => (clone $query)->where('payment_status', 'overdue')->count(),
            ],
            'total_amount' => (clone $query)->sum('total_amount'),
            'pending_payment' => (clone $query)->where('payment_status', 'pending')->sum('total_amount'),
            'paid_amount' => (clone $query)->where('payment_status', 'paid')->sum('total_amount'),
        ];

        return response()->json($stats);
    }

    /**
     * Tedarikçiye göre siparişler
     */
    public function bySupplier(int $supplierId): JsonResponse
    {
        $orders = PurchaseOrder::with('purchasingRequest:id,title,request_code')
            ->where('supplier_id', $supplierId)
            ->latest('order_date')
            ->paginate(15);

        return response()->json($orders);
    }

    /**
     * Yaklaşan teslimatlar
     */
    public function upcomingDeliveries(): JsonResponse
    {
        $orders = PurchaseOrder::with(['supplier', 'purchasingRequest'])
            ->whereIn('status', ['approved', 'processing'])
            ->where('expected_delivery_date', '>=', now())
            ->where('expected_delivery_date', '<=', now()->addDays(7))
            ->orderBy('expected_delivery_date')
            ->get();

        return response()->json([
            'data' => $orders
        ]);
    }
}