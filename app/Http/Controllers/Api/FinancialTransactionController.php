<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Services\Financial\FinancialTransactionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FinancialTransactionController extends Controller
{
    protected FinancialTransactionService $service;

    public function __construct(FinancialTransactionService $service)
    {
        $this->service = $service;
    }

    /**
     * Liste - Filtreleme ve sayfalama ile
     */
    public function index(Request $request): JsonResponse
    {
        $query = FinancialTransaction::with([
            'project:id,name',
            'incomeCategory:id,name,code',
            'expenseCategory:id,name,code',
            'createdBy:id,name',
            'approvedBy:id,name'
        ]);

        // Proje filtresi
        if ($projectId = $request->query('project_id')) {
            $query->forProject($projectId);
        }

        // Tür filtresi (income/expense)
        if ($type = $request->query('transaction_type')) {
            $query->where('transaction_type', $type);
        }

        // Kategori filtresi
        if ($categoryId = $request->query('category_id')) {
            $query->byCategory($categoryId);
        }

        // Ödeme durumu
        if ($status = $request->query('payment_status')) {
            $query->byStatus($status);
        }

        // Tarih aralığı
        if ($startDate = $request->query('start_date')) {
            $endDate = $request->query('end_date', now()->toDateString());
            $query->forPeriod($startDate, $endDate);
        }

        // Kaynak modülü (timesheet, purchasing, vb.)
        if ($sourceModule = $request->query('source_module')) {
            $query->fromSource($sourceModule);
        }

        // Onaylanmış kayıtlar
        if ($request->boolean('approved_only')) {
            $query->approved();
        }

        // Arama
        if ($search = $request->query('search')) {
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%")
                  ->orWhere('accounting_code', 'like', "%{$search}%");
            });
        }

        // Sıralama
        $sortBy = $request->query('sort_by', 'transaction_date');
        $sortOrder = $request->query('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->query('per_page', 15);
        $transactions = $query->paginate($perPage);

        return response()->json(['success' => true, 'data' => $transactions]);
    }

    /**
     * Yeni finansal işlem kaydı oluştur
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'transaction_type' => 'required|in:income,expense',
            'category_id' => 'required|integer',
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'invoice_number' => 'nullable|string|max:255',
            'invoice_date' => 'nullable|date',
            'payment_method' => 'nullable|in:cash,bank_transfer,credit_card,check,other',
            'payment_status' => 'nullable|in:pending,partial,paid,cancelled',
            'paid_amount' => 'nullable|numeric|min:0',
            'accounting_code' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Kullanıcı bilgisini ekle
        $validated['created_by'] = auth()->id();

        // Ödeme durumu varsayılan pending
        $validated['payment_status'] = $validated['payment_status'] ?? 'pending';
        $validated['paid_amount'] = $validated['paid_amount'] ?? 0;

        $transaction = $this->service->createTransaction($validated);

        return response()->json([
            'success' => true,
            'message' => 'Finansal işlem başarıyla oluşturuldu.',
            'data' => $transaction->load(['project', 'createdBy']),
        ], 201);
    }

    /**
     * Detay görüntüleme
     */
    public function show(FinancialTransaction $financialTransaction): JsonResponse
    {
        $financialTransaction->load([
            'project',
            'incomeCategory.parent',
            'expenseCategory.parent',
            'createdBy',
            'approvedBy'
        ]);

        return response()->json(['success' => true, 'data' => $financialTransaction]);
    }

    /**
     * Güncelleme
     */
    public function update(Request $request, FinancialTransaction $financialTransaction): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'sometimes|exists:projects,id',
            'transaction_type' => 'sometimes|in:income,expense',
            'category_id' => 'sometimes|integer',
            'transaction_date' => 'sometimes|date',
            'amount' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'invoice_number' => 'nullable|string|max:255',
            'invoice_date' => 'nullable|date',
            'payment_method' => 'nullable|in:cash,bank_transfer,credit_card,check,other',
            'payment_status' => 'nullable|in:pending,partial,paid,cancelled',
            'paid_amount' => 'nullable|numeric|min:0',
            'accounting_code' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $transaction = $this->service->updateTransaction($financialTransaction, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Finansal işlem başarıyla güncellendi.',
            'data' => $transaction,
        ]);
    }

    /**
     * Silme
     */
    public function destroy(FinancialTransaction $financialTransaction): JsonResponse
    {
        // Onaylanmış kayıtlar silinemez
        if ($financialTransaction->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Onaylanmış finansal işlem silinemez.',
            ], 422);
        }

        $financialTransaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Finansal işlem başarıyla silindi.',
        ]);
    }

    /**
     * Ödeme yapma
     */
    public function makePayment(Request $request, FinancialTransaction $financialTransaction): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,bank_transfer,credit_card,check,other',
        ]);

        $financialTransaction->makePayment($validated['amount'], $validated['payment_method']);

        return response()->json([
            'success' => true,
            'message' => 'Ödeme başarıyla kaydedildi.',
            'data' => $financialTransaction->fresh(),
        ]);
    }

    /**
     * Onaylama
     */
    public function approve(FinancialTransaction $financialTransaction): JsonResponse
    {
        if ($financialTransaction->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Bu işlem zaten onaylanmış.',
            ], 422);
        }

        $financialTransaction->approve(auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Finansal işlem onaylandı.',
            'data' => $financialTransaction->fresh(['approvedBy']),
        ]);
    }

    /**
     * Proje kar/zarar özeti
     */
    public function profitLoss(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'year' => 'nullable|integer',
            'month' => 'nullable|integer|between:1,12',
        ]);

        $summary = $this->service->getProfitLossSummary(
            $validated['project_id'],
            $validated['year'] ?? null,
            $validated['month'] ?? null
        );

        return response()->json(['success' => true, 'data' => $summary]);
    }

    /**
     * Kategori bazlı dağılım
     */
    public function categoryBreakdown(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'year' => 'nullable|integer',
            'month' => 'nullable|integer|between:1,12',
        ]);

        $breakdown = $this->service->getCategoryBreakdown(
            $validated['project_id'],
            $validated['year'] ?? null,
            $validated['month'] ?? null
        );

        return response()->json(['success' => true, 'data' => $breakdown]);
    }

    /**
     * Dashboard özet bilgileri
     */
    public function dashboardSummary(Request $request): JsonResponse
    {
        $projectId = $request->query('project_id');
        $year = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);

        // Bu ayki kar/zarar
        $monthly = $this->service->getProfitLossSummary($projectId, $year, $month);

        // Yıllık kar/zarar
        $yearly = $this->service->getProfitLossSummary($projectId, $year);

        // Bekleyen ödemeler
        $pendingPayments = FinancialTransaction::query()
            ->when($projectId, fn($q) => $q->forProject($projectId))
            ->pending()
            ->sum('amount');

        // Ödenmemiş faturalar
        $unpaidInvoices = FinancialTransaction::query()
            ->when($projectId, fn($q) => $q->forProject($projectId))
            ->where('payment_status', '!=', 'paid')
            ->whereNotNull('invoice_number')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'monthly' => $monthly,
                'yearly' => $yearly,
                'pending_payments' => $pendingPayments,
                'unpaid_invoices' => $unpaidInvoices,
            ]
        ]);
    }
}
