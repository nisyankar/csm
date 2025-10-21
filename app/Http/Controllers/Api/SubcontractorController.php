<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subcontractor;
use App\Models\SubcontractorCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SubcontractorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Subcontractor::with(['category']);

        // Search
        if ($search = $request->query('search')) {
            $query->search($search);
        }

        // Filters
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($city = $request->query('city')) {
            $query->where('city', $city);
        }

        if ($approvalStatus = $request->query('approval_status')) {
            if ($approvalStatus === 'approved') {
                $query->where('is_approved', true);
            } elseif ($approvalStatus === 'pending') {
                $query->where('is_approved', false);
            }
        }

        if ($minRating = $request->query('min_rating')) {
            $query->where('rating', '>=', $minRating);
        }

        // Sorting
        $sortField = $request->query('sort', 'company_name');
        $sortDirection = $request->query('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->query('per_page', 15);
        $subcontractors = $query->paginate($perPage);

        return response()->json(['success' => true, 'data' => $subcontractors]);
    }

    public function store(Request $request): JsonResponse
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
            $validated['approved_by'] = Auth::id();
        }

        $subcontractor = Subcontractor::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Taşeron başarıyla oluşturuldu.',
            'data' => $subcontractor->load('category'),
        ], 201);
    }

    public function show(Subcontractor $subcontractor): JsonResponse
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

        $stats = [
            'certification_stats' => $this->getCertificationStats($subcontractor),
            'rating_stats' => $this->getRatingStats($subcontractor),
        ];

        return response()->json([
            'success' => true,
            'data' => $subcontractor,
            'stats' => $stats,
        ]);
    }

    public function update(Request $request, Subcontractor $subcontractor): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'sometimes|string|max:255',
            'trade_title' => 'nullable|string|max:255',
            'tax_office' => 'sometimes|string|max:100',
            'tax_number' => 'sometimes|string|max:20|unique:subcontractors,tax_number,' . $subcontractor->id,
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
            'status' => 'sometimes|in:active,inactive,blacklisted',
            'is_approved' => 'boolean',
            'notes' => 'nullable|string',
            'tags' => 'nullable|array',
        ]);

        // Update approval status if changed
        if (isset($validated['is_approved']) && $validated['is_approved'] !== $subcontractor->is_approved) {
            if ($validated['is_approved']) {
                $validated['approval_date'] = now();
                $validated['approved_by'] = Auth::id();
            } else {
                $validated['approval_date'] = null;
                $validated['approved_by'] = null;
            }
        }

        $subcontractor->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Taşeron başarıyla güncellendi.',
            'data' => $subcontractor->load('category'),
        ]);
    }

    public function destroy(Subcontractor $subcontractor): JsonResponse
    {
        // Check for active contracts
        $activeProjects = $subcontractor->projects()
            ->wherePivot('status', 'active')
            ->count();

        if ($activeProjects > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Bu taşeronun aktif projeleri bulunduğu için silinemez.',
            ], 422);
        }

        $subcontractor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Taşeron başarıyla silindi.',
        ]);
    }

    public function stats(): JsonResponse
    {
        $stats = [
            'total' => Subcontractor::count(),
            'active' => Subcontractor::active()->count(),
            'approved' => Subcontractor::approved()->count(),
            'blacklisted' => Subcontractor::blacklisted()->count(),
            'pending_approval' => Subcontractor::where('is_approved', false)->count(),
            'high_rated' => Subcontractor::where('rating', '>=', 4.0)->count(),
            'average_rating' => round(Subcontractor::avg('rating'), 2),
        ];

        return response()->json(['success' => true, 'data' => $stats]);
    }

    public function approve(Subcontractor $subcontractor): JsonResponse
    {
        if ($subcontractor->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Bu taşeron zaten onaylanmış.',
            ], 422);
        }

        $subcontractor->update([
            'is_approved' => true,
            'approval_date' => now(),
            'approved_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Taşeron onaylandı.',
            'data' => $subcontractor,
        ]);
    }

    public function blacklist(Request $request, Subcontractor $subcontractor): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $subcontractor->update([
            'status' => 'blacklisted',
            'notes' => ($subcontractor->notes ? $subcontractor->notes . "\n\n" : '') .
                      "Kara listeye alındı: " . $validated['reason'] .
                      " (" . now()->format('d.m.Y H:i') . " - " . Auth::user()->name . ")",
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Taşeron kara listeye alındı.',
            'data' => $subcontractor,
        ]);
    }

    public function activate(Subcontractor $subcontractor): JsonResponse
    {
        $subcontractor->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => 'Taşeron aktif hale getirildi.',
            'data' => $subcontractor,
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = SubcontractorCategory::active()
            ->withCount('subcontractors')
            ->orderBy('name')
            ->get();

        return response()->json(['success' => true, 'data' => $categories]);
    }

    public function byCategory(Request $request, $categoryId): JsonResponse
    {
        $query = Subcontractor::where('category_id', $categoryId)
            ->with('category');

        if ($request->boolean('approved_only')) {
            $query->approved();
        }

        if ($request->boolean('active_only')) {
            $query->active();
        }

        $subcontractors = $query->orderBy('company_name')->get();

        return response()->json(['success' => true, 'data' => $subcontractors]);
    }

    // Helper methods
    private function getCertificationStats(Subcontractor $subcontractor): array
    {
        $certifications = $subcontractor->certifications;

        return [
            'total' => $certifications->count(),
            'valid' => $certifications->where('status', 'valid')->count(),
            'expired' => $certifications->where('status', 'expired')->count(),
            'expiring_soon' => $subcontractor->expiringSoonCertifications()->count(),
        ];
    }

    private function getRatingStats(Subcontractor $subcontractor): array
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
