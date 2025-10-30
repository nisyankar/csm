<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Equipment::with(['currentProject', 'createdBy'])
            ->orderBy('created_at', 'desc');

        // Filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('ownership')) {
            $query->where('ownership', $request->ownership);
        }

        if ($request->filled('project_id')) {
            $query->where('current_project_id', $request->project_id);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                  ->orWhere('name', 'like', "%{$request->search}%")
                  ->orWhere('brand', 'like', "%{$request->search}%")
                  ->orWhere('model', 'like', "%{$request->search}%");
            });
        }

        $equipments = $query->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total_equipments' => Equipment::count(),
            'available' => Equipment::where('status', 'available')->count(),
            'in_use' => Equipment::where('status', 'in_use')->count(),
            'maintenance_due' => Equipment::maintenanceDue()->count(),
        ];

        return Inertia::render('Equipments/Index', [
            'equipments' => $equipments,
            'projects' => Project::select('id', 'name')->get(),
            'stats' => $stats,
            'filters' => $request->only(['type', 'status', 'ownership', 'project_id', 'search'])
        ]);
    }

    public function create()
    {
        // Generate next equipment code
        $lastEquipment = Equipment::orderBy('id', 'desc')->first();
        $nextNumber = $lastEquipment ? (intval(substr($lastEquipment->code, 4)) + 1) : 1;
        $suggestedCode = 'EKP-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return Inertia::render('Equipments/Create', [
            'projects' => Project::select('id', 'name')->get(),
            'suggestedCode' => $suggestedCode,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:equipments,code|max:255',
            'name' => 'required|string|max:255',
            'type' => 'required|in:excavator,bulldozer,crane,loader,grader,roller,forklift,concrete_mixer,pump,generator,compressor,welding_machine,scaffolding,vehicle,tower_crane,mobile_crane,other',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'manufacture_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'specifications' => 'nullable|array',
            'description' => 'nullable|string',
            'ownership' => 'required|in:owned,rented,leased',
            'rental_company' => 'nullable|string|max:255',
            'rental_cost_daily' => 'nullable|numeric|min:0',
            'rental_cost_monthly' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'status' => 'required|in:available,in_use,maintenance,repair,out_of_service,retired',
            'current_project_id' => 'nullable|exists:projects,id',
            'current_location' => 'nullable|string|max:255',
            'insured' => 'boolean',
            'insurance_company' => 'nullable|string|max:255',
            'insurance_policy_number' => 'nullable|string|max:255',
            'insurance_expiry_date' => 'nullable|date',
            'maintenance_interval_days' => 'nullable|integer|min:1',
            'documents' => 'nullable|array',
            'certifications' => 'nullable|array',
            'requires_operator_license' => 'boolean',
            'required_license_type' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $validated['created_by'] = Auth::id();

            // Calculate next maintenance date if interval is provided
            if (isset($validated['maintenance_interval_days'])) {
                $validated['next_maintenance_date'] = now()->addDays($validated['maintenance_interval_days']);
            }

            Equipment::create($validated);
        });

        return redirect()->route('equipments.index')
            ->with('success', 'Ekipman başarıyla oluşturuldu.');
    }

    public function show(Equipment $equipment)
    {
        $equipment->load(['currentProject', 'createdBy', 'usages.project', 'maintenances']);

        // Recent usages
        $recentUsages = $equipment->usages()
            ->with(['project', 'operator'])
            ->orderBy('start_date', 'desc')
            ->limit(10)
            ->get();

        // Recent maintenance
        $recentMaintenance = $equipment->maintenances()
            ->orderBy('maintenance_date', 'desc')
            ->limit(10)
            ->get();

        // Cost summary
        $costSummary = [
            'total_maintenance_cost' => $equipment->calculateTotalMaintenanceCost(),
            'total_usage_cost' => $equipment->calculateTotalUsageCost(),
            'total_operating_cost' => $equipment->getTotalOperatingCost(),
        ];

        return Inertia::render('Equipments/Show', [
            'equipment' => $equipment,
            'recentUsages' => $recentUsages,
            'recentMaintenance' => $recentMaintenance,
            'costSummary' => $costSummary,
        ]);
    }

    public function edit(Equipment $equipment)
    {
        $equipment->load(['currentProject']);

        return Inertia::render('Equipments/Edit', [
            'equipment' => $equipment,
            'projects' => Project::select('id', 'name')->get(),
        ]);
    }

    public function update(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:equipments,code,' . $equipment->id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:excavator,bulldozer,crane,loader,grader,roller,forklift,concrete_mixer,pump,generator,compressor,welding_machine,scaffolding,vehicle,tower_crane,mobile_crane,other',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'manufacture_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'specifications' => 'nullable|array',
            'description' => 'nullable|string',
            'ownership' => 'required|in:owned,rented,leased',
            'rental_company' => 'nullable|string|max:255',
            'rental_cost_daily' => 'nullable|numeric|min:0',
            'rental_cost_monthly' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'status' => 'required|in:available,in_use,maintenance,repair,out_of_service,retired',
            'current_project_id' => 'nullable|exists:projects,id',
            'current_location' => 'nullable|string|max:255',
            'insured' => 'boolean',
            'insurance_company' => 'nullable|string|max:255',
            'insurance_policy_number' => 'nullable|string|max:255',
            'insurance_expiry_date' => 'nullable|date',
            'last_maintenance_date' => 'nullable|date',
            'next_maintenance_date' => 'nullable|date',
            'maintenance_interval_days' => 'nullable|integer|min:1',
            'documents' => 'nullable|array',
            'certifications' => 'nullable|array',
            'requires_operator_license' => 'boolean',
            'required_license_type' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($equipment, $validated) {
            $equipment->update($validated);
        });

        return redirect()->route('equipments.index')
            ->with('success', 'Ekipman başarıyla güncellendi.');
    }

    public function destroy(Equipment $equipment)
    {
        // Check if equipment has active usages
        if ($equipment->usages()->where('status', 'ongoing')->exists()) {
            return redirect()->route('equipments.index')
                ->with('error', 'Aktif kullanımı olan ekipman silinemez.');
        }

        $equipment->delete();

        return redirect()->route('equipments.index')
            ->with('success', 'Ekipman başarıyla silindi.');
    }
}
