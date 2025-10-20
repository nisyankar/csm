<?php

namespace App\Http\Controllers;

use App\Models\LeaveCalculation;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use App\Models\LeaveParameter;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class LeaveCalculationController extends Controller
{
    /**
     * Display a listing of leave calculations
     */
    public function index(Request $request): Response
    {
        $query = LeaveCalculation::with(['employee', 'leaveType']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by leave type
        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        // Filter by calculation year
        if ($request->filled('calculation_year')) {
            $query->where('calculation_year', $request->calculation_year);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by needs recalculation
        if ($request->filled('needs_recalculation')) {
            $query->where('needs_recalculation', $request->boolean('needs_recalculation'));
        }

        $calculations = $query->orderBy('calculation_year', 'desc')
            ->orderBy('updated_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('LeaveManagement/Calculations/Index', [
            'calculations' => $calculations,
            'filters' => $request->only(['search', 'employee_id', 'leave_type_id', 'calculation_year', 'status', 'needs_recalculation']),
            'stats' => $this->getCalculationStats(),
            'employees' => $this->getEmployeesForFilter(),
            'leaveTypes' => $this->getLeaveTypesForFilter(),
            'years' => $this->getAvailableYears(),
        ]);
    }

    /**
     * Display the specified calculation
     */
    public function show(LeaveCalculation $calculation): Response
    {
        $calculation->load(['employee', 'leaveType', 'approver']);

        return Inertia::render('LeaveManagement/Calculations/Show', [
            'calculation' => $calculation,
            'breakdown' => $this->getCalculationBreakdown($calculation),
            'relatedRequests' => $this->getRelatedRequests($calculation),
            'history' => $this->getCalculationHistory($calculation),
        ]);
    }

    /**
     * Recalculate leave for a specific employee
     */
    public function recalculate(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'calculation_year' => 'required|integer|min:2020|max:' . (now()->year + 1),
            'force_recalculation' => 'boolean',
            'reason' => 'nullable|string|max:500',
        ]);

        $leaveType = LeaveType::find($validated['leave_type_id']);

        try {
            DB::beginTransaction();

            $calculation = $this->performLeaveCalculation(
                $employee,
                $leaveType,
                $validated['calculation_year'],
                $validated['force_recalculation'] ?? false,
                $validated['reason']
            );

            DB::commit();

            return back()->with('success', 'İzin hesaplaması başarıyla tamamlandı.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'İzin hesaplaması sırasında hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Bulk recalculate leave for multiple employees
     */
    public function bulkRecalculate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'calculation_year' => 'required|integer|min:2020|max:' . (now()->year + 1),
            'force_recalculation' => 'boolean',
            'reason' => 'nullable|string|max:500',
        ]);

        $employees = Employee::whereIn('id', $validated['employee_ids'])->get();
        $leaveType = LeaveType::find($validated['leave_type_id']);

        $successful = 0;
        $failed = 0;
        $errors = [];

        try {
            DB::beginTransaction();

            foreach ($employees as $employee) {
                try {
                    $this->performLeaveCalculation(
                        $employee,
                        $leaveType,
                        $validated['calculation_year'],
                        $validated['force_recalculation'] ?? false,
                        $validated['reason']
                    );
                    $successful++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "{$employee->full_name}: {$e->getMessage()}";
                }
            }

            DB::commit();

            $message = "Toplu hesaplama tamamlandı. Başarılı: {$successful}, Başarısız: {$failed}";
            
            if (!empty($errors)) {
                session()->flash('bulk_calculation_errors', $errors);
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Toplu hesaplama sırasında kritik hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Approve a calculation
     */
    public function approve(Request $request, LeaveCalculation $calculation): RedirectResponse
    {
        if ($calculation->status !== 'pending') {
            return back()->with('error', 'Sadece bekleyen hesaplamalar onaylanabilir.');
        }

        $validated = $request->validate([
            'approval_notes' => 'nullable|string|max:500',
        ]);

        $calculation->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'approval_notes' => $validated['approval_notes'],
        ]);

        return back()->with('success', 'İzin hesaplaması onaylandı.');
    }

    /**
     * Perform the actual leave calculation
     */
    private function performLeaveCalculation(
        Employee $employee, 
        LeaveType $leaveType, 
        int $year, 
        bool $force = false,
        ?string $reason = null
    ): LeaveCalculation {
        // Check if calculation already exists
        $existingCalculation = LeaveCalculation::where([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'calculation_year' => $year,
        ])->first();

        if ($existingCalculation && !$force) {
            if ($existingCalculation->status === 'approved' && !$existingCalculation->needs_recalculation) {
                throw new \Exception('Bu hesaplama zaten onaylanmış ve yeniden hesaplama gerektirmiyor.');
            }
        }

        // Get calculation parameters
        $parameters = $this->getCalculationParameters($leaveType, $employee);
        
        // Calculate entitlement
        $entitlement = $this->calculateEntitlement($employee, $leaveType, $year, $parameters);
        
        // Calculate usage
        $usage = $this->calculateUsage($employee, $leaveType, $year);
        
        // Calculate carry forward from previous year
        $carryForward = $this->calculateCarryForward($employee, $leaveType, $year - 1);
        
        // Calculate final balance
        $balance = $entitlement + $carryForward - $usage['total_days'];
        
        // Prepare calculation data
        $calculationData = [
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'calculation_year' => $year,
            'entitlement_days' => $entitlement,
            'carried_forward_days' => $carryForward,
            'used_days' => $usage['total_days'],
            'pending_days' => $usage['pending_days'],
            'remaining_days' => max(0, $balance),
            'calculation_date' => now(),
            'calculation_method' => $this->getCalculationMethod($leaveType),
            'parameters_used' => $parameters,
            'calculation_details' => [
                'base_entitlement' => $entitlement,
                'working_days_in_year' => $this->getWorkingDaysInYear($year),
                'employee_start_date' => $employee->start_date,
                'pro_rata_calculation' => $this->isProRataRequired($employee, $year),
                'usage_breakdown' => $usage,
            ],
            'status' => 'pending',
            'calculated_by' => Auth::id(),
            'needs_recalculation' => false,
            'recalculation_reason' => $reason,
        ];

        // Create or update calculation
        if ($existingCalculation) {
            $existingCalculation->update($calculationData);
            return $existingCalculation;
        } else {
            return LeaveCalculation::create($calculationData);
        }
    }

    /**
     * Get calculation parameters for leave type and employee
     */
    private function getCalculationParameters(LeaveType $leaveType, Employee $employee): array
    {
        $parameters = [];
        
        // Get relevant parameters
        $relevantParams = LeaveParameter::where('status', 'active')
            ->where(function ($query) use ($leaveType, $employee) {
                $query->where('applies_to_all', true)
                    ->orWhere('category', $leaveType->category)
                    ->orWhereJsonContains('employee_categories', $employee->category);
            })
            ->get();

        foreach ($relevantParams as $param) {
            $parameters[$param->parameter_key] = $param->default_value;
        }

        return $parameters;
    }

    /**
     * Calculate base entitlement for employee
     */
    private function calculateEntitlement(Employee $employee, LeaveType $leaveType, int $year, array $parameters): float
    {
        $baseEntitlement = $leaveType->max_days_per_year ?? 0;
        
        // Check if pro-rata calculation is needed
        if ($this->isProRataRequired($employee, $year)) {
            $workingDaysInYear = $this->getWorkingDaysFromEmployeeStart($employee, $year);
            $totalWorkingDaysInYear = $this->getWorkingDaysInYear($year);
            
            $baseEntitlement = ($baseEntitlement * $workingDaysInYear) / $totalWorkingDaysInYear;
        }

        // Apply accrual rate if specified
        if ($leaveType->accrual_rate) {
            $baseEntitlement *= $leaveType->accrual_rate;
        }

        return round($baseEntitlement, 2);
    }

    /**
     * Calculate leave usage for the year
     */
    private function calculateUsage(Employee $employee, LeaveType $leaveType, int $year): array
    {
        $approvedRequests = LeaveRequest::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->sum('days_requested');

        $pendingRequests = LeaveRequest::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'pending')
            ->whereYear('start_date', $year)
            ->sum('days_requested');

        return [
            'approved_days' => $approvedRequests,
            'pending_days' => $pendingRequests,
            'total_days' => $approvedRequests + $pendingRequests,
        ];
    }

    /**
     * Calculate carry forward from previous year
     */
    private function calculateCarryForward(Employee $employee, LeaveType $leaveType, int $previousYear): float
    {
        if (!$leaveType->carry_forward_allowed) {
            return 0;
        }

        $previousCalculation = LeaveCalculation::where([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'calculation_year' => $previousYear,
            'status' => 'approved',
        ])->first();

        if (!$previousCalculation) {
            return 0;
        }

        $carryForward = max(0, $previousCalculation->remaining_days);
        
        // Apply carry forward limit
        if ($leaveType->carry_forward_max_days) {
            $carryForward = min($carryForward, $leaveType->carry_forward_max_days);
        }

        return $carryForward;
    }

    /**
     * Check if pro-rata calculation is required
     */
    private function isProRataRequired(Employee $employee, int $year): bool
    {
        $employeeStartYear = Carbon::parse($employee->start_date)->year;
        return $employeeStartYear == $year;
    }

    /**
     * Get working days in year
     */
    private function getWorkingDaysInYear(int $year): int
    {
        $start = Carbon::createFromDate($year, 1, 1);
        $end = Carbon::createFromDate($year, 12, 31);
        
        $workingDays = 0;
        while ($start->lte($end)) {
            if (!$start->isWeekend()) {
                $workingDays++;
            }
            $start->addDay();
        }
        
        return $workingDays;
    }

    /**
     * Get working days from employee start date in year
     */
    private function getWorkingDaysFromEmployeeStart(Employee $employee, int $year): int
    {
        $employeeStart = Carbon::parse($employee->start_date);
        $yearStart = Carbon::createFromDate($year, 1, 1);
        $yearEnd = Carbon::createFromDate($year, 12, 31);
        
        $start = $employeeStart->gt($yearStart) ? $employeeStart : $yearStart;
        $end = $yearEnd;
        
        $workingDays = 0;
        while ($start->lte($end)) {
            if (!$start->isWeekend()) {
                $workingDays++;
            }
            $start->addDay();
        }
        
        return $workingDays;
    }

    /**
     * Get calculation method description
     */
    private function getCalculationMethod(LeaveType $leaveType): string
    {
        $methods = [];
        
        if ($leaveType->accrual_rate) {
            $methods[] = "Tahakkuk oranı: {$leaveType->accrual_rate}";
        }
        
        if ($leaveType->carry_forward_allowed) {
            $methods[] = "Devir izni";
        }
        
        return implode(', ', $methods) ?: 'Standart hesaplama';
    }

    /**
     * Get calculation statistics
     */
    private function getCalculationStats(): array
    {
        $currentYear = now()->year;
        
        return [
            'total' => LeaveCalculation::count(),
            'this_year' => LeaveCalculation::where('calculation_year', $currentYear)->count(),
            'pending' => LeaveCalculation::where('status', 'pending')->count(),
            'approved' => LeaveCalculation::where('status', 'approved')->count(),
            'needs_recalculation' => LeaveCalculation::where('needs_recalculation', true)->count(),
            'by_year' => LeaveCalculation::selectRaw('calculation_year, COUNT(*) as count')
                ->groupBy('calculation_year')
                ->orderBy('calculation_year', 'desc')
                ->pluck('count', 'calculation_year')
                ->toArray(),
        ];
    }

    /**
     * Get employees for filter dropdown
     */
    private function getEmployeesForFilter(): array
    {
        return Employee::where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'employee_code'])
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->full_name,
                    'code' => $employee->employee_code,
                ];
            })
            ->toArray();
    }

    /**
     * Get leave types for filter dropdown
     */
    private function getLeaveTypesForFilter(): array
    {
        return LeaveType::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->toArray();
    }

    /**
     * Get available years for calculation
     */
    private function getAvailableYears(): array
    {
        $currentYear = now()->year;
        $startYear = LeaveCalculation::min('calculation_year') ?? ($currentYear - 2);
        
        $years = [];
        for ($year = $currentYear + 1; $year >= $startYear; $year--) {
            $years[] = $year;
        }
        
        return $years;
    }

    /**
     * Get calculation breakdown details
     */
    private function getCalculationBreakdown(LeaveCalculation $calculation): array
    {
        return [
            'base_calculation' => [
                'entitlement' => $calculation->entitlement_days,
                'carry_forward' => $calculation->carried_forward_days,
                'total_available' => $calculation->entitlement_days + $calculation->carried_forward_days,
            ],
            'usage' => [
                'used' => $calculation->used_days,
                'pending' => $calculation->pending_days,
                'total_used' => $calculation->used_days + $calculation->pending_days,
            ],
            'balance' => [
                'remaining' => $calculation->remaining_days,
                'percentage_used' => $calculation->entitlement_days > 0 
                    ? round(($calculation->used_days / $calculation->entitlement_days) * 100, 1) 
                    : 0,
            ],
            'parameters' => $calculation->parameters_used,
            'details' => $calculation->calculation_details,
        ];
    }

    /**
     * Get related leave requests
     */
    private function getRelatedRequests(LeaveCalculation $calculation): array
    {
        return LeaveRequest::where('employee_id', $calculation->employee_id)
            ->where('leave_type_id', $calculation->leave_type_id)
            ->whereYear('start_date', $calculation->calculation_year)
            ->with('approver')
            ->orderBy('start_date')
            ->get()
            ->toArray();
    }

    /**
     * Get calculation history
     */
    private function getCalculationHistory(LeaveCalculation $calculation): array
    {
        // This would return audit trail for calculation changes
        // For now, return placeholder data
        return [];
    }
}