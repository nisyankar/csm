<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalePayment;
use App\Models\UnitSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SalePaymentController extends Controller
{
    /**
     * Display a listing of sale payments
     */
    public function index(Request $request)
    {
        $query = SalePayment::with([
            'unitSale.project',
            'unitSale.projectUnit',
            'customer',
            'approver'
        ]);

        // Filters
        if ($request->filled('unit_sale_id')) {
            $query->forUnitSale($request->unit_sale_id);
        }

        if ($request->filled('customer_id')) {
            $query->forCustomer($request->customer_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_type')) {
            $query->byPaymentType($request->payment_type);
        }

        if ($request->filled('overdue_only') && $request->overdue_only) {
            $query->overdue();
        }

        if ($request->filled('due_month')) {
            $query->dueThisMonth();
        }

        // Sorting
        $sortField = $request->get('sort_by', 'due_date');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $payments = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $payments->items(),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
            'links' => [
                'first' => $payments->url(1),
                'last' => $payments->url($payments->lastPage()),
                'prev' => $payments->previousPageUrl(),
                'next' => $payments->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Store a new payment
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit_sale_id' => 'required|exists:unit_sales,id',
            'customer_id' => 'required|exists:customers,id',
            'payment_type' => 'required|in:down_payment,installment,additional,penalty,discount',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $payment = SalePayment::create(array_merge(
            $request->all(),
            ['created_by' => Auth::id()]
        ));

        return response()->json([
            'success' => true,
            'message' => 'Ödeme kaydı başarıyla oluşturuldu.',
            'data' => $payment,
        ], 201);
    }

    /**
     * Display the specified payment
     */
    public function show(SalePayment $payment)
    {
        $payment->load([
            'unitSale.project',
            'unitSale.projectUnit',
            'customer',
            'approver'
        ]);

        return response()->json([
            'success' => true,
            'data' => $payment,
        ]);
    }

    /**
     * Update the specified payment
     */
    public function update(Request $request, SalePayment $payment)
    {
        if ($payment->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Ödenmiş taksitler güncellenemez.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'sometimes|numeric|min:0',
            'due_date' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $payment->update(array_merge(
            $request->all(),
            ['updated_by' => Auth::id()]
        ));

        return response()->json([
            'success' => true,
            'message' => 'Ödeme kaydı başarıyla güncellendi.',
            'data' => $payment,
        ]);
    }

    /**
     * Remove the specified payment
     */
    public function destroy(SalePayment $payment)
    {
        if ($payment->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Ödenmiş taksitler silinemez.',
            ], 422);
        }

        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ödeme kaydı başarıyla silindi.',
        ]);
    }

    /**
     * Mark payment as paid
     */
    public function markAsPaid(Request $request, SalePayment $payment)
    {
        $validator = Validator::make($request->all(), [
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,bank_transfer,credit_card,check,bank_loan',
            'transaction_reference' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $payment->markAsPaid(
                $request->paid_amount,
                $request->payment_method,
                $request->transaction_reference
            );

            // Check if all payments are completed and update sale status
            $unitSale = $payment->unitSale;
            $allPaid = $unitSale->salePayments()
                ->whereNotIn('status', ['paid'])
                ->count() === 0;

            if ($allPaid && $unitSale->status !== 'completed') {
                $unitSale->update(['status' => 'in_payment']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ödeme başarıyla kaydedildi.',
                'data' => $payment->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Ödeme kaydedilirken bir hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve payment
     */
    public function approve(SalePayment $payment)
    {
        $payment->approve(Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Ödeme onaylandı.',
            'data' => $payment,
        ]);
    }

    /**
     * Cancel payment
     */
    public function cancel(SalePayment $payment)
    {
        if ($payment->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Ödenmiş taksitler iptal edilemez.',
            ], 422);
        }

        $payment->cancel();

        return response()->json([
            'success' => true,
            'message' => 'Ödeme iptal edildi.',
            'data' => $payment,
        ]);
    }

    /**
     * Calculate late fees for overdue payments
     */
    public function calculateLateFees(Request $request)
    {
        $feePercentage = $request->get('fee_percentage', 1.5);

        $overduePayments = SalePayment::overdue()->get();

        foreach ($overduePayments as $payment) {
            $payment->calculateLateFee($feePercentage);
        }

        return response()->json([
            'success' => true,
            'message' => count($overduePayments) . ' adet gecikme faizi hesaplandı.',
        ]);
    }

    /**
     * Send payment reminders
     */
    public function sendReminders(Request $request)
    {
        $daysBeforeDue = $request->get('days_before_due', 7);

        $payments = SalePayment::pending()
            ->whereDate('due_date', '<=', now()->addDays($daysBeforeDue))
            ->whereDate('due_date', '>=', now())
            ->get();

        foreach ($payments as $payment) {
            $payment->sendReminder();
        }

        return response()->json([
            'success' => true,
            'message' => count($payments) . ' adet hatırlatma gönderildi.',
        ]);
    }

    /**
     * Get payment statistics
     */
    public function stats(Request $request)
    {
        $query = SalePayment::query();

        if ($request->filled('unit_sale_id')) {
            $query->forUnitSale($request->unit_sale_id);
        }

        if ($request->filled('customer_id')) {
            $query->forCustomer($request->customer_id);
        }

        $stats = [
            'total_payments' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'total_paid' => $query->where('status', 'paid')->sum('paid_amount'),
            'total_pending' => $query->whereIn('status', ['pending', 'partial'])->sum('remaining_amount'),
            'overdue_count' => (clone $query)->overdue()->count(),
            'overdue_amount' => (clone $query)->overdue()->sum('remaining_amount'),
            'due_this_month' => (clone $query)->dueThisMonth()->sum('amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get upcoming payments
     */
    public function upcoming(Request $request)
    {
        $days = $request->get('days', 30);

        $payments = SalePayment::with(['unitSale.project', 'customer'])
            ->pending()
            ->dueInRange(now(), now()->addDays($days))
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }
}
