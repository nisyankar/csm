<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    /**
     * Taşeron için sözleşmeleri getir
     */
    public function bySubcontractor(Request $request, int $subcontractorId): JsonResponse
    {
        $query = Contract::where('subcontractor_id', $subcontractorId)
            ->with(['project']);

        // Proje filtresi
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Sadece aktif sözleşmeler
        if ($request->boolean('active_only')) {
            $query->where('status', 'active');
        }

        $contracts = $query->orderBy('start_date', 'desc')->get();

        return response()->json($contracts);
    }

    /**
     * Proje için sözleşmeleri getir
     */
    public function byProject(Request $request, int $projectId): JsonResponse
    {
        $query = Contract::where('project_id', $projectId)
            ->with(['subcontractor']);

        // Taşeron filtresi
        if ($request->filled('subcontractor_id')) {
            $query->where('subcontractor_id', $request->subcontractor_id);
        }

        // Tip filtresi
        if ($request->filled('contract_type')) {
            $query->where('contract_type', $request->contract_type);
        }

        // Sadece aktif sözleşmeler
        if ($request->boolean('active_only')) {
            $query->where('status', 'active');
        }

        $contracts = $query->orderBy('start_date', 'desc')->get();

        return response()->json($contracts);
    }

    /**
     * Proje + Taşeron için aktif sözleşme getir
     */
    public function activeContract(int $projectId, int $subcontractorId): JsonResponse
    {
        $contract = Contract::where('project_id', $projectId)
            ->where('subcontractor_id', $subcontractorId)
            ->where('status', 'active')
            ->with(['project', 'subcontractor'])
            ->first();

        if (!$contract) {
            return response()->json([
                'message' => 'Bu proje ve taşeron için aktif sözleşme bulunamadı.'
            ], 404);
        }

        // Sözleşme özeti
        $summary = [
            'contract_value' => $contract->contract_value,
            'total_paid' => $contract->total_paid_amount,
            'remaining' => $contract->remaining_amount,
            'completion_percentage' => $contract->completion_percentage,
            'days_until_expiry' => $contract->days_until_expiry,
        ];

        return response()->json([
            'contract' => $contract,
            'summary' => $summary,
        ]);
    }

    /**
     * Sözleşme arama
     */
    public function search(Request $request): JsonResponse
    {
        $query = Contract::with(['project', 'subcontractor']);

        // Arama terimi
        if ($request->filled('q')) {
            $query->search($request->q);
        }

        // Proje filtresi
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Taşeron filtresi
        if ($request->filled('subcontractor_id')) {
            $query->where('subcontractor_id', $request->subcontractor_id);
        }

        // Tip filtresi
        if ($request->filled('contract_type')) {
            $query->where('contract_type', $request->contract_type);
        }

        // Durum filtresi
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sadece aktif sözleşmeler
        if ($request->boolean('active_only')) {
            $query->where('status', 'active');
        }

        $limit = $request->get('limit', 20);
        $contracts = $query->orderBy('contract_number', 'desc')->limit($limit)->get();

        return response()->json($contracts);
    }

    /**
     * Sözleşme detayı API
     */
    public function show(Contract $contract): JsonResponse
    {
        $contract->load([
            'project',
            'subcontractor',
            'progressPayments',
        ]);

        $summary = [
            'contract_value' => $contract->contract_value,
            'total_paid' => $contract->total_paid_amount,
            'remaining' => $contract->remaining_amount,
            'completion_percentage' => $contract->completion_percentage,
            'payment_count' => $contract->progressPayments->count(),
            'days_until_expiry' => $contract->days_until_expiry,
            'is_expiring_soon' => $contract->is_expiring_soon,
            'is_expired' => $contract->is_expired,
        ];

        return response()->json([
            'contract' => $contract,
            'summary' => $summary,
        ]);
    }

    /**
     * Yaklaşan bitiş tarihleri
     */
    public function expiringSoon(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);
        $projectId = $request->get('project_id');

        $query = Contract::with(['project', 'subcontractor'])
            ->expiringSoon($days);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $contracts = $query->orderBy('end_date', 'asc')->get();

        return response()->json($contracts);
    }

    /**
     * Süresi dolan sözleşmeler
     */
    public function expired(Request $request): JsonResponse
    {
        $projectId = $request->get('project_id');

        $query = Contract::with(['project', 'subcontractor'])
            ->expired();

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $contracts = $query->orderBy('end_date', 'desc')->get();

        return response()->json($contracts);
    }

    /**
     * Dashboard istatistikleri API
     */
    public function stats(Request $request): JsonResponse
    {
        $projectId = $request->get('project_id');
        $query = Contract::query();

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $stats = [
            'total_contracts' => $query->count(),
            'active_contracts' => (clone $query)->where('status', 'active')->count(),
            'draft_contracts' => (clone $query)->where('status', 'draft')->count(),
            'completed_contracts' => (clone $query)->where('status', 'completed')->count(),
            'terminated_contracts' => (clone $query)->where('status', 'terminated')->count(),
            'expired_contracts' => (clone $query)->where('status', 'expired')->count(),
            'expiring_soon' => (clone $query)->expiringSoon(30)->count(),
            'total_value' => (clone $query)->where('status', 'active')->sum('contract_value'),
            'subcontractor_contracts' => (clone $query)->where('contract_type', 'subcontractor')->where('status', 'active')->count(),
            'supplier_contracts' => (clone $query)->where('contract_type', 'supplier')->where('status', 'active')->count(),
        ];

        return response()->json($stats);
    }
}
