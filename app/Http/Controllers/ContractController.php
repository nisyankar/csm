<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Project;
use App\Models\Subcontractor;
use App\Services\Contract\ContractService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class ContractController extends Controller
{
    protected ContractService $service;

    public function __construct(ContractService $service)
    {
        $this->service = $service;
    }

    /**
     * Dashboard - Sözleşme özetleri
     */
    public function dashboard(Request $request): Response
    {
        $projectId = $request->filled('project_id') ? (int) $request->get('project_id') : null;

        $stats = $this->service->getDashboardStats($projectId);
        $expiringSoon = $this->service->getExpiringSoonContracts(30, $projectId);
        $expired = $this->service->getExpiredContracts($projectId);

        // Son eklenen sözleşmeler
        $recentContracts = Contract::with(['project', 'subcontractor'])
            ->when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $projects = Project::select('id', 'name')->where('status', 'active')->get();

        return Inertia::render('Contracts/Dashboard', [
            'stats' => $stats,
            'expiringSoon' => $expiringSoon,
            'expired' => $expired,
            'recentContracts' => $recentContracts,
            'projects' => $projects,
            'selectedProjectId' => $projectId,
        ]);
    }

    /**
     * Sözleşme listesi
     */
    public function index(Request $request): Response
    {
        $query = Contract::with(['project', 'subcontractor', 'creator']);

        // Filters
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('subcontractor_id')) {
            $query->where('subcontractor_id', $request->subcontractor_id);
        }

        if ($request->filled('contract_type')) {
            $query->where('contract_type', $request->contract_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('start_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $contracts = $query->paginate(15)->withQueryString();

        // Get filter options
        $projects = Project::select('id', 'name')->where('status', 'active')->get();
        $subcontractors = Subcontractor::select('id', 'company_name')
            ->where('status', 'active')
            ->orderBy('company_name')
            ->get();

        return Inertia::render('Contracts/Index', [
            'contracts' => $contracts,
            'filters' => $request->only([
                'project_id', 'subcontractor_id', 'contract_type',
                'status', 'start_date', 'end_date', 'search'
            ]),
            'projects' => $projects,
            'subcontractors' => $subcontractors,
        ]);
    }

    /**
     * Sözleşme oluşturma formu
     */
    public function create(Request $request): Response
    {
        $projects = Project::select('id', 'name', 'project_code')
            ->where('status', 'active')
            ->get();

        $subcontractors = Subcontractor::select('id', 'company_name', 'trade_title')
            ->where('status', 'active')
            ->orderBy('company_name')
            ->get();

        return Inertia::render('Contracts/Create', [
            'projects' => $projects,
            'subcontractors' => $subcontractors,
            'selectedProjectId' => $request->get('project_id'),
        ]);
    }

    /**
     * Sözleşme kaydet
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contract_type' => 'required|in:subcontractor,supplier',
            'contract_number' => 'nullable|string|unique:contracts,contract_number',
            'contract_name' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'subcontractor_id' => 'nullable|exists:subcontractors,id',
            'work_description' => 'nullable|string',
            'scope_of_work' => 'nullable|string',
            'contract_value' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'payment_terms' => 'nullable|string',
            'signing_date' => 'nullable|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'warranty_amount' => 'nullable|numeric|min:0',
            'warranty_type' => 'required|in:bank_letter,cash,check,none',
            'warranty_start_date' => 'nullable|date',
            'warranty_end_date' => 'nullable|date|after:warranty_start_date',
            'status' => 'required|in:draft,active',
            'notes' => 'nullable|string',
            'documents' => 'nullable|array',
        ]);

        $validated['created_by'] = auth()->id();

        try {
            $contract = $this->service->create($validated);

            return redirect()
                ->route('contracts.show', $contract)
                ->with('success', 'Sözleşme başarıyla oluşturuldu.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Sözleşme oluşturulurken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Sözleşme detayı
     */
    public function show(Contract $contract): Response
    {
        $contract->load([
            'project',
            'subcontractor',
            'creator',
            'updater',
            'progressPayments.workItem',
            'progressPayments.projectStructure',
            'progressPayments.projectFloor',
        ]);

        $summary = $this->service->getContractSummary($contract);

        return Inertia::render('Contracts/Show', [
            'contract' => $contract,
            'summary' => $summary,
        ]);
    }

    /**
     * Sözleşme düzenleme formu
     */
    public function edit(Contract $contract): Response
    {
        $contract->load(['project', 'subcontractor']);

        $projects = Project::select('id', 'name', 'project_code')
            ->where('status', 'active')
            ->get();

        $subcontractors = Subcontractor::select('id', 'company_name', 'trade_title')
            ->where('status', 'active')
            ->orderBy('company_name')
            ->get();

        return Inertia::render('Contracts/Edit', [
            'contract' => $contract,
            'projects' => $projects,
            'subcontractors' => $subcontractors,
        ]);
    }

    /**
     * Sözleşme güncelle
     */
    public function update(Request $request, Contract $contract): RedirectResponse
    {
        $validated = $request->validate([
            'contract_name' => 'required|string|max:255',
            'work_description' => 'nullable|string',
            'scope_of_work' => 'nullable|string',
            'contract_value' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'payment_terms' => 'nullable|string',
            'signing_date' => 'nullable|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'warranty_amount' => 'nullable|numeric|min:0',
            'warranty_type' => 'required|in:bank_letter,cash,check,none',
            'warranty_start_date' => 'nullable|date',
            'warranty_end_date' => 'nullable|date|after:warranty_start_date',
            'notes' => 'nullable|string',
            'documents' => 'nullable|array',
        ]);

        $validated['updated_by'] = auth()->id();

        try {
            $this->service->update($contract, $validated);

            return redirect()
                ->route('contracts.show', $contract)
                ->with('success', 'Sözleşme başarıyla güncellendi.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Sözleşme güncellenirken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Sözleşme sil
     */
    public function destroy(Contract $contract): RedirectResponse
    {
        try {
            // Hakediş kayıtları varsa silinemez
            if ($contract->progressPayments()->count() > 0) {
                return back()->with('error', 'Bu sözleşmeye bağlı hakediş kayıtları bulunduğu için silinemez.');
            }

            $contract->delete();

            return redirect()
                ->route('contracts.index')
                ->with('success', 'Sözleşme başarıyla silindi.');
        } catch (\Exception $e) {
            return back()->with('error', 'Sözleşme silinirken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Sözleşme aktive et
     */
    public function activate(Contract $contract): RedirectResponse
    {
        try {
            $this->service->activate($contract);

            return back()->with('success', 'Sözleşme aktif hale getirildi.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Sözleşme feshet
     */
    public function terminate(Request $request, Contract $contract): RedirectResponse
    {
        $validated = $request->validate([
            'termination_reason' => 'required|string',
        ]);

        try {
            $this->service->terminate($contract, $validated['termination_reason']);

            return back()->with('success', 'Sözleşme feshedildi.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Sözleşme tamamla
     */
    public function complete(Contract $contract): RedirectResponse
    {
        try {
            $this->service->complete($contract);

            return back()->with('success', 'Sözleşme tamamlandı olarak işaretlendi.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
