<?php

namespace App\Http\Controllers;

use App\Models\LeaveBalanceLog;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\LeaveCalculation;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class LeaveBalanceLogController extends Controller
{
    /**
     * Display a listing of leave balance logs
     */
    public function index(Request $request): Response
    {
        $query = LeaveBalanceLog::with(['employee', 'leaveType', 'adjustedBy']);

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

        // Filter by action type
        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by adjustment reason
        if ($request->filled('reason_category')) {
            $query->where('reason_category', $request->reason_category);
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('LeaveManagement/BalanceLogs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['search', 'employee_id', 'leave_type_id', 'action_type', 'date_from', 'date_to', 'reason_category']),
            'stats' => $this->getBalanceLogStats(),
            'employees' => $this->getEmployeesForFilter(),
            'leaveTypes' => $this->getLeaveTypesForFilter(),
            'actionTypes' => $this->getActionTypes(),
            'reasonCategories' => $this->getReasonCategories(),
        ]);
    }

    /**
     * Display balance logs for a specific employee
     */
    public function byEmployee(Request $request, Employee $employee): Response
    {
        $query = $employee->leaveBalanceLogs()->with(['leaveType', 'adjustedBy']);

        // Filter by leave type
        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        // Filter by action type
        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('LeaveManagement/BalanceLogs/ByEmployee', [
            'employee' => $employee,
            'logs' => $logs,
            'filters' => $request->only(['leave_type_id', 'action_type', 'date_from', 'date_to']),
            'employeeStats' => $this->getEmployeeBalanceStats($employee),
            'currentBalances' => $this->getCurrentBalances($employee),
            'leaveTypes' => $this->getLeaveTypesForFilter(),
            'actionTypes' => $this->getActionTypes(),
        ]);
    }

    /**
     * Adjust leave balance manually
     */
    public function adjust(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'adjustment_type' => 'required|in:add,subtract,set',
            'adjustment_amount' => 'required|numeric|min:0',
            'reason_category' => 'required|in:correction,bonus,penalty,carry_forward,system_error,administrative,other',
            'reason' => 'required|string|max:500',
            'effective_date' => 'nullable|date',
            'reference_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        $employee = Employee::find($validated['employee_id']);
        $leaveType = LeaveType::find($validated['leave_type_id']);
        $effectiveDate = $validated['effective_date'] ? Carbon::parse($validated['effective_date']) : now();

        try {
            DB::beginTransaction();

            // Get current balance
            $currentBalance = $this->getCurrentBalance($employee, $leaveType, $effectiveDate->year);
            
            // Calculate new balance
            $newBalance = $this->calculateNewBalance(
                $currentBalance,
                $validated['adjustment_type'],
                $validated['adjustment_amount']
            );

            // Validate new balance
            if ($newBalance < 0) {
                throw new \Exception('Düzenleme sonucu bakiye negatif olamaz.');
            }

            // Create balance log entry
            $logData = [
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'action_type' => 'manual_adjustment',
                'previous_balance' => $currentBalance,
                'change_amount' => $this->getChangeAmount($validated['adjustment_type'], $validated['adjustment_amount'], $currentBalance, $newBalance),
                'new_balance' => $newBalance,
                'reason_category' => $validated['reason_category'],
                'reason' => $validated['reason'],
                'reference_number' => $validated['reference_number'],
                'notes' => $validated['notes'],
                'effective_date' => $effectiveDate,
                'adjusted_by' => Auth::id(),
                'metadata' => [
                    'adjustment_type' => $validated['adjustment_type'],
                    'adjustment_amount' => $validated['adjustment_amount'],
                    'calculation_year' => $effectiveDate->year,
                ],
            ];

            LeaveBalanceLog::create($logData);

            // Update or create leave calculation record
            $this->updateLeaveCalculation($employee, $leaveType, $effectiveDate->year, $newBalance);

            DB::commit();

            return back()->with('success', 'İzin bakiyesi başarıyla düzenlendi.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'İzin bakiyesi düzenlenirken hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Get current balance for employee and leave type
     */
    private function getCurrentBalance(Employee $employee, LeaveType $leaveType, int $year): float
    {
        // First try to get from calculation
        $calculation = LeaveCalculation::where([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'calculation_year' => $year,
        ])->first();

        if ($calculation) {
            return $calculation->remaining_days;
        }

        // If no calculation exists, calculate from logs
        $lastLog = LeaveBalanceLog::where([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
        ])
        ->whereYear('effective_date', $year)
        ->orderBy('effective_date', 'desc')
        ->orderBy('created_at', 'desc')
        ->first();

        return $lastLog ? $lastLog->new_balance : 0;
    }

    /**
     * Calculate new balance based on adjustment type
     */
    private function calculateNewBalance(float $currentBalance, string $adjustmentType, float $adjustmentAmount): float
    {
        switch ($adjustmentType) {
            case 'add':
                return $currentBalance + $adjustmentAmount;
            case 'subtract':
                return max(0, $currentBalance - $adjustmentAmount);
            case 'set':
                return $adjustmentAmount;
            default:
                throw new \Exception('Geçersiz düzenleme türü.');
        }
    }

    /**
     * Get change amount for logging
     */
    private function getChangeAmount(string $adjustmentType, float $adjustmentAmount, float $oldBalance, float $newBalance): float
    {
        return $newBalance - $oldBalance;
    }

    /**
     * Update leave calculation with new balance
     */
    private function updateLeaveCalculation(Employee $employee, LeaveType $leaveType, int $year, float $newBalance): void
    {
        $calculation = LeaveCalculation::where([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'calculation_year' => $year,
        ])->first();

        if ($calculation) {
            $calculation->update([
                'remaining_days' => $newBalance,
                'needs_recalculation' => false,
                'last_adjustment_at' => now(),
            ]);
        } else {
            // Create new calculation record for manual adjustment
            LeaveCalculation::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'calculation_year' => $year,
                'entitlement_days' => 0,
                'carried_forward_days' => 0,
                'used_days' => 0,
                'pending_days' => 0,
                'remaining_days' => $newBalance,
                'calculation_date' => now(),
                'calculation_method' => 'manual_adjustment',
                'status' => 'approved',
                'calculated_by' => Auth::id(),
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
        }
    }

    /**
     * Get balance log statistics
     */
    private function getBalanceLogStats(): array
    {
        return [
            'total_logs' => LeaveBalanceLog::count(),
            'today' => LeaveBalanceLog::whereDate('created_at', today())->count(),
            'this_week' => LeaveBalanceLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => LeaveBalanceLog::whereMonth('created_at', now()->month)->count(),
            'by_action_type' => LeaveBalanceLog::selectRaw('action_type, COUNT(*) as count')
                ->groupBy('action_type')
                ->pluck('count', 'action_type')
                ->toArray(),
            'by_reason_category' => LeaveBalanceLog::selectRaw('reason_category, COUNT(*) as count')
                ->groupBy('reason_category')
                ->pluck('count', 'reason_category')
                ->toArray(),
            'recent_adjustments' => LeaveBalanceLog::where('action_type', 'manual_adjustment')
                ->whereBetween('created_at', [now()->subDays(7), now()])
                ->count(),
        ];
    }

    /**
     * Get employee balance statistics
     */
    private function getEmployeeBalanceStats(Employee $employee): array
    {
        return [
            'total_logs' => $employee->leaveBalanceLogs()->count(),
            'adjustments' => $employee->leaveBalanceLogs()->where('action_type', 'manual_adjustment')->count(),
            'system_changes' => $employee->leaveBalanceLogs()->where('action_type', '!=', 'manual_adjustment')->count(),
            'last_adjustment' => $employee->leaveBalanceLogs()
                ->where('action_type', 'manual_adjustment')
                ->latest()
                ->first()?->created_at,
        ];
    }

    /**
     * Get current balances for employee
     */
    private function getCurrentBalances(Employee $employee): array
    {
        $currentYear = now()->year;
        
        $calculations = LeaveCalculation::where('employee_id', $employee->id)
            ->where('calculation_year', $currentYear)
            ->with('leaveType')
            ->get();

        return $calculations->map(function ($calculation) {
            return [
                'leave_type' => $calculation->leaveType->name,
                'entitlement' => $calculation->entitlement_days,
                'used' => $calculation->used_days,
                'pending' => $calculation->pending_days,
                'remaining' => $calculation->remaining_days,
                'carry_forward' => $calculation->carried_forward_days,
                'last_updated' => $calculation->updated_at,
            ];
        })->toArray();
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
     * Get available action types
     */
    private function getActionTypes(): array
    {
        return [
            'manual_adjustment' => 'Manuel Düzenleme',
            'system_calculation' => 'Sistem Hesaplaması',
            'leave_request' => 'İzin Talebi',
            'leave_cancellation' => 'İzin İptali',
            'carry_forward' => 'Devir İşlemi',
            'year_end_process' => 'Yıl Sonu İşlemi',
            'import' => 'Veri Aktarımı',
            'correction' => 'Düzeltme',
        ];
    }

    /**
     * Get available reason categories
     */
    private function getReasonCategories(): array
    {
        return [
            'correction' => 'Düzeltme',
            'bonus' => 'Bonus/Ek İzin',
            'penalty' => 'Ceza/Kesinti',
            'carry_forward' => 'Devir İşlemi',
            'system_error' => 'Sistem Hatası',
            'administrative' => 'İdari İşlem',
            'other' => 'Diğer',
        ];
    }

    /**
     * Log balance change automatically (called from other parts of system)
     */
    public static function logBalanceChange(array $data): LeaveBalanceLog
    {
        return LeaveBalanceLog::create([
            'employee_id' => $data['employee_id'],
            'leave_type_id' => $data['leave_type_id'],
            'action_type' => $data['action_type'],
            'previous_balance' => $data['previous_balance'] ?? 0,
            'change_amount' => $data['change_amount'],
            'new_balance' => $data['new_balance'],
            'reason_category' => $data['reason_category'] ?? 'system_calculation',
            'reason' => $data['reason'] ?? 'Otomatik sistem hesaplaması',
            'reference_number' => $data['reference_number'] ?? null,
            'notes' => $data['notes'] ?? null,
            'effective_date' => $data['effective_date'] ?? now(),
            'adjusted_by' => $data['adjusted_by'] ?? Auth::id(),
            'metadata' => $data['metadata'] ?? [],
        ]);
    }

    /**
     * Get balance history for employee and leave type
     */
    public function getBalanceHistory(Employee $employee, LeaveType $leaveType, int $year): array
    {
        return LeaveBalanceLog::where([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
        ])
        ->whereYear('effective_date', $year)
        ->orderBy('effective_date')
        ->orderBy('created_at')
        ->get()
        ->map(function ($log) {
            return [
                'date' => $log->effective_date->format('Y-m-d'),
                'action' => $log->action_type,
                'previous_balance' => $log->previous_balance,
                'change' => $log->change_amount,
                'new_balance' => $log->new_balance,
                'reason' => $log->reason,
                'adjusted_by' => $log->adjustedBy?->name,
            ];
        })
        ->toArray();
    }

    /**
     * Generate balance report for multiple employees
     */
    public function generateBalanceReport(Request $request): array
    {
        $validated = $request->validate([
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'exists:employees,id',
            'leave_type_ids' => 'nullable|array',
            'leave_type_ids.*' => 'exists:leave_types,id',
            'year' => 'required|integer|min:2020|max:' . (now()->year + 1),
            'include_history' => 'boolean',
        ]);

        $year = $validated['year'];
        $employeeIds = $validated['employee_ids'] ?? Employee::where('status', 'active')->pluck('id')->toArray();
        $leaveTypeIds = $validated['leave_type_ids'] ?? LeaveType::where('status', 'active')->pluck('id')->toArray();

        $report = [];

        foreach ($employeeIds as $employeeId) {
            $employee = Employee::find($employeeId);
            $employeeData = [
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->full_name,
                    'code' => $employee->employee_code,
                    'category' => $employee->category,
                ],
                'balances' => [],
            ];

            foreach ($leaveTypeIds as $leaveTypeId) {
                $leaveType = LeaveType::find($leaveTypeId);
                $calculation = LeaveCalculation::where([
                    'employee_id' => $employeeId,
                    'leave_type_id' => $leaveTypeId,
                    'calculation_year' => $year,
                ])->first();

                $balanceData = [
                    'leave_type' => $leaveType->name,
                    'entitlement' => $calculation?->entitlement_days ?? 0,
                    'carry_forward' => $calculation?->carried_forward_days ?? 0,
                    'used' => $calculation?->used_days ?? 0,
                    'pending' => $calculation?->pending_days ?? 0,
                    'remaining' => $calculation?->remaining_days ?? 0,
                    'last_updated' => $calculation?->updated_at,
                ];

                if ($validated['include_history'] ?? false) {
                    $balanceData['history'] = $this->getBalanceHistory($employee, $leaveType, $year);
                }

                $employeeData['balances'][] = $balanceData;
            }

            $report[] = $employeeData;
        }

        return $report;
    }

    /**
     * Export balance logs to CSV
     */
    public function exportLogs(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $validated = $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'leave_type_id' => 'nullable|exists:leave_types,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'action_type' => 'nullable|string',
        ]);

        $query = LeaveBalanceLog::with(['employee', 'leaveType', 'adjustedBy']);

        if ($validated['employee_id'] ?? null) {
            $query->where('employee_id', $validated['employee_id']);
        }

        if ($validated['leave_type_id'] ?? null) {
            $query->where('leave_type_id', $validated['leave_type_id']);
        }

        if ($validated['date_from'] ?? null) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
        }

        if ($validated['date_to'] ?? null) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
        }

        if ($validated['action_type'] ?? null) {
            $query->where('action_type', $validated['action_type']);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="izin_bakiye_loglari_' . now()->format('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($logs) {
            $handle = fopen('php://output', 'w');

            // CSV headers
            fputcsv($handle, [
                'Tarih',
                'Çalışan',
                'Çalışan Kodu',
                'İzin Türü',
                'İşlem Türü',
                'Önceki Bakiye',
                'Değişim',
                'Yeni Bakiye',
                'Sebep Kategorisi',
                'Sebep',
                'Referans No',
                'Düzenleyen',
                'Notlar',
            ]);

            // CSV data
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->employee->full_name,
                    $log->employee->employee_code,
                    $log->leaveType->name,
                    $this->getActionTypes()[$log->action_type] ?? $log->action_type,
                    $log->previous_balance,
                    $log->change_amount,
                    $log->new_balance,
                    $this->getReasonCategories()[$log->reason_category] ?? $log->reason_category,
                    $log->reason,
                    $log->reference_number,
                    $log->adjustedBy?->name ?? 'Sistem',
                    $log->notes,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}