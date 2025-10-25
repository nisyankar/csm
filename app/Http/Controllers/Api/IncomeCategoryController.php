<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncomeCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class IncomeCategoryController extends Controller
{
    /**
     * Gelir kategorileri listesi (hiyerarşik)
     */
    public function index(Request $request): JsonResponse
    {
        $query = IncomeCategory::with('children')->active();

        // Sadece ana kategoriler
        if ($request->boolean('root_only')) {
            $query->root();
        }

        // Arama
        if ($search = $request->query('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $categories = $query->orderBy('name')->get();

        return response()->json(['success' => true, 'data' => $categories]);
    }

    /**
     * Tüm kategoriler (ağaç yapısı)
     */
    public function tree(): JsonResponse
    {
        $categories = IncomeCategory::with('children')
            ->active()
            ->root()
            ->orderBy('name')
            ->get();

        return response()->json(['success' => true, 'data' => $categories]);
    }

    /**
     * Detay
     */
    public function show(IncomeCategory $incomeCategory): JsonResponse
    {
        $incomeCategory->load(['parent', 'children', 'transactions']);

        return response()->json(['success' => true, 'data' => $incomeCategory]);
    }
}
