<?php

namespace App\Http\Controllers;

use App\Models\KpiDefinition;
use App\Models\Project;
use App\Services\KpiCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class KpiController extends Controller
{
    protected KpiCalculatorService $kpiCalculator;

    public function __construct(KpiCalculatorService $kpiCalculator)
    {
        $this->kpiCalculator = $kpiCalculator;
    }

    /**
     * Display a listing of KPI definitions
     */
    public function index(Request $request)
    {
        $query = KpiDefinition::with('createdBy')->orderBy('created_at', 'desc');

        // Filters
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $kpis = $query->paginate(15)->withQueryString();

        return Inertia::render('KPIs/Index', [
            'kpis' => $kpis,
            'filters' => $request->only(['module', 'is_active', 'search'])
        ]);
    }

    /**
     * Show the form for creating a new KPI definition
     */
    public function create()
    {
        return Inertia::render('KPIs/Create', [
            'projects' => Project::select('id', 'name')->get(),
        ]);
    }

    /**
     * Store a newly created KPI definition
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'module' => 'required|in:progress_payments,timesheets,financials,safety,equipment,stock,quantities,projects,general',
            'formula' => 'required|string',
            'target_value' => 'nullable|numeric',
            'warning_threshold' => 'nullable|numeric',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($validated) {
            $validated['created_by'] = Auth::id();

            KpiDefinition::create($validated);
        });

        return redirect()->route('kpis.index')
            ->with('success', 'KPI tanımı başarıyla oluşturuldu.');
    }

    /**
     * Show the form for editing the specified KPI definition
     */
    public function edit(KpiDefinition $kpi)
    {
        $kpi->load('createdBy');

        return Inertia::render('KPIs/Edit', [
            'kpi' => $kpi,
            'projects' => Project::select('id', 'name')->get(),
        ]);
    }

    /**
     * Update the specified KPI definition
     */
    public function update(Request $request, KpiDefinition $kpi)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'module' => 'required|in:progress_payments,timesheets,financials,safety,equipment,stock,quantities,projects,general',
            'formula' => 'required|string',
            'target_value' => 'nullable|numeric',
            'warning_threshold' => 'nullable|numeric',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($kpi, $validated) {
            $kpi->update($validated);
        });

        return redirect()->route('kpis.index')
            ->with('success', 'KPI tanımı başarıyla güncellendi.');
    }

    /**
     * Remove the specified KPI definition
     */
    public function destroy(KpiDefinition $kpi)
    {
        $kpi->delete();

        return redirect()->route('kpis.index')
            ->with('success', 'KPI tanımı başarıyla silindi.');
    }

    /**
     * Calculate KPI value
     */
    public function calculate(Request $request, KpiDefinition $kpi)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $result = $this->kpiCalculator->calculate($kpi, $validated['project_id'] ?? null);

        return response()->json($result);
    }
}
