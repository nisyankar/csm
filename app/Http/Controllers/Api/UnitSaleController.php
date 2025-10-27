<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UnitSale;
use App\Models\SalePayment;
use App\Models\ProjectUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class UnitSaleController extends Controller
{
    /**
     * Display a listing of unit sales
     */
    public function index(Request $request)
    {
        $query = UnitSale::with([
            'project',
            'projectUnit.projectFloor.projectStructure',
            'customer',
            'salesAgent'
        ]);

        // Filters
        if ($request->filled('project_id')) {
            $query->forProject($request->project_id);
        }

        if ($request->filled('customer_id')) {
            $query->forCustomer($request->customer_id);
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('deed_status')) {
            $query->byDeedStatus($request->deed_status);
        }

        if ($request->filled('sale_type')) {
            $query->where('sale_type', $request->sale_type);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Sorting
        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $unitSales = $query->paginate($request->get('per_page', 15));

        // Add computed fields
        $unitSales->getCollection()->transform(function ($sale) {
            $sale->total_paid = $sale->getTotalPaid();
            $sale->total_remaining = $sale->getTotalRemaining();
            $sale->has_overdue = $sale->hasOverduePayments();
            return $sale;
        });

        return response()->json([
            'success' => true,
            'data' => $unitSales->items(),
            'meta' => [
                'current_page' => $unitSales->currentPage(),
                'last_page' => $unitSales->lastPage(),
                'per_page' => $unitSales->perPage(),
                'total' => $unitSales->total(),
            ],
            'links' => [
                'first' => $unitSales->url(1),
                'last' => $unitSales->url($unitSales->lastPage()),
                'prev' => $unitSales->previousPageUrl(),
                'next' => $unitSales->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Store a new unit sale
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'project_unit_id' => 'required|exists:project_units,id|unique:unit_sales,project_unit_id',
            'customer_id' => 'required|exists:customers,id',
            'sale_type' => 'required|in:reservation,sale,presale',
            'list_price' => 'required|numeric|min:0',
            'final_price' => 'required|numeric|min:0',
            'down_payment' => 'nullable|numeric|min:0',
            'installment_count' => 'nullable|integer|min:0',
            'payment_method' => 'required|in:cash,installment,bank_loan,mixed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create sale
            $unitSale = UnitSale::create(array_merge(
                $request->all(),
                ['created_by' => Auth::id()]
            ));

            // Create payment schedule if installment
            if ($request->payment_method === 'installment' && $request->installment_count > 0) {
                $this->createPaymentSchedule($unitSale, $request);
            }

            // Update project unit status
            ProjectUnit::where('id', $request->project_unit_id)
                ->update(['status' => 'sold']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Satış başarıyla oluşturuldu.',
                'data' => $unitSale->load(['customer', 'project', 'projectUnit', 'salePayments']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Satış oluşturulurken bir hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified unit sale
     */
    public function show(UnitSale $unitSale)
    {
        $unitSale->load([
            'project',
            'projectUnit.projectFloor.projectStructure',
            'customer',
            'salesAgent',
            'salePayments' => function ($query) {
                $query->orderBy('due_date', 'asc');
            }
        ]);

        $unitSale->total_paid = $unitSale->getTotalPaid();
        $unitSale->total_remaining = $unitSale->getTotalRemaining();
        $unitSale->has_overdue = $unitSale->hasOverduePayments();

        return response()->json([
            'success' => true,
            'data' => $unitSale,
        ]);
    }

    /**
     * Update the specified unit sale
     */
    public function update(Request $request, UnitSale $unitSale)
    {
        $validator = Validator::make($request->all(), [
            'list_price' => 'sometimes|numeric|min:0',
            'final_price' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:reserved,contracted,in_payment,completed,cancelled,delayed',
            'deed_status' => 'sometimes|in:not_transferred,in_progress,transferred,postponed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $unitSale->update(array_merge(
            $request->all(),
            ['updated_by' => Auth::id()]
        ));

        return response()->json([
            'success' => true,
            'message' => 'Satış başarıyla güncellendi.',
            'data' => $unitSale,
        ]);
    }

    /**
     * Remove the specified unit sale
     */
    public function destroy(UnitSale $unitSale)
    {
        if ($unitSale->status !== 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Sadece iptal edilmiş satışlar silinebilir.',
            ], 422);
        }

        $unitSale->delete();

        // Update project unit status back to available
        ProjectUnit::where('id', $unitSale->project_unit_id)
            ->update(['status' => 'available']);

        return response()->json([
            'success' => true,
            'message' => 'Satış başarıyla silindi.',
        ]);
    }

    /**
     * Cancel sale
     */
    public function cancel(Request $request, UnitSale $unitSale)
    {
        $validator = Validator::make($request->all(), [
            'cancellation_reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $unitSale->cancel($request->cancellation_reason);

            // Update project unit status back to available
            ProjectUnit::where('id', $unitSale->project_unit_id)
                ->update(['status' => 'available']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Satış başarıyla iptal edildi.',
                'data' => $unitSale,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Satış iptal edilirken bir hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete sale
     */
    public function complete(UnitSale $unitSale)
    {
        if ($unitSale->getTotalRemaining() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Ödenmemiş taksitler var. Satış tamamlanamaz.',
            ], 422);
        }

        $unitSale->complete();

        return response()->json([
            'success' => true,
            'message' => 'Satış başarıyla tamamlandı.',
            'data' => $unitSale,
        ]);
    }

    /**
     * Transfer deed
     */
    public function transferDeed(UnitSale $unitSale)
    {
        if ($unitSale->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Sadece tamamlanmış satışların tapusu devredilebilir.',
            ], 422);
        }

        $unitSale->transferDeed();

        return response()->json([
            'success' => true,
            'message' => 'Tapu başarıyla devredildi.',
            'data' => $unitSale,
        ]);
    }

    /**
     * Get sales statistics
     */
    public function stats(Request $request)
    {
        $query = UnitSale::query();

        if ($request->filled('project_id')) {
            $query->forProject($request->project_id);
        }

        $stats = [
            'total_sales' => $query->count(),
            'total_revenue' => $query->sum('final_price'),
            'reserved' => (clone $query)->reserved()->count(),
            'contracted' => (clone $query)->contracted()->count(),
            'completed' => (clone $query)->completed()->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
            'deed_transferred' => (clone $query)->where('deed_status', 'transferred')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Create payment schedule
     */
    private function createPaymentSchedule(UnitSale $unitSale, Request $request)
    {
        $remainingAmount = $unitSale->final_price - $unitSale->down_payment;
        $monthlyInstallment = $remainingAmount / $unitSale->installment_count;

        // Create down payment if exists
        if ($unitSale->down_payment > 0) {
            SalePayment::create([
                'unit_sale_id' => $unitSale->id,
                'customer_id' => $unitSale->customer_id,
                'installment_number' => 0,
                'payment_type' => 'down_payment',
                'amount' => $unitSale->down_payment,
                'remaining_amount' => $unitSale->down_payment,
                'currency' => $unitSale->currency,
                'due_date' => $unitSale->contract_date ?? now(),
                'status' => 'pending',
                'created_by' => Auth::id(),
            ]);
        }

        // Create installments
        for ($i = 1; $i <= $unitSale->installment_count; $i++) {
            $dueDate = Carbon::parse($unitSale->contract_date ?? now())->addMonths($i);

            SalePayment::create([
                'unit_sale_id' => $unitSale->id,
                'customer_id' => $unitSale->customer_id,
                'installment_number' => $i,
                'payment_type' => 'installment',
                'amount' => $monthlyInstallment,
                'remaining_amount' => $monthlyInstallment,
                'currency' => $unitSale->currency,
                'due_date' => $dueDate,
                'status' => 'pending',
                'created_by' => Auth::id(),
            ]);
        }
    }
}
