<?php

namespace App\Http\Controllers;

use App\Models\SalePayment;
use App\Models\UnitSale;
use App\Models\Customer;
use App\Models\FinancialTransaction;
use App\Models\IncomeCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalePaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = SalePayment::with([
            'unitSale.project',
            'unitSale.projectUnit',
            'customer'
        ]);

        // Filters
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
            $query->whereYear('due_date', substr($request->due_month, 0, 4))
                ->whereMonth('due_date', substr($request->due_month, 5, 2));
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('payment_number', 'like', "%{$request->search}%")
                    ->orWhereHas('customer', function ($q2) use ($request) {
                        $q2->where('first_name', 'like', "%{$request->search}%")
                            ->orWhere('last_name', 'like', "%{$request->search}%");
                    });
            });
        }

        $salePayments = $query->orderBy('due_date', 'asc')->paginate(15)->withQueryString();

        return Inertia::render('Sales/Payments/Index', [
            'salePayments' => $salePayments,
            'filters' => $request->only(['status', 'payment_type', 'due_month', 'overdue_only', 'search'])
        ]);
    }

    public function create()
    {
        $unitSales = UnitSale::with(['project', 'projectUnit', 'customer'])
            ->whereIn('status', ['contracted', 'in_payment'])
            ->latest()
            ->get();

        $customers = Customer::select('id', 'first_name', 'last_name', 'company_name', 'customer_type')
            ->whereIn('customer_status', ['active'])
            ->latest()
            ->get();

        return Inertia::render('Sales/Payments/Create', [
            'unitSales' => $unitSales,
            'customers' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_sale_id' => 'required|exists:unit_sales,id',
            'customer_id' => 'required|exists:customers,id',
            'installment_number' => 'nullable|integer|min:1',
            'payment_type' => 'required|in:down_payment,installment,additional,penalty,discount',
            'amount' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'currency' => 'required|in:TRY,USD,EUR',
            'late_fee' => 'nullable|numeric|min:0',
            'due_date' => 'required|date',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|in:cash,bank_transfer,credit_card,check,bank_loan',
            'bank_name' => 'nullable|string|max:255',
            'check_number' => 'nullable|string|max:255',
            'check_date' => 'nullable|date',
            'transaction_reference' => 'nullable|string|max:255',
            'status' => 'required|in:pending,paid,partial,overdue,cancelled',
            'notes' => 'nullable|string',
        ]);

        // Kalan tutarı hesapla
        $validated['paid_amount'] = $validated['paid_amount'] ?? 0;
        $validated['remaining_amount'] = $validated['amount'] - $validated['paid_amount'];

        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $payment = SalePayment::create($validated);

        // Eğer direkt 'paid' olarak kaydedildiyse gelir kaydı oluştur
        if ($payment->status === 'paid' && $payment->paid_amount > 0 && $payment->payment_date) {
            $this->createIncomeTransaction($payment, [
                'paid_amount' => $payment->paid_amount,
                'payment_method' => $payment->payment_method,
                'payment_date' => $payment->payment_date,
                'transaction_reference' => $payment->transaction_reference,
            ]);
        }

        return redirect()->route('sales.payments.show', $payment)
            ->with('success', 'Ödeme kaydı başarıyla oluşturuldu.');
    }

    public function show(SalePayment $payment)
    {
        return Inertia::render('Sales/Payments/Show', [
            'payment' => $payment->load(['unitSale.project', 'unitSale.projectUnit', 'customer'])
        ]);
    }

    public function edit(SalePayment $payment)
    {
        $unitSales = UnitSale::with(['project', 'projectUnit', 'customer'])
            ->whereIn('status', ['contracted', 'in_payment', 'completed'])
            ->latest()
            ->get();

        $customers = Customer::select('id', 'first_name', 'last_name', 'company_name', 'customer_type')
            ->whereIn('customer_status', ['active', 'inactive'])
            ->latest()
            ->get();

        return Inertia::render('Sales/Payments/Edit', [
            'payment' => $payment->load(['unitSale', 'customer']),
            'unitSales' => $unitSales,
            'customers' => $customers,
        ]);
    }

    public function update(Request $request, SalePayment $payment)
    {
        $validated = $request->validate([
            'unit_sale_id' => 'required|exists:unit_sales,id',
            'customer_id' => 'required|exists:customers,id',
            'installment_number' => 'nullable|integer|min:1',
            'payment_type' => 'required|in:down_payment,installment,additional,penalty,discount',
            'amount' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'currency' => 'required|in:TRY,USD,EUR',
            'late_fee' => 'nullable|numeric|min:0',
            'due_date' => 'required|date',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|in:cash,bank_transfer,credit_card,check,bank_loan',
            'bank_name' => 'nullable|string|max:255',
            'check_number' => 'nullable|string|max:255',
            'check_date' => 'nullable|date',
            'transaction_reference' => 'nullable|string|max:255',
            'status' => 'required|in:pending,paid,partial,overdue,cancelled',
            'notes' => 'nullable|string',
        ]);

        // Kalan tutarı hesapla
        $validated['paid_amount'] = $validated['paid_amount'] ?? 0;
        $validated['remaining_amount'] = $validated['amount'] - $validated['paid_amount'] + ($validated['late_fee'] ?? 0);

        $validated['updated_by'] = auth()->id();

        // Önceki durumu kaydet
        $wasUnpaid = $payment->status !== 'paid';

        $payment->update($validated);

        // Eğer status 'pending' veya başka durumdan 'paid'e değiştiyse ve daha önce gelir kaydı oluşturulmamışsa
        if ($wasUnpaid && $payment->status === 'paid' && $payment->paid_amount > 0 && $payment->payment_date) {
            // Daha önce gelir kaydı oluşturulmuş mu kontrol et
            $existingTransaction = FinancialTransaction::where('source_module', 'sale_payment')
                ->where('source_id', $payment->id)
                ->exists();

            if (!$existingTransaction) {
                $this->createIncomeTransaction($payment, [
                    'paid_amount' => $payment->paid_amount,
                    'payment_method' => $payment->payment_method,
                    'payment_date' => $payment->payment_date,
                    'transaction_reference' => $payment->transaction_reference,
                ]);
            }
        }

        return redirect()->route('sales.payments.show', $payment)
            ->with('success', 'Ödeme kaydı başarıyla güncellendi.');
    }

    public function destroy(SalePayment $payment)
    {
        // Ödenmiş durumda ise silmeyi engelle
        if ($payment->status === 'paid') {
            return redirect()->back()
                ->with('error', 'Ödenmiş ödeme kayıtları silinemez.');
        }

        $payment->delete();

        return redirect()->route('sales.payments.index')
            ->with('success', 'Ödeme kaydı başarıyla silindi.');
    }

    public function markAsPaid(Request $request, SalePayment $payment)
    {
        $validated = $request->validate([
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,bank_transfer,credit_card,check,bank_loan',
            'payment_date' => 'required|date',
            'transaction_reference' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
        ]);

        $payment->markAsPaid(
            $validated['paid_amount'],
            $validated['payment_method'],
            $validated['transaction_reference'] ?? null
        );

        $payment->update([
            'payment_date' => $validated['payment_date'],
            'bank_name' => $validated['bank_name'] ?? null,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Otomatik gelir kaydı oluştur
        $this->createIncomeTransaction($payment, $validated);

        return redirect()->route('sales.payments.show', $payment)
            ->with('success', 'Ödeme kaydedildi ve gelir kaydı oluşturuldu.');
    }

    /**
     * Ödeme alındığında otomatik gelir kaydı oluştur
     */
    private function createIncomeTransaction(SalePayment $payment, array $paymentData)
    {
        // Bu ödeme için daha önce gelir kaydı oluşturulmuş mu kontrol et
        $existingTransaction = FinancialTransaction::where('source_module', 'sale_payment')
            ->where('source_id', $payment->id)
            ->exists();

        if ($existingTransaction) {
            \Log::info("Ödeme ID {$payment->id} için zaten gelir kaydı mevcut. Yeni kayıt oluşturulmadı.");
            return;
        }

        // Konut Satışları kategorisini bul (veya genel Satış Gelirleri)
        $incomeCategory = IncomeCategory::where('code', 'SATIS-KONUT')
            ->orWhere('code', 'SATIS')
            ->first();

        if (!$incomeCategory) {
            // Kategori bulunamazsa logla ama devam et
            \Log::warning('Satış gelir kategorisi bulunamadı. FinancialTransaction oluşturulamadı.');
            return;
        }

        // Ödeme tipi açıklaması
        $paymentTypeLabel = match($payment->payment_type) {
            'down_payment' => 'Peşinat',
            'installment' => $payment->installment_number . '. Taksit',
            'additional' => 'Ek Ödeme',
            'penalty' => 'Gecikme Cezası',
            default => 'Ödeme'
        };

        // Müşteri ve proje bilgileri
        $unitSale = $payment->unitSale;
        $customer = $payment->customer;
        $customerName = $customer->customer_type === 'corporate'
            ? $customer->company_name
            : $customer->first_name . ' ' . $customer->last_name;

        // Birim bilgisi
        $unitInfo = $unitSale->projectUnit->unit_code ?? 'Birim';

        FinancialTransaction::create([
            'project_id' => $unitSale->project_id,
            'transaction_type' => 'income',
            'category_id' => $incomeCategory->id,
            'transaction_date' => $paymentData['payment_date'],
            'amount' => $paymentData['paid_amount'],
            'description' => "{$customerName} - {$unitInfo} - {$paymentTypeLabel} Ödemesi",
            'source_module' => 'sale_payment',
            'source_id' => $payment->id,
            'invoice_number' => $payment->payment_number ?? null,
            'payment_method' => $paymentData['payment_method'],
            'payment_status' => 'paid',
            'paid_amount' => $paymentData['paid_amount'],
            'notes' => "Satış No: {$unitSale->sale_number}\nÖdeme No: {$payment->payment_number}\n" .
                      ($paymentData['transaction_reference'] ? "Referans: {$paymentData['transaction_reference']}" : ''),
            'created_by' => auth()->id(),
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
    }
}
