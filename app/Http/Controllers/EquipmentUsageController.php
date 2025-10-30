<?php

namespace App\Http\Controllers;

use App\Models\EquipmentUsage;
use App\Models\Equipment;
use App\Models\Project;
use App\Models\Employee;
use App\Models\FinancialTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EquipmentUsageController extends Controller
{
    public function index(Request $request)
    {
        $query = EquipmentUsage::with(['equipment', 'project', 'operator', 'createdBy'])
            ->orderBy('start_date', 'desc');

        // Filters
        if ($request->filled('equipment_id')) {
            $query->where('equipment_id', $request->equipment_id);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('work_description', 'like', "%{$request->search}%")
                  ->orWhere('operator_name', 'like', "%{$request->search}%")
                  ->orWhere('work_site_location', 'like', "%{$request->search}%");
            });
        }

        $usages = $query->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total_usages' => EquipmentUsage::count(),
            'ongoing' => EquipmentUsage::where('status', 'ongoing')->count(),
            'completed' => EquipmentUsage::where('status', 'completed')->count(),
            'total_rental_cost' => EquipmentUsage::where('status', 'completed')->sum('rental_cost'),
        ];

        return Inertia::render('EquipmentUsages/Index', [
            'usages' => $usages,
            'equipments' => Equipment::select('id', 'code', 'name')->get(),
            'projects' => Project::select('id', 'name')->get(),
            'stats' => $stats,
            'filters' => $request->only(['equipment_id', 'project_id', 'status', 'search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('EquipmentUsages/Create', [
            'equipments' => Equipment::where('status', 'available')
                ->orWhere('status', 'in_use')
                ->select('id', 'code', 'name', 'type', 'ownership', 'rental_cost_daily')
                ->get(),
            'projects' => Project::select('id', 'name')->get(),
            'employees' => Employee::select('id', 'first_name', 'last_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:equipments,id',
            'project_id' => 'required|exists:projects,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'operator_id' => 'nullable|exists:employees,id',
            'operator_name' => 'nullable|string|max:255',
            'work_site_location' => 'nullable|string|max:255',
            'work_description' => 'nullable|string',
            'meter_start' => 'nullable|integer|min:0',
            'meter_end' => 'nullable|integer|min:0',
            'meter_unit' => 'nullable|in:hours,kilometers,cycles',
            'fuel_consumed' => 'nullable|numeric|min:0',
            'fuel_cost' => 'nullable|numeric|min:0',
            'rental_cost' => 'nullable|numeric|min:0',
            'rental_period_type' => 'nullable|in:daily,weekly,monthly',
            'notes' => 'nullable|string',
            'issues_reported' => 'nullable|array',
        ]);

        DB::transaction(function () use ($validated) {
            $validated['created_by'] = Auth::id();
            $validated['status'] = $validated['end_date'] ? 'completed' : 'ongoing';

            $usage = EquipmentUsage::create($validated);

            // Calculate durations
            $usage->calculateDuration();
            $usage->calculateMeterTotal();
            $usage->save();

            // Update equipment status if usage is ongoing
            if ($usage->status === 'ongoing') {
                $equipment = Equipment::find($validated['equipment_id']);
                $equipment->update([
                    'status' => 'in_use',
                    'current_project_id' => $validated['project_id'],
                ]);
            }

            // Create financial transaction if rental cost exists and usage is completed
            if ($usage->status === 'completed' && $usage->rental_cost > 0) {
                $this->createFinancialTransaction($usage);
            }
        });

        return redirect()->route('equipment-usages.index')
            ->with('success', 'Ekipman kullanımı başarıyla oluşturuldu.');
    }

    public function edit(EquipmentUsage $equipmentUsage)
    {
        $equipmentUsage->load(['equipment', 'project', 'operator']);

        return Inertia::render('EquipmentUsages/Edit', [
            'usage' => $equipmentUsage,
            'equipments' => Equipment::select('id', 'code', 'name', 'type', 'ownership', 'rental_cost_daily')->get(),
            'projects' => Project::select('id', 'name')->get(),
            'employees' => Employee::select('id', 'first_name', 'last_name')->get(),
        ]);
    }

    public function update(Request $request, EquipmentUsage $equipmentUsage)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:equipments,id',
            'project_id' => 'required|exists:projects,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'operator_id' => 'nullable|exists:employees,id',
            'operator_name' => 'nullable|string|max:255',
            'work_site_location' => 'nullable|string|max:255',
            'work_description' => 'nullable|string',
            'meter_start' => 'nullable|integer|min:0',
            'meter_end' => 'nullable|integer|min:0',
            'meter_unit' => 'nullable|in:hours,kilometers,cycles',
            'fuel_consumed' => 'nullable|numeric|min:0',
            'fuel_cost' => 'nullable|numeric|min:0',
            'rental_cost' => 'nullable|numeric|min:0',
            'rental_period_type' => 'nullable|in:daily,weekly,monthly',
            'status' => 'required|in:ongoing,completed,interrupted',
            'notes' => 'nullable|string',
            'issues_reported' => 'nullable|array',
        ]);

        DB::transaction(function () use ($equipmentUsage, $validated) {
            $previousStatus = $equipmentUsage->status;
            $equipmentUsage->update($validated);

            // Calculate durations
            $equipmentUsage->calculateDuration();
            $equipmentUsage->calculateMeterTotal();
            $equipmentUsage->save();

            // Update equipment status if usage is completed or interrupted
            if ($previousStatus === 'ongoing' && in_array($validated['status'], ['completed', 'interrupted'])) {
                $equipment = Equipment::find($validated['equipment_id']);
                $equipment->update([
                    'status' => 'available',
                    'current_project_id' => null,
                ]);
            }

            // Create financial transaction if rental cost exists and usage is newly completed
            if ($previousStatus !== 'completed' && $validated['status'] === 'completed' &&
                $equipmentUsage->rental_cost > 0 && !$equipmentUsage->cost_recorded) {
                $this->createFinancialTransaction($equipmentUsage);
            }
        });

        return redirect()->route('equipment-usages.index')
            ->with('success', 'Ekipman kullanımı başarıyla güncellendi.');
    }

    public function destroy(EquipmentUsage $equipmentUsage)
    {
        DB::transaction(function () use ($equipmentUsage) {
            // If usage is ongoing, update equipment status
            if ($equipmentUsage->status === 'ongoing') {
                $equipment = Equipment::find($equipmentUsage->equipment_id);
                $equipment->update([
                    'status' => 'available',
                    'current_project_id' => null,
                ]);
            }

            // Delete financial transaction if exists
            if ($equipmentUsage->financial_transaction_id) {
                FinancialTransaction::find($equipmentUsage->financial_transaction_id)?->delete();
            }

            $equipmentUsage->delete();
        });

        return redirect()->route('equipment-usages.index')
            ->with('success', 'Ekipman kullanımı başarıyla silindi.');
    }

    /**
     * Create financial transaction for equipment rental cost
     */
    private function createFinancialTransaction(EquipmentUsage $usage)
    {
        $equipment = Equipment::find($usage->equipment_id);
        $totalCost = $usage->rental_cost + ($usage->fuel_cost ?? 0);

        $transaction = FinancialTransaction::create([
            'project_id' => $usage->project_id,
            'type' => 'expense',
            'category' => 'equipment_rental',
            'amount' => $totalCost,
            'transaction_date' => $usage->end_date ?? $usage->start_date,
            'description' => "Ekipman Kira Gideri - {$equipment->name} ({$equipment->code})",
            'payment_method' => 'bank_transfer',
            'status' => 'completed',
            'notes' => "Kullanım ID: {$usage->id}, Süre: {$usage->duration_days} gün",
            'created_by' => Auth::id(),
        ]);

        $usage->update([
            'financial_transaction_id' => $transaction->id,
            'cost_recorded' => true,
        ]);
    }
}
