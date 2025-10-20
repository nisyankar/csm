<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of leave types
     */
    public function index(Request $request): Response
    {
        $query = LeaveType::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by paid/unpaid
        if ($request->filled('is_paid')) {
            $query->where('is_paid', $request->boolean('is_paid'));
        }

        $leaveTypes = $query->withCount(['leaveRequests as total_requests', 'leaveRequests as approved_requests' => function ($q) {
                $q->where('status', 'approved');
            }])
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('LeaveManagement/Types/Index', [
            'leaveTypes' => $leaveTypes,
            'filters' => $request->only(['search', 'status', 'category', 'is_paid']),
            'stats' => $this->getLeaveTypeStats(),
            'categories' => $this->getLeaveCategories(),
        ]);
    }

    /**
     * Show the form for creating a new leave type
     */
    public function create(): Response
    {
        return Inertia::render('LeaveManagement/Types/Create', [
            'categories' => $this->getLeaveCategories(),
            'employeeCategories' => $this->getEmployeeCategories(),
        ]);
    }

    /**
     * Store a newly created leave type
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:leave_types',
            'description' => 'nullable|string',
            'category' => 'required|in:annual,sick,maternity,paternity,marriage,funeral,military,unpaid,emergency,study,other',
            'is_paid' => 'boolean',
            'requires_approval' => 'boolean',
            'requires_documentation' => 'boolean',
            'max_days_per_year' => 'nullable|integer|min:1',
            'max_days_per_request' => 'nullable|integer|min:1',
            'min_days_advance_notice' => 'nullable|integer|min:0',
            'max_days_advance_notice' => 'nullable|integer|min:0',
            'carry_forward_allowed' => 'boolean',
            'carry_forward_max_days' => 'nullable|integer|min:0',
            'accrual_rate' => 'nullable|numeric|min:0',
            'waiting_period_days' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
            'gender_restriction' => 'nullable|in:male,female',
            'marital_status_restriction' => 'nullable|in:single,married',
            'employee_categories' => 'nullable|array',
            'employee_categories.*' => 'string|in:worker,foreman,engineer,manager,system_admin',
            'calculation_rules' => 'nullable|array',
            'documentation_required' => 'nullable|array',
            'documentation_required.*' => 'string',
        ]);

        // Validation for advance notice
        if (isset($validated['min_days_advance_notice'], $validated['max_days_advance_notice'])) {
            if ($validated['min_days_advance_notice'] > $validated['max_days_advance_notice']) {
                return back()->withErrors(['max_days_advance_notice' => 'Maksimum önceden haber verme süresi minimumdan büyük olmalıdır.']);
            }
        }

        // Validation for carry forward
        if ($validated['carry_forward_allowed'] && isset($validated['max_days_per_year'], $validated['carry_forward_max_days'])) {
            if ($validated['carry_forward_max_days'] > $validated['max_days_per_year']) {
                return back()->withErrors(['carry_forward_max_days' => 'Devredilebilir maksimum gün sayısı yıllık limitten fazla olamaz.']);
            }
        }

        $validated['created_by'] = Auth::id();

        LeaveType::create($validated);

        return redirect()->route('leave-management.types.index')
            ->with('success', 'İzin türü başarıyla oluşturuldu.');
    }

    /**
     * Display the specified leave type
     */
    public function show(LeaveType $leaveType): Response
    {
        $leaveType->load(['creator', 'leaveRequests' => function ($query) {
            $query->with('employee')->latest()->limit(10);
        }]);

        return Inertia::render('LeaveManagement/Types/Show', [
            'leaveType' => $leaveType,
            'stats' => $this->getLeaveTypeDetailStats($leaveType),
            'recentRequests' => $leaveType->leaveRequests,
            'monthlyUsage' => $this->getMonthlyUsageStats($leaveType),
        ]);
    }

    /**
     * Show the form for editing the leave type
     */
    public function edit(LeaveType $leaveType): Response
    {
        return Inertia::render('LeaveManagement/Types/Edit', [
            'leaveType' => $leaveType,
            'categories' => $this->getLeaveCategories(),
            'employeeCategories' => $this->getEmployeeCategories(),
        ]);
    }

    /**
     * Update the specified leave type
     */
    public function update(Request $request, LeaveType $leaveType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:leave_types,code,' . $leaveType->id,
            'description' => 'nullable|string',
            'category' => 'required|in:annual,sick,maternity,paternity,marriage,funeral,military,unpaid,emergency,study,other',
            'is_paid' => 'boolean',
            'requires_approval' => 'boolean',
            'requires_documentation' => 'boolean',
            'max_days_per_year' => 'nullable|integer|min:1',
            'max_days_per_request' => 'nullable|integer|min:1',
            'min_days_advance_notice' => 'nullable|integer|min:0',
            'max_days_advance_notice' => 'nullable|integer|min:0',
            'carry_forward_allowed' => 'boolean',
            'carry_forward_max_days' => 'nullable|integer|min:0',
            'accrual_rate' => 'nullable|numeric|min:0',
            'waiting_period_days' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
            'gender_restriction' => 'nullable|in:male,female',
            'marital_status_restriction' => 'nullable|in:single,married',
            'employee_categories' => 'nullable|array',
            'employee_categories.*' => 'string|in:worker,foreman,engineer,manager,system_admin',
            'calculation_rules' => 'nullable|array',
            'documentation_required' => 'nullable|array',
            'documentation_required.*' => 'string',
        ]);

        // Validation for advance notice
        if (isset($validated['min_days_advance_notice'], $validated['max_days_advance_notice'])) {
            if ($validated['min_days_advance_notice'] > $validated['max_days_advance_notice']) {
                return back()->withErrors(['max_days_advance_notice' => 'Maksimum önceden haber verme süresi minimumdan büyük olmalıdır.']);
            }
        }

        // Validation for carry forward
        if ($validated['carry_forward_allowed'] && isset($validated['max_days_per_year'], $validated['carry_forward_max_days'])) {
            if ($validated['carry_forward_max_days'] > $validated['max_days_per_year']) {
                return back()->withErrors(['carry_forward_max_days' => 'Devredilebilir maksimum gün sayısı yıllık limitten fazla olamaz.']);
            }
        }

        $leaveType->update($validated);

        return redirect()->route('leave-management.types.show', $leaveType)
            ->with('success', 'İzin türü başarıyla güncellendi.');
    }

    /**
     * Remove the specified leave type
     */
    public function destroy(LeaveType $leaveType): RedirectResponse
    {
        // Check if leave type has any requests
        if ($leaveType->leaveRequests()->exists()) {
            return back()->with('error', 'Bu izin türüne ait talepler bulunduğu için silinemez.');
        }

        $leaveType->delete();

        return redirect()->route('leave-management.types.index')
            ->with('success', 'İzin türü başarıyla silindi.');
    }

    /**
     * Get leave type statistics
     */
    private function getLeaveTypeStats(): array
    {
        return [
            'total' => LeaveType::count(),
            'active' => LeaveType::where('status', 'active')->count(),
            'inactive' => LeaveType::where('status', 'inactive')->count(),
            'paid' => LeaveType::where('is_paid', true)->count(),
            'unpaid' => LeaveType::where('is_paid', false)->count(),
            'requires_approval' => LeaveType::where('requires_approval', true)->count(),
            'by_category' => LeaveType::selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
        ];
    }

    /**
     * Get detailed statistics for a specific leave type
     */
    private function getLeaveTypeDetailStats(LeaveType $leaveType): array
    {
        $currentYear = now()->year;
        
        return [
            'total_requests' => $leaveType->leaveRequests()->count(),
            'requests_this_year' => $leaveType->leaveRequests()
                ->whereYear('start_date', $currentYear)
                ->count(),
            'approved_requests' => $leaveType->leaveRequests()
                ->where('status', 'approved')
                ->count(),
            'pending_requests' => $leaveType->leaveRequests()
                ->where('status', 'pending')
                ->count(),
            'rejected_requests' => $leaveType->leaveRequests()
                ->where('status', 'rejected')
                ->count(),
            'total_days_requested' => $leaveType->leaveRequests()
                ->where('status', 'approved')
                ->sum('days_requested'),
            'average_request_days' => $leaveType->leaveRequests()
                ->where('status', 'approved')
                ->avg('days_requested'),
            'most_active_month' => $this->getMostActiveMonth($leaveType),
        ];
    }

    /**
     * Get monthly usage statistics
     */
    private function getMonthlyUsageStats(LeaveType $leaveType): array
    {
        $currentYear = now()->year;
        
        return LeaveRequest::selectRaw('MONTH(start_date) as month, COUNT(*) as requests, SUM(days_requested) as total_days')
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->month => [
                    'requests' => $item->requests,
                    'total_days' => $item->total_days,
                ]];
            })
            ->toArray();
    }

    /**
     * Get most active month for leave type
     */
    private function getMostActiveMonth(LeaveType $leaveType): ?int
    {
        $result = LeaveRequest::selectRaw('MONTH(start_date) as month, COUNT(*) as requests')
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'approved')
            ->groupBy('month')
            ->orderByDesc('requests')
            ->first();

        return $result ? $result->month : null;
    }

    /**
     * Get available leave categories
     */
    private function getLeaveCategories(): array
    {
        return [
            'annual' => 'Yıllık İzin',
            'sick' => 'Hastalık İzni',
            'maternity' => 'Doğum İzni',
            'paternity' => 'Babalık İzni',
            'marriage' => 'Evlilik İzni',
            'funeral' => 'Cenaze İzni',
            'military' => 'Askerlik İzni',
            'unpaid' => 'Ücretsiz İzin',
            'emergency' => 'Acil Durum İzni',
            'study' => 'Eğitim İzni',
            'other' => 'Diğer',
        ];
    }

    /**
     * Get employee categories
     */
    private function getEmployeeCategories(): array
    {
        return [
            'worker' => 'İşçi',
            'foreman' => 'Forman',
            'engineer' => 'Mühendis',
            'manager' => 'Proje Yöneticisi',
            'system_admin' => 'Sistem Yöneticisi',
        ];
    }
}