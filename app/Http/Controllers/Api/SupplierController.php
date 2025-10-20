<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    /**
     * Tedarikçi listesi
     */
    public function index(Request $request): JsonResponse
    {
        $query = Supplier::query();

        // Filtreleme
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('category')) {
            $query->whereJsonContains('categories', $request->category);
        }

        if ($request->has('has_contract')) {
            $query->where('has_contract', $request->boolean('has_contract'));
        }

        // Arama
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('supplier_code', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Sıralama
        $sortBy = $request->get('sort_by', 'company_name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // İlişkiler
        if ($request->has('with_stats')) {
            $query->withCount(['quotations', 'purchaseOrders']);
        }

        $perPage = $request->get('per_page', 15);
        $suppliers = $query->paginate($perPage);

        return response()->json($suppliers);
    }

    /**
     * Yeni tedarikçi oluştur
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'tax_number' => 'required|string|max:50|unique:suppliers,tax_number',
            'tax_office' => 'required|string|max:255',
            'address' => 'required|string',
            'contact_person' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'email' => 'required|email|max:255|unique:suppliers,email',
            'website' => 'nullable|url|max:255',
            'categories' => 'required|array|min:1',
            'categories.*' => 'string|in:concrete,steel,equipment,material,service,other',
            'specialization' => 'nullable|string',
            'payment_term_days' => 'nullable|integer|min:0|max:365',
            'payment_method' => 'nullable|in:cash,transfer,check,credit_card',
            'credit_limit' => 'nullable|numeric|min:0',
            'has_contract' => 'boolean',
            'contract_start_date' => 'nullable|required_if:has_contract,true|date',
            'contract_end_date' => 'nullable|required_if:has_contract,true|date|after:contract_start_date',
            'notes' => 'nullable|string',
        ]);

        try {
            $supplier = Supplier::create(array_merge($validated, [
                'status' => 'active',
                'rating' => 5.0,
                'total_orders' => 0,
                'total_amount' => 0,
                'on_time_delivery_count' => 0,
                'late_delivery_count' => 0,
            ]));

            return response()->json([
                'message' => 'Tedarikçi başarıyla oluşturuldu.',
                'data' => $supplier
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Tedarikçi oluşturulamadı.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tedarikçi detayı
     */
    public function show(Supplier $supplier): JsonResponse
    {
        $supplier->load([
            'quotations' => function($query) {
                $query->latest()->limit(10);
            },
            'purchaseOrders' => function($query) {
                $query->latest()->limit(10);
            }
        ]);

        // İstatistikler ekle
        $supplier->on_time_delivery_rate = $supplier->getOnTimeDeliveryRate();

        return response()->json([
            'data' => $supplier
        ]);
    }

    /**
     * Tedarikçi güncelle
     */
    public function update(Request $request, Supplier $supplier): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'sometimes|required|string|max:255',
            'tax_number' => 'sometimes|required|string|max:50|unique:suppliers,tax_number,' . $supplier->id,
            'tax_office' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string',
            'contact_person' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'email' => 'sometimes|required|email|max:255|unique:suppliers,email,' . $supplier->id,
            'website' => 'nullable|url|max:255',
            'categories' => 'sometimes|required|array|min:1',
            'categories.*' => 'string|in:concrete,steel,equipment,material,service,other',
            'specialization' => 'nullable|string',
            'rating' => 'nullable|numeric|min:0|max:5',
            'payment_term_days' => 'nullable|integer|min:0|max:365',
            'payment_method' => 'nullable|in:cash,transfer,check,credit_card',
            'credit_limit' => 'nullable|numeric|min:0',
            'has_contract' => 'boolean',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
            'status' => 'sometimes|in:active,inactive,blacklisted',
            'notes' => 'nullable|string',
        ]);

        try {
            $supplier->update($validated);

            return response()->json([
                'message' => 'Tedarikçi başarıyla güncellendi.',
                'data' => $supplier
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Tedarikçi güncellenemedi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tedarikçi sil
     */
    public function destroy(Supplier $supplier): JsonResponse
    {
        // Aktif siparişi varsa silinemez
        $activeOrders = $supplier->purchaseOrders()
            ->whereIn('status', ['pending', 'approved', 'processing'])
            ->count();

        if ($activeOrders > 0) {
            return response()->json([
                'message' => 'Aktif siparişleri olan tedarikçi silinemez.'
            ], 403);
        }

        try {
            $supplier->delete();

            return response()->json([
                'message' => 'Tedarikçi başarıyla silindi.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Tedarikçi silinemedi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tedarikçiyi kara listeye al
     */
    public function blacklist(Request $request, Supplier $supplier): JsonResponse
    {
        $validated = $request->validate([
            'blacklist_reason' => 'required|string|max:500'
        ]);

        try {
            $supplier->update([
                'status' => 'blacklisted',
                'blacklist_reason' => $validated['blacklist_reason']
            ]);

            return response()->json([
                'message' => 'Tedarikçi kara listeye alındı.',
                'data' => $supplier
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'İşlem başarısız.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tedarikçiyi aktif et
     */
    public function activate(Supplier $supplier): JsonResponse
    {
        try {
            $supplier->update([
                'status' => 'active',
                'blacklist_reason' => null
            ]);

            return response()->json([
                'message' => 'Tedarikçi aktif edildi.',
                'data' => $supplier
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'İşlem başarısız.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tedarikçi performans istatistikleri
     */
    public function performance(Supplier $supplier): JsonResponse
    {
        $stats = [
            'total_orders' => $supplier->total_orders,
            'total_amount' => $supplier->total_amount,
            'average_order_amount' => $supplier->total_orders > 0
                ? $supplier->total_amount / $supplier->total_orders
                : 0,
            'on_time_delivery_count' => $supplier->on_time_delivery_count,
            'late_delivery_count' => $supplier->late_delivery_count,
            'on_time_delivery_rate' => $supplier->getOnTimeDeliveryRate(),
            'rating' => $supplier->rating,
            'recent_orders' => $supplier->purchaseOrders()
                ->with('purchasingRequest:id,title,request_code')
                ->latest()
                ->limit(5)
                ->get(),
            'recent_quotations' => $supplier->quotations()
                ->with('purchasingRequest:id,title,request_code')
                ->latest()
                ->limit(5)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Tedarikçi karşılaştırma
     */
    public function compare(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_ids' => 'required|array|min:2|max:5',
            'supplier_ids.*' => 'required|exists:suppliers,id'
        ]);

        $suppliers = Supplier::whereIn('id', $validated['supplier_ids'])
            ->withCount(['quotations', 'purchaseOrders'])
            ->get()
            ->map(function($supplier) {
                return [
                    'id' => $supplier->id,
                    'company_name' => $supplier->company_name,
                    'rating' => $supplier->rating,
                    'total_orders' => $supplier->total_orders,
                    'total_amount' => $supplier->total_amount,
                    'on_time_delivery_rate' => $supplier->getOnTimeDeliveryRate(),
                    'payment_term_days' => $supplier->payment_term_days,
                    'credit_limit' => $supplier->credit_limit,
                    'categories' => $supplier->categories,
                ];
            });

        return response()->json([
            'data' => $suppliers
        ]);
    }

    /**
     * Kategoriye göre tedarikçiler
     */
    public function byCategory(string $category): JsonResponse
    {
        $suppliers = Supplier::active()
            ->byCategory($category)
            ->orderBy('rating', 'desc')
            ->get();

        return response()->json([
            'category' => $category,
            'data' => $suppliers
        ]);
    }
}