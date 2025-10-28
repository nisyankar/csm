<?php

namespace App\Http\Controllers;

use App\Models\UnitSale;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeedTrackingController extends Controller
{
    /**
     * Display deed tracking dashboard
     */
    public function dashboard()
    {
        $stats = [
            'not_transferred' => UnitSale::where('deed_status', 'not_transferred')->count(),
            'in_process' => UnitSale::where('deed_status', 'in_process')->count(),
            'transferred' => UnitSale::where('deed_status', 'transferred')->count(),
            'postponed' => UnitSale::where('deed_status', 'postponed')->count(),
            'total' => UnitSale::whereNotNull('deed_status')->count(),
        ];

        // Proje bazlı istatistikler
        $projectStats = UnitSale::with('project:id,name,project_code')
            ->selectRaw('project_id,
                COUNT(*) as total_sales,
                SUM(CASE WHEN deed_status = "not_transferred" THEN 1 ELSE 0 END) as not_transferred,
                SUM(CASE WHEN deed_status = "in_process" THEN 1 ELSE 0 END) as in_process,
                SUM(CASE WHEN deed_status = "transferred" THEN 1 ELSE 0 END) as transferred,
                SUM(CASE WHEN deed_status = "postponed" THEN 1 ELSE 0 END) as postponed')
            ->groupBy('project_id')
            ->get()
            ->map(function ($item) {
                $transferredPercentage = $item->total_sales > 0
                    ? round(($item->transferred / $item->total_sales) * 100, 1)
                    : 0;

                return [
                    'project' => $item->project,
                    'total_sales' => $item->total_sales,
                    'not_transferred' => $item->not_transferred,
                    'in_process' => $item->in_process,
                    'transferred' => $item->transferred,
                    'postponed' => $item->postponed,
                    'transferred_percentage' => $transferredPercentage,
                ];
            });

        // Son devir işlemleri
        $recentTransfers = UnitSale::with(['project:id,name,project_code', 'customer:id,first_name,last_name,company_name', 'projectUnit:id,unit_number'])
            ->where('deed_status', 'transferred')
            ->whereNotNull('deed_transfer_date')
            ->orderBy('deed_transfer_date', 'desc')
            ->take(10)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'sale_number' => $sale->sale_number,
                    'project' => $sale->project,
                    'customer' => $sale->customer,
                    'unit_number' => $sale->projectUnit?->unit_number,
                    'deed_transfer_date' => $sale->deed_transfer_date,
                    'title_deed_number' => $sale->title_deed_number,
                    'deed_type' => $sale->deed_type,
                ];
            });

        // İşlemdeki tapular (in_process)
        $inProcessDeeds = UnitSale::with(['project:id,name,project_code', 'customer:id,first_name,last_name,company_name', 'projectUnit:id,unit_number'])
            ->where('deed_status', 'in_process')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'sale_number' => $sale->sale_number,
                    'project' => $sale->project,
                    'customer' => $sale->customer,
                    'unit_number' => $sale->projectUnit?->unit_number,
                    'final_price' => $sale->final_price,
                    'currency' => $sale->currency,
                    'updated_at' => $sale->updated_at,
                ];
            });

        return Inertia::render('Sales/DeedTracking/Dashboard', [
            'stats' => $stats,
            'projectStats' => $projectStats,
            'recentTransfers' => $recentTransfers,
            'inProcessDeeds' => $inProcessDeeds,
        ]);
    }

    /**
     * Display deed tracking list
     */
    public function index(Request $request)
    {
        $query = UnitSale::with([
            'project:id,name,project_code',
            'customer:id,first_name,last_name,company_name,customer_type',
            'projectUnit:id,unit_number,unit_type,gross_area'
        ]);

        // Filters
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('deed_status')) {
            $query->where('deed_status', $request->deed_status);
        }

        if ($request->filled('deed_type')) {
            $query->where('deed_type', 'like', '%' . $request->deed_type . '%');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sale_number', 'like', "%{$search}%")
                    ->orWhere('title_deed_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");
                    });
            });
        }

        // Date range filters
        if ($request->filled('transfer_date_from')) {
            $query->where('deed_transfer_date', '>=', $request->transfer_date_from);
        }

        if ($request->filled('transfer_date_to')) {
            $query->where('deed_transfer_date', '<=', $request->transfer_date_to);
        }

        // Sort
        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $unitSales = $query->paginate(15)->withQueryString();

        // Transform data
        $unitSales->getCollection()->transform(function ($sale) {
            return [
                'id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'project' => $sale->project,
                'customer' => $sale->customer,
                'customer_full_name' => $sale->customer?->full_name,
                'unit' => $sale->projectUnit,
                'deed_status' => $sale->deed_status,
                'deed_status_badge' => $sale->deed_status_badge,
                'deed_type' => $sale->deed_type,
                'title_deed_number' => $sale->title_deed_number,
                'deed_transfer_date' => $sale->deed_transfer_date,
                'final_price' => $sale->final_price,
                'currency' => $sale->currency,
                'deed_documents_count' => is_array($sale->deed_documents) ? count($sale->deed_documents) : 0,
                'has_deed_notes' => !empty($sale->deed_notes),
                'created_at' => $sale->created_at,
            ];
        });

        // Projects for filter dropdown
        $projects = Project::select('id', 'name', 'project_code')
            ->orderBy('name')
            ->get();

        return Inertia::render('Sales/DeedTracking/Index', [
            'unitSales' => $unitSales,
            'projects' => $projects,
            'filters' => $request->only(['project_id', 'deed_status', 'deed_type', 'search', 'transfer_date_from', 'transfer_date_to', 'sort_by', 'sort_direction']),
        ]);
    }
}
