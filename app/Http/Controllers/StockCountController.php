<?php

namespace App\Http\Controllers;

use App\Models\StockCount;
use App\Models\Warehouse;
use App\Models\Material;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class StockCountController extends Controller
{
    public function index(Request $request)
    {
        $query = StockCount::with(['warehouse', 'material', 'countedBy', 'approvedBy'])
            ->latest('count_date');

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('material', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                  })
                  ->orWhereHas('warehouse', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('warehouse')) {
            $query->where('warehouse_id', $request->warehouse);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('difference_type')) {
            if ($request->difference_type === 'surplus') {
                $query->where('difference', '>', 0);
            } elseif ($request->difference_type === 'shortage') {
                $query->where('difference', '<', 0);
            } elseif ($request->difference_type === 'match') {
                $query->where('difference', '=', 0);
            }
        }

        $counts = $query->paginate(15)->withQueryString();

        // Statistics
        $statistics = [
            'total_counts' => StockCount::count(),
            'pending_counts' => StockCount::pending()->count(),
            'approved_counts' => StockCount::approved()->count(),
            'total_differences' => StockCount::whereNotNull('difference')->sum(DB::raw('ABS(difference)')),
            'surplus_count' => StockCount::where('difference', '>', 0)->count(),
            'shortage_count' => StockCount::where('difference', '<', 0)->count(),
        ];

        return Inertia::render('StockCounts/Index', [
            'counts' => $counts,
            'statistics' => $statistics,
            'warehouses' => Warehouse::active()->get(['id', 'name']),
            'filters' => $request->only(['search', 'warehouse', 'status', 'difference_type']),
        ]);
    }

    public function create()
    {
        $warehouses = Warehouse::active()
            ->with('project')
            ->get();

        $materials = Material::all(['id', 'name', 'code', 'unit']);

        return Inertia::render('StockCounts/Create', [
            'warehouses' => $warehouses,
            'materials' => $materials,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'material_id' => 'required|exists:materials,id',
            'counted_quantity' => 'required|numeric|min:0',
            'count_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Get system quantity
        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);
        $systemQuantity = $warehouse->getStockByMaterial($validated['material_id']);

        // Calculate difference
        $difference = $validated['counted_quantity'] - $systemQuantity;

        // Generate reference number
        $referenceNumber = StockCount::generateReferenceNumber();

        $count = StockCount::create([
            'reference_number' => $referenceNumber,
            'warehouse_id' => $validated['warehouse_id'],
            'material_id' => $validated['material_id'],
            'system_quantity' => $systemQuantity,
            'counted_quantity' => $validated['counted_quantity'],
            'difference' => $difference,
            'count_date' => $validated['count_date'],
            'counted_by' => auth()->id(),
            'notes' => $validated['notes'],
            'status' => 'pending',
        ]);

        return redirect()->route('stock-counts.index')
            ->with('success', 'Sayım kaydı başarıyla oluşturuldu.');
    }

    public function show(StockCount $stockCount)
    {
        $stockCount->load(['warehouse', 'material', 'countedBy', 'approvedBy']);

        return Inertia::render('StockCounts/Show', [
            'count' => $stockCount,
        ]);
    }

    public function approve(StockCount $stockCount)
    {
        if ($stockCount->status !== 'pending') {
            return back()->withErrors(['error' => 'Sadece beklemedeki sayımlar onaylanabilir.']);
        }

        DB::transaction(function () use ($stockCount) {
            $stockCount->approve(auth()->id());

            // If there's a difference, create adjustment movement
            if ($stockCount->difference != 0) {
                StockMovement::create([
                    'warehouse_id' => $stockCount->warehouse_id,
                    'material_id' => $stockCount->material_id,
                    'movement_type' => 'adjustment',
                    'quantity' => $stockCount->difference,
                    'movement_date' => now(),
                    'performed_by' => auth()->id(),
                    'notes' => "Sayım düzeltmesi - Referans: {$stockCount->reference_number}",
                    'reference_type' => StockCount::class,
                    'reference_id' => $stockCount->id,
                ]);
            }
        });

        return redirect()->route('stock-counts.index')
            ->with('success', 'Sayım onaylandı ve stok düzeltmesi yapıldı.');
    }

    public function reject(Request $request, StockCount $stockCount)
    {
        if ($stockCount->status !== 'pending') {
            return back()->withErrors(['error' => 'Sadece beklemedeki sayımlar reddedilebilir.']);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $stockCount->reject(auth()->id(), $validated['rejection_reason']);

        return redirect()->route('stock-counts.index')
            ->with('success', 'Sayım reddedildi.');
    }

    public function getSystemStock(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'material_id' => 'required|exists:materials,id',
        ]);

        $warehouse = Warehouse::findOrFail($request->warehouse_id);
        $systemStock = $warehouse->getStockByMaterial($request->material_id);

        return response()->json([
            'system_stock' => $systemStock,
        ]);
    }
}
