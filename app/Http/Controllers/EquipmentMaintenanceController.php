<?php

namespace App\Http\Controllers;

use App\Models\EquipmentMaintenance;
use App\Models\Equipment;
use App\Models\FinancialTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EquipmentMaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = EquipmentMaintenance::with(['equipment', 'createdBy'])
            ->orderBy('maintenance_date', 'desc');

        // Filters
        if ($request->filled('equipment_id')) {
            $query->where('equipment_id', $request->equipment_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('service_provider')) {
            $query->where('service_provider', $request->service_provider);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('maintenance_code', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%")
                  ->orWhere('service_company', 'like', "%{$request->search}%");
            });
        }

        $maintenances = $query->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total_maintenances' => EquipmentMaintenance::count(),
            'scheduled' => EquipmentMaintenance::where('status', 'scheduled')->count(),
            'completed' => EquipmentMaintenance::where('status', 'completed')->count(),
            'total_cost' => EquipmentMaintenance::where('status', 'completed')->sum('total_cost'),
        ];

        return Inertia::render('EquipmentMaintenance/Index', [
            'maintenances' => $maintenances,
            'equipments' => Equipment::select('id', 'code', 'name')->get(),
            'stats' => $stats,
            'filters' => $request->only(['equipment_id', 'type', 'status', 'service_provider', 'search'])
        ]);
    }

    public function create()
    {
        // Generate next maintenance code
        $lastMaintenance = EquipmentMaintenance::orderBy('id', 'desc')->first();
        $nextNumber = $lastMaintenance ? (intval(substr($lastMaintenance->maintenance_code, 4)) + 1) : 1;
        $suggestedCode = 'BKM-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return Inertia::render('EquipmentMaintenance/Create', [
            'equipments' => Equipment::select('id', 'code', 'name', 'type')->get(),
            'suggestedCode' => $suggestedCode,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:equipments,id',
            'maintenance_code' => 'required|string|unique:equipment_maintenance,maintenance_code|max:255',
            'type' => 'required|in:routine,preventive,corrective,breakdown,inspection,calibration,overhaul,seasonal,other',
            'maintenance_date' => 'required|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'description' => 'required|string',
            'findings' => 'nullable|string',
            'work_performed' => 'nullable|array',
            'parts_replaced' => 'nullable|array',
            'service_provider' => 'required|in:internal,external',
            'service_company' => 'nullable|string|max:255',
            'technician_name' => 'nullable|string|max:255',
            'technician_phone' => 'nullable|string|max:255',
            'labor_cost' => 'nullable|numeric|min:0',
            'parts_cost' => 'nullable|numeric|min:0',
            'external_service_cost' => 'nullable|numeric|min:0',
            'meter_reading' => 'nullable|integer|min:0',
            'next_maintenance_date' => 'nullable|date',
            'next_maintenance_meter' => 'nullable|integer|min:0',
            'documents' => 'nullable|array',
            'photos' => 'nullable|array',
            'under_warranty' => 'boolean',
            'warranty_claim_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'recommendations' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $validated['created_by'] = Auth::id();
            $validated['status'] = 'scheduled';

            // Calculate total cost
            $validated['total_cost'] =
                ($validated['labor_cost'] ?? 0) +
                ($validated['parts_cost'] ?? 0) +
                ($validated['external_service_cost'] ?? 0);

            $maintenance = EquipmentMaintenance::create($validated);

            // Update equipment maintenance dates
            $equipment = Equipment::find($validated['equipment_id']);
            if ($maintenance->status === 'completed') {
                $equipment->update([
                    'last_maintenance_date' => $validated['maintenance_date'],
                    'next_maintenance_date' => $validated['next_maintenance_date'] ?? null,
                ]);
            }
        });

        return redirect()->route('equipment-maintenance.index')
            ->with('success', 'Bakım kaydı başarıyla oluşturuldu.');
    }

    public function edit(EquipmentMaintenance $equipmentMaintenance)
    {
        $equipmentMaintenance->load(['equipment']);

        return Inertia::render('EquipmentMaintenance/Edit', [
            'maintenance' => $equipmentMaintenance,
            'equipments' => Equipment::select('id', 'code', 'name', 'type')->get(),
        ]);
    }

    public function update(Request $request, EquipmentMaintenance $equipmentMaintenance)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:equipments,id',
            'maintenance_code' => 'required|string|max:255|unique:equipment_maintenance,maintenance_code,' . $equipmentMaintenance->id,
            'type' => 'required|in:routine,preventive,corrective,breakdown,inspection,calibration,overhaul,seasonal,other',
            'maintenance_date' => 'required|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'description' => 'required|string',
            'findings' => 'nullable|string',
            'work_performed' => 'nullable|array',
            'parts_replaced' => 'nullable|array',
            'service_provider' => 'required|in:internal,external',
            'service_company' => 'nullable|string|max:255',
            'technician_name' => 'nullable|string|max:255',
            'technician_phone' => 'nullable|string|max:255',
            'labor_cost' => 'nullable|numeric|min:0',
            'parts_cost' => 'nullable|numeric|min:0',
            'external_service_cost' => 'nullable|numeric|min:0',
            'meter_reading' => 'nullable|integer|min:0',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'next_maintenance_date' => 'nullable|date',
            'next_maintenance_meter' => 'nullable|integer|min:0',
            'documents' => 'nullable|array',
            'photos' => 'nullable|array',
            'under_warranty' => 'boolean',
            'warranty_claim_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'recommendations' => 'nullable|string',
        ]);

        DB::transaction(function () use ($equipmentMaintenance, $validated) {
            $previousStatus = $equipmentMaintenance->status;

            // Calculate total cost
            $validated['total_cost'] =
                ($validated['labor_cost'] ?? 0) +
                ($validated['parts_cost'] ?? 0) +
                ($validated['external_service_cost'] ?? 0);

            $equipmentMaintenance->update($validated);

            // Update equipment maintenance dates if status changed to completed
            if ($previousStatus !== 'completed' && $validated['status'] === 'completed') {
                $equipment = Equipment::find($validated['equipment_id']);
                $equipment->update([
                    'last_maintenance_date' => $validated['maintenance_date'],
                    'next_maintenance_date' => $validated['next_maintenance_date'] ?? null,
                    'status' => 'available', // Return to available status after maintenance
                ]);

                // Create financial transaction if cost exists and not recorded
                if ($validated['total_cost'] > 0 && !$equipmentMaintenance->cost_recorded) {
                    $this->createFinancialTransaction($equipmentMaintenance);
                }
            }

            // Update equipment status to maintenance if status is in_progress
            if ($validated['status'] === 'in_progress') {
                $equipment = Equipment::find($validated['equipment_id']);
                $equipment->update(['status' => 'maintenance']);
            }
        });

        return redirect()->route('equipment-maintenance.index')
            ->with('success', 'Bakım kaydı başarıyla güncellendi.');
    }

    public function destroy(EquipmentMaintenance $equipmentMaintenance)
    {
        DB::transaction(function () use ($equipmentMaintenance) {
            // Delete financial transaction if exists
            if ($equipmentMaintenance->financial_transaction_id) {
                FinancialTransaction::find($equipmentMaintenance->financial_transaction_id)?->delete();
            }

            $equipmentMaintenance->delete();
        });

        return redirect()->route('equipment-maintenance.index')
            ->with('success', 'Bakım kaydı başarıyla silindi.');
    }

    /**
     * Create financial transaction for maintenance cost
     */
    private function createFinancialTransaction(EquipmentMaintenance $maintenance)
    {
        $equipment = Equipment::find($maintenance->equipment_id);

        $transaction = FinancialTransaction::create([
            'project_id' => $equipment->current_project_id,
            'type' => 'expense',
            'category' => 'equipment_maintenance',
            'amount' => $maintenance->total_cost,
            'transaction_date' => $maintenance->maintenance_date,
            'description' => "Ekipman Bakım Gideri - {$equipment->name} ({$equipment->code}) - {$maintenance->type_label}",
            'payment_method' => 'bank_transfer',
            'status' => 'completed',
            'notes' => "Bakım Kodu: {$maintenance->maintenance_code}, Servis: {$maintenance->service_provider_label}",
            'created_by' => Auth::id(),
        ]);

        $maintenance->update([
            'financial_transaction_id' => $transaction->id,
            'cost_recorded' => true,
        ]);
    }
}
