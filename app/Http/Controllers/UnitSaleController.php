<?php

namespace App\Http\Controllers;

use App\Models\UnitSale;
use App\Models\Project;
use App\Models\Customer;
use App\Models\ProjectUnit;
use App\Models\SalePayment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class UnitSaleController extends Controller
{
    public function index(Request $request)
    {
        $query = UnitSale::with([
            'project',
            'projectUnit.floor.structure',
            'customer'
        ]);

        // Filters
        if ($request->filled('project_id')) {
            $query->forProject($request->project_id);
        }

        if ($request->filled('sale_type')) {
            $query->where('sale_type', $request->sale_type);
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('deed_status')) {
            $query->byDeedStatus($request->deed_status);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $unitSales = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Sales/UnitSales/Index', [
            'unitSales' => $unitSales,
            'projects' => Project::select('id', 'name')->get(),
            'filters' => $request->only(['project_id', 'sale_type', 'status', 'deed_status', 'search'])
        ]);
    }

    public function create()
    {
        $projects = Project::with(['structures.floors.units' => function ($query) {
            $query->where('status', 'available');
        }])
        ->select('id', 'name')
        ->get();

        $customers = Customer::select('id', 'first_name', 'last_name', 'company_name', 'customer_type', 'email', 'phone')
            ->whereIn('customer_status', ['potential', 'interested', 'active'])
            ->latest()
            ->get();

        return Inertia::render('Sales/UnitSales/Create', [
            'projects' => $projects,
            'customers' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'project_unit_id' => 'required|exists:project_units,id',
            'customer_id' => 'required|exists:customers,id',
            'sale_type' => 'required|in:reservation,sale,presale',
            'list_price' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'currency' => 'required|in:TRY,USD,EUR',
            'down_payment' => 'nullable|numeric|min:0',
            'installment_count' => 'nullable|integer|min:0',
            'payment_method' => 'required|in:cash,installment,bank_loan,mixed',
            'reservation_date' => 'nullable|date',
            'sale_date' => 'nullable|date',
            'contract_date' => 'nullable|date',
            'delivery_date' => 'nullable|date',
            'deed_status' => 'nullable|in:not_transferred,in_progress,transferred,postponed',
            'deed_type' => 'nullable|string|max:255',
            'status' => 'required|in:reserved,contracted,in_payment,completed,cancelled,delayed',
            'sales_agent_id' => 'nullable|exists:users,id',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        // Final price hesaplama
        $finalPrice = $validated['list_price'] - ($validated['discount_amount'] ?? 0);
        $validated['final_price'] = $finalPrice;

        // Aylık taksit hesaplama
        if (isset($validated['installment_count']) && $validated['installment_count'] > 0) {
            $remainingAmount = $finalPrice - ($validated['down_payment'] ?? 0);
            $validated['monthly_installment'] = $remainingAmount / $validated['installment_count'];
        }

        // Komisyon hesaplama
        if (isset($validated['commission_percentage'])) {
            $validated['commission_amount'] = $finalPrice * ($validated['commission_percentage'] / 100);
        }

        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $unitSale = UnitSale::create($validated);

        // Proje birimini satıldı olarak işaretle
        ProjectUnit::find($validated['project_unit_id'])->update([
            'status' => 'sold',
            'is_sold' => true,
            'sale_date' => now()
        ]);

        // Otomatik ödeme planı oluştur
        $this->createPaymentPlan($unitSale, $validated);

        return redirect()->route('sales.unit-sales.show', $unitSale)
            ->with('success', 'Satış kaydı ve ödeme planı başarıyla oluşturuldu.');
    }

    public function show(UnitSale $unitSale)
    {
        return Inertia::render('Sales/UnitSales/Show', [
            'unitSale' => $unitSale->load(['project', 'projectUnit', 'customer', 'salePayments'])
        ]);
    }

    public function edit(UnitSale $unitSale)
    {
        $projects = Project::with(['structures.floors.units'])
            ->select('id', 'name')
            ->get();

        $customers = Customer::select('id', 'first_name', 'last_name', 'company_name', 'customer_type', 'email', 'phone')
            ->whereIn('customer_status', ['potential', 'interested', 'active'])
            ->latest()
            ->get();

        return Inertia::render('Sales/UnitSales/Edit', [
            'unitSale' => $unitSale->load(['project', 'projectUnit', 'customer']),
            'projects' => $projects,
            'customers' => $customers,
        ]);
    }

    public function update(Request $request, UnitSale $unitSale)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'project_unit_id' => 'required|exists:project_units,id',
            'customer_id' => 'required|exists:customers,id',
            'sale_type' => 'required|in:reservation,sale,presale',
            'list_price' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'currency' => 'required|in:TRY,USD,EUR',
            'down_payment' => 'nullable|numeric|min:0',
            'installment_count' => 'nullable|integer|min:0',
            'payment_method' => 'required|in:cash,installment,bank_loan,mixed',
            'reservation_date' => 'nullable|date',
            'sale_date' => 'nullable|date',
            'contract_date' => 'nullable|date',
            'delivery_date' => 'nullable|date',
            'deed_status' => 'nullable|in:not_transferred,in_progress,transferred,postponed',
            'deed_type' => 'nullable|string|max:255',
            'title_deed_number' => 'nullable|string|max:255',
            'deed_notes' => 'nullable|string',
            'status' => 'required|in:reserved,contracted,in_payment,completed,cancelled,delayed',
            'sales_agent_id' => 'nullable|exists:users,id',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'cancellation_reason' => 'nullable|string',
        ]);

        // Final price hesaplama
        $finalPrice = $validated['list_price'] - ($validated['discount_amount'] ?? 0);
        $validated['final_price'] = $finalPrice;

        // Aylık taksit hesaplama
        if (isset($validated['installment_count']) && $validated['installment_count'] > 0) {
            $remainingAmount = $finalPrice - ($validated['down_payment'] ?? 0);
            $validated['monthly_installment'] = $remainingAmount / $validated['installment_count'];
        }

        // Komisyon hesaplama
        if (isset($validated['commission_percentage'])) {
            $validated['commission_amount'] = $finalPrice * ($validated['commission_percentage'] / 100);
        }

        $validated['updated_by'] = auth()->id();

        // Eğer birim değiştirilmişse, eski birimi tekrar uygun yap
        if ($unitSale->project_unit_id != $validated['project_unit_id']) {
            ProjectUnit::find($unitSale->project_unit_id)->update([
                'is_sold' => false,
                'sale_date' => null
            ]);
            ProjectUnit::find($validated['project_unit_id'])->update([
                'status' => 'sold',
                'is_sold' => true,
                'sale_date' => now()
            ]);
        }

        $unitSale->update($validated);

        return redirect()->route('sales.unit-sales.show', $unitSale)
            ->with('success', 'Satış kaydı başarıyla güncellendi.');
    }

    public function destroy(UnitSale $unitSale)
    {
        // Ödenmiş ödeme varsa silmeyi engelle
        $paidPayments = $unitSale->salePayments()->where('status', 'paid')->count();
        if ($paidPayments > 0) {
            return redirect()->back()
                ->with('error', 'Bu satışa ait ödenmiş ödeme kayıtları bulunmaktadır. Satış silinemez.');
        }

        // Bekleyen ödemeleri sil
        $unitSale->salePayments()->delete();

        // Proje birimini tekrar uygun yap
        ProjectUnit::find($unitSale->project_unit_id)->update([
            'is_sold' => false,
            'sale_date' => null
        ]);

        $unitSale->delete();

        return redirect()->route('sales.unit-sales.index')
            ->with('success', 'Satış kaydı ve bekleyen ödemeler başarıyla silindi.');
    }

    /**
     * Otomatik ödeme planı oluştur
     */
    private function createPaymentPlan(UnitSale $unitSale, array $validated)
    {
        $currency = $validated['currency'] ?? 'TRY';
        $startDate = $validated['contract_date'] ?? $validated['sale_date'] ?? now();
        $startDate = Carbon::parse($startDate);

        // 1. Peşinat Ödemesi
        if (isset($validated['down_payment']) && $validated['down_payment'] > 0) {
            SalePayment::create([
                'unit_sale_id' => $unitSale->id,
                'customer_id' => $unitSale->customer_id,
                'payment_type' => 'down_payment',
                'installment_number' => null,
                'amount' => $validated['down_payment'],
                'paid_amount' => 0,
                'remaining_amount' => $validated['down_payment'],
                'currency' => $currency,
                'due_date' => $startDate,
                'status' => 'pending',
                'notes' => 'Otomatik oluşturulan peşinat ödemesi',
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
        }

        // 2. Taksit Ödemeleri
        if (isset($validated['installment_count']) && $validated['installment_count'] > 0 && isset($validated['monthly_installment'])) {
            for ($i = 1; $i <= $validated['installment_count']; $i++) {
                // Her taksit için vade tarihi = başlangıç + i ay
                $dueDate = $startDate->copy()->addMonths($i);

                SalePayment::create([
                    'unit_sale_id' => $unitSale->id,
                    'customer_id' => $unitSale->customer_id,
                    'payment_type' => 'installment',
                    'installment_number' => $i,
                    'amount' => $validated['monthly_installment'],
                    'paid_amount' => 0,
                    'remaining_amount' => $validated['monthly_installment'],
                    'currency' => $currency,
                    'due_date' => $dueDate,
                    'status' => 'pending',
                    'notes' => "Otomatik oluşturulan {$i}. taksit ödemesi",
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
            }
        }
    }

    /**
     * Tapu durumunu güncelle
     */
    public function updateDeedStatus(Request $request, UnitSale $unitSale)
    {
        $validated = $request->validate([
            'deed_status' => 'required|in:not_transferred,in_progress,transferred,postponed',
            'deed_type' => 'nullable|string|max:255',
            'title_deed_number' => 'nullable|string|max:255',
            'deed_transfer_date' => 'nullable|date',
            'deed_notes' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();

        // Eğer tapu devredildi olarak işaretlendiyse ve tarih yoksa, bugünü ekle
        if ($validated['deed_status'] === 'transferred' && empty($validated['deed_transfer_date'])) {
            $validated['deed_transfer_date'] = now();
        }

        $unitSale->update($validated);

        return redirect()->back()->with('success', 'Tapu durumu başarıyla güncellendi.');
    }

    /**
     * Tapu belgesi yükle
     */
    public function uploadDeedDocument(Request $request, UnitSale $unitSale)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // Max 10MB
        ]);

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('deed_documents', $fileName, 'public');

            // Mevcut belgeler listesine ekle
            $deedDocuments = $unitSale->deed_documents ?? [];
            $deedDocuments[] = [
                'file_name' => $fileName,
                'file_path' => $filePath,
                'uploaded_at' => now()->toDateTimeString(),
                'uploaded_by' => auth()->id(),
            ];

            $unitSale->update([
                'deed_documents' => $deedDocuments,
                'updated_by' => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Tapu belgesi başarıyla yüklendi.');
        }

        return redirect()->back()->with('error', 'Belge yüklenirken bir hata oluştu.');
    }
}
