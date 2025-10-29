<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\Material;
use App\Services\Financial\FinancialTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockMovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = StockMovement::with(['warehouse', 'material', 'performedBy']);

        // Arama filtresi
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('material', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Depo filtresi
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Malzeme filtresi
        if ($request->filled('material_id')) {
            $query->where('material_id', $request->material_id);
        }

        // Hareket tipi filtresi
        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        // Tarih aralığı filtresi
        if ($request->filled('date_from')) {
            $query->where('movement_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('movement_date', '<=', $request->date_to);
        }

        $movements = $query->latest('movement_date')->paginate(20)->withQueryString();

        return Inertia::render('StockMovements/Index', [
            'movements' => $movements,
            'warehouses' => Warehouse::where('is_active', true)->select('id', 'name')->get(),
            'materials' => Material::where('is_active', true)->select('id', 'name', 'unit')->get(),
            'filters' => $request->only(['search', 'warehouse_id', 'material_id', 'movement_type', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('StockMovements/Create', [
            'warehouses' => Warehouse::where('is_active', true)->select('id', 'name')->get(),
            'materials' => Material::where('is_active', true)->select('id', 'name', 'unit', 'current_stock')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, FinancialTransactionService $financialService)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'material_id' => 'required|exists:materials,id',
            'movement_type' => 'required|in:in,out,transfer,adjustment',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'movement_date' => 'required|date',
        ]);

        $movement = DB::transaction(function () use ($validated, $financialService) {
            // Stok hareketini kaydet
            $movement = StockMovement::create([
                ...$validated,
                'performed_by' => auth()->check() ? auth()->id() : 1,
            ]);

            // Malzeme stok miktarını güncelle
            $material = Material::find($validated['material_id']);

            if ($validated['movement_type'] === 'in' || $validated['movement_type'] === 'adjustment') {
                $material->increment('current_stock', $validated['quantity']);
            } elseif ($validated['movement_type'] === 'out') {
                if ($material->current_stock < $validated['quantity']) {
                    throw new \Exception("Yetersiz stok! Mevcut stok: {$material->current_stock}");
                }
                $material->decrement('current_stock', $validated['quantity']);
            }

            // Stok çıkışı için finansal kayıt oluştur
            $movement->load(['warehouse', 'material']);
            $financialService->createFromStockMovement($movement);

            return $movement;
        });

        return redirect()->route('stock-movements.index')
            ->with('success', 'Stok hareketi başarıyla oluşturuldu.');
    }

    /**
     * Display the specified resource.
     */
    public function show(StockMovement $stockMovement)
    {
        $stockMovement->load(['warehouse', 'material', 'performedBy']);

        return Inertia::render('StockMovements/Show', [
            'movement' => $stockMovement,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockMovement $stockMovement)
    {
        return Inertia::render('StockMovements/Edit', [
            'movement' => $stockMovement,
            'warehouses' => Warehouse::where('is_active', true)->select('id', 'name')->get(),
            'materials' => Material::where('is_active', true)->select('id', 'name', 'unit', 'current_stock')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockMovement $stockMovement, FinancialTransactionService $financialService)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'material_id' => 'required|exists:materials,id',
            'movement_type' => 'required|in:in,out,transfer,adjustment',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'movement_date' => 'required|date',
        ]);

        DB::transaction(function () use ($validated, $stockMovement, $financialService) {
            $material = Material::find($stockMovement->material_id);

            // Eski hareketi geri al
            if ($stockMovement->movement_type === 'in' || $stockMovement->movement_type === 'adjustment') {
                $material->decrement('current_stock', (float)$stockMovement->quantity);
            } elseif ($stockMovement->movement_type === 'out') {
                $material->increment('current_stock', (float)$stockMovement->quantity);
            }

            // Yeni hareketi uygula
            $newMaterial = Material::find($validated['material_id']);
            if ($validated['movement_type'] === 'in' || $validated['movement_type'] === 'adjustment') {
                $newMaterial->increment('current_stock', $validated['quantity']);
            } elseif ($validated['movement_type'] === 'out') {
                if ($newMaterial->current_stock < $validated['quantity']) {
                    throw new \Exception("Yetersiz stok! Mevcut stok: {$newMaterial->current_stock}");
                }
                $newMaterial->decrement('current_stock', $validated['quantity']);
            }

            // Hareketi güncelle
            $stockMovement->update($validated);

            // Finansal kayıt güncelle (eski sil, yeni oluştur)
            $stockMovement->load(['warehouse', 'material']);
            $financialService->createFromStockMovement($stockMovement);
        });

        return redirect()->route('stock-movements.index')
            ->with('success', 'Stok hareketi başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockMovement $stockMovement)
    {
        DB::transaction(function () use ($stockMovement) {
            $material = Material::find($stockMovement->material_id);

            // Hareketi geri al
            if ($stockMovement->movement_type === 'in' || $stockMovement->movement_type === 'adjustment') {
                $material->decrement('current_stock', (float)$stockMovement->quantity);
            } elseif ($stockMovement->movement_type === 'out') {
                $material->increment('current_stock', (float)$stockMovement->quantity);
            }

            // İlişkili finansal kaydı sil (eğer varsa)
            \App\Models\FinancialTransaction::fromSource('stock_movement', $stockMovement->id)->delete();

            $stockMovement->delete();
        });

        return redirect()->route('stock-movements.index')
            ->with('success', 'Stok hareketi başarıyla silindi.');
    }
}
