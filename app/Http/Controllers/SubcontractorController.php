<?php

namespace App\Http\Controllers;

use App\Models\Subcontractor;
use App\Models\SubcontractorCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class SubcontractorController extends Controller
{
    /**
     * Taşeron listesi
     */
    public function index(Request $request)
    {
        // Sadece category ile başlayalım, certifications ve ratings ağır yükler
        $query = Subcontractor::with(['category']);

        // Arama
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Durum filtresi
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Kategori filtresi
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Şehir filtresi
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        // Onay durumu filtresi
        if ($request->filled('approval_status')) {
            if ($request->approval_status === 'approved') {
                $query->where('is_approved', true);
            } elseif ($request->approval_status === 'pending') {
                $query->where('is_approved', false);
            }
        }

        // Puan filtresi
        if ($request->filled('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }

        // Sıralama
        $sortField = $request->get('sort', 'company_name');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $subcontractors = $query->paginate(15)->withQueryString();

        // Kategoriler
        $categories = SubcontractorCategory::active()
            ->orderBy('name')
            ->get();

        // Şehirler
        $cities = Subcontractor::select('city')
            ->distinct()
            ->whereNotNull('city')
            ->orderBy('city')
            ->pluck('city');

        return Inertia::render('Subcontractors/Index', [
            'subcontractors' => $subcontractors,
            'categories' => $categories,
            'cities' => $cities,
            'filters' => $request->only([
                'search',
                'status',
                'category_id',
                'city',
                'approval_status',
                'min_rating',
                'sort',
                'direction'
            ]),
            'stats' => $this->getStats(),
        ]);
    }

    /**
     * Taşeron oluşturma formu
     */
    public function create()
    {
        $categories = SubcontractorCategory::active()
            ->orderBy('name')
            ->get();

        return Inertia::render('Subcontractors/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Yeni taşeron kaydet
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'trade_title' => 'nullable|string|max:255',
            'tax_office' => 'required|string|max:100',
            'tax_number' => 'required|string|max:20|unique:subcontractors,tax_number',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'authorized_person' => 'nullable|string|max:100',
            'authorized_phone' => 'nullable|string|max:20',
            'authorized_email' => 'nullable|email|max:255',
            'authorized_title' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'branch_name' => 'nullable|string|max:100',
            'branch_code' => 'nullable|string|max:10',
            'account_number' => 'nullable|string|max:50',
            'iban' => 'nullable|string|max:34',
            'category_id' => 'nullable|exists:subcontractor_categories,id',
            'status' => 'required|in:active,inactive,blacklisted',
            'is_approved' => 'boolean',
            'notes' => 'nullable|string',
            'tags' => 'nullable|array',
        ]);

        if ($validated['is_approved'] ?? false) {
            $validated['approval_date'] = now();
            $validated['approved_by'] = auth()->id();
        }

        $subcontractor = Subcontractor::create($validated);

        return redirect()
            ->route('subcontractors.show', $subcontractor)
            ->with('success', 'Taşeron başarıyla oluşturuldu.');
    }

    /**
     * Taşeron detayları
     */
    public function show(Subcontractor $subcontractor)
    {
        $subcontractor->load([
            'category',
            'certifications' => function ($query) {
                $query->orderBy('expiry_date', 'desc');
            },
            'ratings' => function ($query) {
                $query->with(['project', 'rater'])
                    ->orderBy('rated_at', 'desc');
            },
            'approver',
        ]);

        return Inertia::render('Subcontractors/Show', [
            'subcontractor' => $subcontractor,
            'certificationStats' => $this->getCertificationStats($subcontractor),
            'ratingStats' => $this->getRatingStats($subcontractor),
        ]);
    }

    /**
     * Taşeron düzenleme formu
     */
    public function edit(Subcontractor $subcontractor)
    {
        $categories = SubcontractorCategory::active()
            ->orderBy('name')
            ->get();

        $subcontractor->load(['category', 'certifications', 'ratings']);

        return Inertia::render('Subcontractors/Edit', [
            'subcontractor' => $subcontractor,
            'categories' => $categories,
        ]);
    }

    /**
     * Taşeron güncelle
     */
    public function update(Request $request, Subcontractor $subcontractor)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'trade_title' => 'nullable|string|max:255',
            'tax_office' => 'required|string|max:100',
            'tax_number' => 'required|string|max:20|unique:subcontractors,tax_number,' . $subcontractor->id,
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'authorized_person' => 'nullable|string|max:100',
            'authorized_phone' => 'nullable|string|max:20',
            'authorized_email' => 'nullable|email|max:255',
            'authorized_title' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'branch_name' => 'nullable|string|max:100',
            'branch_code' => 'nullable|string|max:10',
            'account_number' => 'nullable|string|max:50',
            'iban' => 'nullable|string|max:34',
            'category_id' => 'nullable|exists:subcontractor_categories,id',
            'status' => 'required|in:active,inactive,blacklisted',
            'is_approved' => 'boolean',
            'notes' => 'nullable|string',
            'tags' => 'nullable|array',
        ]);

        // Onay durumu değişiyorsa tarihi ve onaylayan kişiyi güncelle
        if (isset($validated['is_approved']) && $validated['is_approved'] !== $subcontractor->is_approved) {
            if ($validated['is_approved']) {
                $validated['approval_date'] = now();
                $validated['approved_by'] = auth()->id();
            } else {
                $validated['approval_date'] = null;
                $validated['approved_by'] = null;
            }
        }

        $subcontractor->update($validated);

        return redirect()
            ->route('subcontractors.show', $subcontractor)
            ->with('success', 'Taşeron başarıyla güncellendi.');
    }

    /**
     * Taşeron sil
     */
    public function destroy(Subcontractor $subcontractor)
    {
        // TODO: Eğer taşeronun aktif sözleşmesi varsa silmeyi engelle

        $subcontractor->delete();

        return redirect()
            ->route('subcontractors.index')
            ->with('success', 'Taşeron başarıyla silindi.');
    }

    /**
     * Genel istatistikler
     */
    private function getStats()
    {
        return [
            'total' => Subcontractor::count(),
            'active' => Subcontractor::active()->count(),
            'approved' => Subcontractor::approved()->count(),
            'blacklisted' => Subcontractor::blacklisted()->count(),
            'pending_approval' => Subcontractor::where('is_approved', false)->count(),
            'high_rated' => Subcontractor::where('rating', '>=', 4.0)->count(),
            'average_rating' => round(Subcontractor::avg('rating'), 2),
        ];
    }

    /**
     * Belgelendirme istatistikleri
     */
    private function getCertificationStats(Subcontractor $subcontractor)
    {
        $certifications = $subcontractor->certifications;

        return [
            'total' => $certifications->count(),
            'valid' => $certifications->where('status', 'valid')->count(),
            'expired' => $certifications->where('status', 'expired')->count(),
            'expiring_soon' => $subcontractor->expiringSoonCertifications()->count(),
        ];
    }

    /**
     * Değerlendirme istatistikleri
     */
    private function getRatingStats(Subcontractor $subcontractor)
    {
        $ratings = $subcontractor->ratings;

        if ($ratings->isEmpty()) {
            return [
                'count' => 0,
                'average' => 0,
                'quality_avg' => 0,
                'timeline_avg' => 0,
                'safety_avg' => 0,
                'communication_avg' => 0,
                'cost_avg' => 0,
                'would_rehire_percentage' => 0,
            ];
        }

        return [
            'count' => $ratings->count(),
            'average' => round($ratings->avg('overall_score'), 2),
            'quality_avg' => round($ratings->avg('quality_score'), 2),
            'timeline_avg' => round($ratings->avg('timeline_score'), 2),
            'safety_avg' => round($ratings->avg('safety_score'), 2),
            'communication_avg' => round($ratings->avg('communication_score'), 2),
            'cost_avg' => round($ratings->avg('cost_score'), 2),
            'would_rehire_percentage' => round(
                ($ratings->where('would_rehire', true)->count() / $ratings->count()) * 100,
                2
            ),
        ];
    }
}
