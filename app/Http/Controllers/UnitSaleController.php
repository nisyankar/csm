<?php

namespace App\Http\Controllers;

use App\Models\UnitSale;
use App\Models\Project;
use App\Models\Customer;
use App\Models\ProjectUnit;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
        // TODO: Implement store logic
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
        // TODO: Implement update logic
    }

    public function destroy(UnitSale $unitSale)
    {
        // TODO: Implement destroy logic
    }
}
