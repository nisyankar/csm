<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectStructure;
use App\Models\ProjectFloor;
use App\Models\ProjectUnit;
use App\Models\UnitSale;
use Inertia\Inertia;

class SalesStatusController extends Controller
{
    /**
     * Proje satış durumu görselleştirme ana sayfası
     */
    public function show(Project $project)
    {
        // Proje bilgileri
        $projectData = $project->load([
            'structures' => function ($query) {
                $query->with(['floors.units']);
            }
        ]);

        // Proje için genel istatistikler
        $stats = $this->getProjectSalesStats($project);

        return Inertia::render('Sales/SalesStatus/Show', [
            'project' => $projectData,
            'stats' => $stats,
        ]);
    }

    /**
     * Proje satış istatistiklerini getir
     */
    private function getProjectSalesStats(Project $project)
    {
        $totalUnits = ProjectUnit::whereHas('floor.structure', function ($query) use ($project) {
            $query->where('project_id', $project->id);
        })->count();

        // Gerçek satış kayıtlarından sold units hesapla
        $soldUnits = UnitSale::where('project_id', $project->id)
            ->whereIn('status', ['contracted', 'in_payment', 'completed'])
            ->distinct('project_unit_id')
            ->count('project_unit_id');

        $reservedUnits = UnitSale::where('project_id', $project->id)
            ->where('status', 'reserved')
            ->count();

        $availableUnits = $totalUnits - $soldUnits - $reservedUnits;

        $salesPercentage = $totalUnits > 0 ? round(($soldUnits / $totalUnits) * 100, 2) : 0;

        // Satış tutarı toplamları
        $totalSalesAmount = UnitSale::where('project_id', $project->id)
            ->whereNotIn('status', ['cancelled'])
            ->sum('final_price');

        $totalPaidAmount = UnitSale::where('project_id', $project->id)
            ->whereNotIn('status', ['cancelled'])
            ->get()
            ->sum(function ($sale) {
                return $sale->getTotalPaid();
            });

        return [
            'total_units' => $totalUnits,
            'sold_units' => $soldUnits,
            'reserved_units' => $reservedUnits,
            'available_units' => $availableUnits,
            'sales_percentage' => $salesPercentage,
            'total_sales_amount' => $totalSalesAmount,
            'total_paid_amount' => $totalPaidAmount,
        ];
    }

    /**
     * Blok detaylarını getir
     */
    public function getStructureDetails(ProjectStructure $structure)
    {
        $floors = $structure->floors()
            ->with(['units' => function ($query) {
                $query->with('unitSale');
            }])
            ->orderBy('floor_number', 'desc')
            ->get()
            ->map(function ($floor) {
                $totalUnits = $floor->units->count();

                // Gerçek satış kayıtlarına bakarak sat sold_units hesapla
                $soldUnits = $floor->units->filter(function ($unit) {
                    $sale = UnitSale::where('project_unit_id', $unit->id)
                        ->whereNotIn('status', ['cancelled'])
                        ->whereIn('status', ['contracted', 'in_payment', 'completed'])
                        ->first();
                    return $sale !== null;
                })->count();

                return [
                    'id' => $floor->id,
                    'name' => $floor->name,
                    'floor_number' => $floor->floor_number,
                    'total_units' => $totalUnits,
                    'sold_units' => $soldUnits,
                    'available_units' => $totalUnits - $soldUnits,
                    'sales_percentage' => $totalUnits > 0 ? round(($soldUnits / $totalUnits) * 100, 2) : 0,
                ];
            });

        return response()->json([
            'structure' => $structure,
            'floors' => $floors,
        ]);
    }

    /**
     * Kat detaylarını ve daireleri getir
     */
    public function getFloorUnits(ProjectFloor $floor)
    {
        $units = $floor->units()
            ->with(['unitSale' => function ($query) {
                $query->with('customer');
            }])
            ->get()
            ->map(function ($unit) {
                // Satış bilgisi
                $sale = UnitSale::where('project_unit_id', $unit->id)
                    ->whereNotIn('status', ['cancelled'])
                    ->first();

                $status = 'available'; // Müsait
                $statusColor = 'green';
                $customerName = null;
                $saleInfo = null;

                if ($sale) {
                    if ($sale->status === 'reserved') {
                        $status = 'reserved';
                        $statusColor = 'yellow';
                    } elseif (in_array($sale->status, ['contracted', 'in_payment', 'completed'])) {
                        $status = 'sold';
                        $statusColor = 'red';
                    } elseif ($sale->status === 'delayed') {
                        $status = 'delayed';
                        $statusColor = 'orange';
                    }

                    $customerName = $sale->customer?->full_name;
                    $saleInfo = [
                        'id' => $sale->id,
                        'sale_number' => $sale->sale_number,
                        'status' => $sale->status,
                        'status_badge' => $sale->status_badge,
                        'final_price' => $sale->final_price,
                        'currency' => $sale->currency,
                        'payment_completion' => $sale->payment_completion_percentage,
                    ];
                }

                return [
                    'id' => $unit->id,
                    'unit_code' => $unit->unit_code,
                    'unit_type' => $unit->unit_type,
                    'room_configuration' => $unit->room_configuration,
                    'gross_area' => $unit->gross_area,
                    'net_area' => $unit->net_area,
                    'status' => $status,
                    'status_color' => $statusColor,
                    'customer_name' => $customerName,
                    'sale_info' => $saleInfo,
                ];
            });

        return response()->json([
            'floor' => [
                'id' => $floor->id,
                'name' => $floor->name,
                'floor_number' => $floor->floor_number,
            ],
            'units' => $units,
        ]);
    }

    /**
     * Proje listesi - Satış durumu sayfası için
     */
    public function index()
    {
        $projects = Project::select('id', 'name', 'project_code', 'status')
            ->whereIn('status', ['active', 'planning'])
            ->get()
            ->map(function ($project) {
                $stats = $this->getProjectSalesStats($project);

                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'code' => $project->project_code,
                    'status' => $project->status,
                    'stats' => $stats,
                ];
            });

        return Inertia::render('Sales/SalesStatus/Index', [
            'projects' => $projects,
        ]);
    }
}
