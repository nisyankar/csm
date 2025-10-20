<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class MaterialController extends Controller
{
    /**
     * Malzeme listesi
     */
    public function index(Request $request): Response
    {
        $query = Material::query();

        // Arama
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('material_code', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Kategori filtresi
        if ($request->has('category') && $request->category !== '') {
            $query->where('category', $request->category);
        }

        // Aktif/Pasif filtresi
        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active === 'true' || $request->is_active === '1');
        }

        // Sıralama
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $materials = $query->paginate(15)->withQueryString();

        // Benzersiz kategorileri al
        $categories = Material::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return Inertia::render('Materials/Index', [
            'materials' => $materials,
            'filters' => $request->only(['search', 'category', 'is_active']),
            'categories' => $categories,
        ]);
    }

    /**
     * Yeni malzeme oluşturma formu
     */
    public function create(): Response
    {
        // Mevcut kategorileri al
        $categories = Material::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return Inertia::render('Materials/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Yeni malzeme kaydet
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'estimated_unit_price' => 'required|numeric|min:0',
            'specification' => 'nullable|string',
            'material_code' => 'nullable|string|max:100|unique:materials,material_code',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Material::create($validated);

        return redirect()->route('materials.index')
            ->with('success', 'Malzeme başarıyla oluşturuldu.');
    }

    /**
     * Malzeme detayı göster
     */
    public function show(Material $material): Response
    {
        $material->load(['purchasingItems.purchasingRequest']);

        return Inertia::render('Materials/Show', [
            'material' => $material,
        ]);
    }

    /**
     * Malzeme düzenleme formu
     */
    public function edit(Material $material): Response
    {
        // Mevcut kategorileri al
        $categories = Material::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return Inertia::render('Materials/Edit', [
            'material' => $material,
            'categories' => $categories,
        ]);
    }

    /**
     * Malzeme güncelle
     */
    public function update(Request $request, Material $material): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'estimated_unit_price' => 'required|numeric|min:0',
            'specification' => 'nullable|string',
            'material_code' => 'nullable|string|max:100|unique:materials,material_code,' . $material->id,
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $material->update($validated);

        return redirect()->route('materials.index')
            ->with('success', 'Malzeme başarıyla güncellendi.');
    }

    /**
     * Malzeme sil
     */
    public function destroy(Material $material): RedirectResponse
    {
        // Eğer malzeme kullanılıyorsa silmeyi engelle
        if ($material->purchasingItems()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Bu malzeme satınalma taleplerinde kullanıldığı için silinemez.');
        }

        $material->delete();

        return redirect()->route('materials.index')
            ->with('success', 'Malzeme başarıyla silindi.');
    }

    /**
     * Aktif malzemeleri JSON olarak döndür (API)
     */
    public function getActive(Request $request)
    {
        $query = Material::active();

        // Kategori filtresi
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Arama
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('material_code', 'like', "%{$search}%");
            });
        }

        $materials = $query->orderBy('name')->get();

        return response()->json($materials);
    }
}
