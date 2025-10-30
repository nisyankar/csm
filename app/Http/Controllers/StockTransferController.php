<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\Material;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::with(['warehouse', 'toWarehouse', 'material', 'performedBy'])
            ->transfers()
            ->latest('movement_date');

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('material', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                })
                ->orWhereHas('warehouse', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('toWarehouse', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('from_warehouse')) {
            $query->where('warehouse_id', $request->from_warehouse);
        }

        if ($request->filled('to_warehouse')) {
            $query->where('to_warehouse_id', $request->to_warehouse);
        }

        if ($request->filled('material')) {
            $query->where('material_id', $request->material);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('movement_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('movement_date', '<=', $request->date_to);
        }

        $transfers = $query->paginate(15)->withQueryString();

        // Statistics
        $statistics = [
            'total_transfers' => StockMovement::transfers()->count(),
            'today_transfers' => StockMovement::transfers()->whereDate('movement_date', today())->count(),
            'this_month_transfers' => StockMovement::transfers()->whereMonth('movement_date', now()->month)->count(),
            'total_quantity_transferred' => StockMovement::transfers()->sum('quantity'),
        ];

        return Inertia::render('StockTransfers/Index', [
            'transfers' => $transfers,
            'statistics' => $statistics,
            'warehouses' => Warehouse::active()->get(['id', 'name']),
            'materials' => Material::all(['id', 'name', 'code']),
            'filters' => $request->only(['search', 'from_warehouse', 'to_warehouse', 'material', 'date_from', 'date_to']),
        ]);
    }

    public function create()
    {
        $warehouses = Warehouse::active()
            ->with('project')
            ->get();

        $materials = Material::all(['id', 'name', 'code', 'unit']);

        return Inertia::render('StockTransfers/Create', [
            'warehouses' => $warehouses,
            'materials' => $materials,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'material_id' => 'required|exists:materials,id',
            'quantity' => 'required|numeric|min:0.01',
            'movement_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ], [
            'to_warehouse_id.different' => 'Hedef depo, kaynak depodan farklı olmalıdır.',
        ]);

        // Check if source warehouse has enough stock
        $fromWarehouse = Warehouse::findOrFail($validated['from_warehouse_id']);
        $currentStock = $fromWarehouse->getStockByMaterial($validated['material_id']);

        if ($currentStock < $validated['quantity']) {
            return back()->withErrors([
                'quantity' => "Kaynak depoda yeterli stok bulunmamaktadır. Mevcut stok: {$currentStock}"
            ]);
        }

        // Create outbound movement
        StockMovement::create([
            'warehouse_id' => $validated['from_warehouse_id'],
            'to_warehouse_id' => $validated['to_warehouse_id'],
            'material_id' => $validated['material_id'],
            'movement_type' => 'transfer',
            'quantity' => $validated['quantity'],
            'movement_date' => $validated['movement_date'],
            'performed_by' => auth()->id(),
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('stock-transfers.index')
            ->with('success', 'Transfer başarıyla kaydedildi.');
    }

    public function show(StockMovement $stockTransfer)
    {
        $stockTransfer->load(['warehouse', 'toWarehouse', 'material', 'performedBy']);

        return Inertia::render('StockTransfers/Show', [
            'transfer' => $stockTransfer,
        ]);
    }

    public function getWarehouseStock(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'material_id' => 'required|exists:materials,id',
        ]);

        $warehouse = Warehouse::findOrFail($request->warehouse_id);
        $stock = $warehouse->getStockByMaterial($request->material_id);

        return response()->json([
            'stock' => $stock,
        ]);
    }
}
