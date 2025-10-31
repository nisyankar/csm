<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MaterialResource;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = Material::query();

        // Search
        if ($search = $request->query('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('material_code', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        if ($request->has('is_active')) {
            $isActive = $request->query('is_active');
            $query->where('is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'name');
        $sortOrder = $request->query('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->query('per_page', 15);
        $materials = $query->paginate($perPage);

        return MaterialResource::collection($materials)->additional([
            'success' => true,
        ]);
    }

    public function store(Request $request)
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

        $material = Material::create($validated);

        return (new MaterialResource($material))->additional([
            'success' => true,
            'message' => 'Malzeme başarıyla oluşturuldu.',
        ])->response()->setStatusCode(201);
    }

    public function show(Material $material)
    {
        $material->load(['purchasingItems.purchasingRequest']);

        return (new MaterialResource($material))->additional([
            'success' => true,
        ]);
    }

    public function update(Request $request, Material $material)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'category' => 'sometimes|string|max:255',
            'unit' => 'sometimes|string|max:50',
            'estimated_unit_price' => 'sometimes|numeric|min:0',
            'specification' => 'nullable|string',
            'material_code' => 'nullable|string|max:100|unique:materials,material_code,' . $material->id,
            'is_active' => 'boolean',
        ]);

        $material->update($validated);

        return (new MaterialResource($material->fresh()))->additional([
            'success' => true,
            'message' => 'Malzeme başarıyla güncellendi.',
        ]);
    }

    public function destroy(Material $material): JsonResponse
    {
        // Check if material is in use
        if ($material->purchasingItems()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Bu malzeme satınalma taleplerinde kullanıldığı için silinemez.',
            ], 422);
        }

        $material->delete();

        return response()->json([
            'success' => true,
            'message' => 'Malzeme başarıyla silindi.',
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = Material::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return response()->json(['success' => true, 'data' => $categories]);
    }

    public function active(Request $request)
    {
        $query = Material::active();

        // Category filter
        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        // Search
        if ($search = $request->query('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('material_code', 'like', "%{$search}%");
            });
        }

        $materials = $query->orderBy('name')->get();

        return MaterialResource::collection($materials)->additional([
            'success' => true,
        ]);
    }

    public function byCategory(Request $request, string $category)
    {
        $query = Material::where('category', $category);

        if ($request->boolean('active_only')) {
            $query->active();
        }

        $materials = $query->orderBy('name')->get();

        return MaterialResource::collection($materials)->additional([
            'success' => true,
        ]);
    }

    /**
     * Düşük stok seviyesindeki malzemeler (Mobile için)
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function lowStock(Request $request)
    {
        $query = Material::active()
            ->whereColumn('current_stock', '<', 'min_stock_level')
            ->orderBy('current_stock', 'asc');

        $materials = $query->get();

        return MaterialResource::collection($materials)->additional([
            'success' => true,
        ]);
    }
}
