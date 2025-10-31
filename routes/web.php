<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DwgImportController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeProjectAssignmentController;
use App\Http\Controllers\TimesheetController;
use App\Http\Controllers\TimesheetBulkController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\TimesheetApprovalController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\LeaveParameterController;
use App\Http\Controllers\LeaveBalanceLogController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LeaveCalculationController;
use App\Http\Controllers\LeaveReportController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\PurchasingRequestController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\SubcontractorController;
use App\Http\Controllers\ProjectStructureController;
use App\Http\Controllers\ProjectFloorController;
use App\Http\Controllers\ProgressPaymentController;
use App\Http\Controllers\ProjectUnitController;
use App\Http\Controllers\WorkItemAssignmentController;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\QuantityController;
use App\Http\Controllers\SafetyIncidentController;
use App\Http\Controllers\SafetyTrainingController;
use App\Http\Controllers\SafetyInspectionController;
use App\Http\Controllers\RiskAssessmentController;
use App\Http\Controllers\PpeAssignmentController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\EquipmentUsageController;
use App\Http\Controllers\EquipmentMaintenanceController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\StockCountController;
use App\Http\Controllers\ProjectScheduleController;
use App\Http\Controllers\UserProjectRoleController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\RoutePermissionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Guest Routes (Authentication)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'create'])->name('login');
    Route::post('login', [AuthController::class, 'store'])->name('login.store');

    // QR Code login (for mobile/kiosk)
    Route::get('qr-login', function () {
        return Inertia::render('Auth/QRLogin');
    })->name('qr.login');

    Route::post('qr-login', [AuthController::class, 'qrLogin'])->name('qr.login.store');
});

// Authenticated Routes
Route::middleware(['auth', 'verified'])->group(function () {

    // Logout
    Route::post('logout', [AuthController::class, 'destroy'])->name('logout');

    // Dashboard Routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Role-specific dashboards
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    Route::prefix('hr')->name('hr.')->middleware('role:hr|admin')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    Route::prefix('manager')->name('manager.')->middleware('role:project_manager|site_manager|admin')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    Route::prefix('foreman')->name('foreman.')->middleware('role:foreman|admin')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    // Profile and Settings Routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [AuthController::class, 'profile'])->name('show');
        Route::patch('/', [AuthController::class, 'updateProfile'])->name('update');
        Route::patch('/password', [AuthController::class, 'updatePassword'])->name('password.update');
        Route::post('/two-factor/enable', [AuthController::class, 'enableTwoFactor'])->name('two-factor.enable');
        Route::delete('/two-factor/disable', [AuthController::class, 'disableTwoFactor'])->name('two-factor.disable');
    });

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [AuthController::class, 'settings'])->name('show');
        Route::patch('/', [AuthController::class, 'updateSettings'])->name('update');
        Route::post('/delegate', [AuthController::class, 'delegateAuthority'])->name('delegate');
        Route::delete('/delegate', [AuthController::class, 'revokeDelegation'])->name('delegate.revoke');
    });

    // Employee Management Routes
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::get('/create', [EmployeeController::class, 'create'])
            ->middleware('role:admin|hr|project_manager')
            ->name('create');
        Route::post('/', [EmployeeController::class, 'store'])
            ->middleware('role:admin|hr|project_manager')
            ->name('store');
        Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
        Route::get('/{employee}/timesheets', [EmployeeController::class, 'getTimesheets'])->name('timesheets');
        Route::get('/{employee}/leaves', [EmployeeController::class, 'getLeaves'])->name('leaves');
        Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])
            ->middleware('role:admin|hr|project_manager')
            ->name('edit');
        Route::patch('/{employee}', [EmployeeController::class, 'update'])
            ->middleware('role:admin|hr|project_manager')
            ->name('update');
        Route::delete('/{employee}', [EmployeeController::class, 'destroy'])
            ->middleware('role:admin|hr')
            ->name('destroy');

        // Employee Actions
        Route::post('/{employee}/assign-project', [EmployeeController::class, 'assignToProject'])
            ->middleware('role:admin|hr|project_manager')
            ->name('assign-project');
        Route::delete('/{employee}/remove-project', [EmployeeController::class, 'removeFromProject'])
            ->middleware('role:admin|hr|project_manager')
            ->name('remove-project');
        Route::post('/{employee}/create-user', [EmployeeController::class, 'createUserAccount'])
            ->middleware('role:admin|hr')
            ->name('create-user');
        Route::patch('/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])
            ->middleware('role:admin|hr|project_manager')
            ->name('toggle-status');
        Route::post('/{employee}/terminate', [EmployeeController::class, 'terminate'])
            ->middleware('role:admin|hr')
            ->name('terminate');

        // QR Code Management
        Route::get('/{employee}/qr-code', [EmployeeController::class, 'generateQrCode'])
            ->name('qr-code');
        Route::post('/bulk-qr-codes', [EmployeeController::class, 'bulkGenerateQrCodes'])
            ->middleware('role:admin|hr|project_manager')
            ->name('bulk-qr-codes');

        // Reports
        Route::get('/{employee}/attendance-report', [EmployeeController::class, 'attendanceReport'])
            ->name('attendance-report');
        Route::get('/{employee}/performance', [EmployeeController::class, 'performanceMetrics'])
            ->name('performance');

        // Import/Export
        Route::get('/export', [EmployeeController::class, 'export'])
            ->middleware('role:admin|hr|project_manager')
            ->name('export');
        Route::post('/import', [EmployeeController::class, 'import'])
            ->middleware('role:admin|hr')
            ->name('import');
    });

    // Employee Project Assignment Management Routes
    Route::prefix('employee-assignments')->name('employee-assignments.')
        ->middleware('role:admin|hr|project_manager|site_manager')
        ->group(function () {
            Route::get('/', [EmployeeProjectAssignmentController::class, 'index'])->name('index');
            Route::get('/create', [EmployeeProjectAssignmentController::class, 'create'])->name('create');
            Route::post('/', [EmployeeProjectAssignmentController::class, 'store'])->name('store');
            Route::get('/{employeeAssignment}', [EmployeeProjectAssignmentController::class, 'show'])->name('show');
            Route::get('/{employeeAssignment}/edit', [EmployeeProjectAssignmentController::class, 'edit'])->name('edit');
            Route::patch('/{employeeAssignment}', [EmployeeProjectAssignmentController::class, 'update'])->name('update');
            Route::delete('/{employeeAssignment}', [EmployeeProjectAssignmentController::class, 'destroy'])->name('destroy');

            // Assignment Actions
            Route::post('/{employeeAssignment}/complete', [EmployeeProjectAssignmentController::class, 'complete'])->name('complete');

            // Employee-specific view
            Route::get('/employee/{employee}', [EmployeeProjectAssignmentController::class, 'employeeAssignments'])->name('employee');
        });

    // Timesheet Management Routes
    Route::prefix('timesheets')->name('timesheets.')->group(function () {
        Route::get('/', [TimesheetController::class, 'index'])->name('index');
        Route::get('/create', [TimesheetController::class, 'create'])->name('create');

        // Bulk Entry (MUST be before {timesheet} routes)
        Route::get('/bulk-entry', [TimesheetController::class, 'bulkEntry'])
            ->middleware('role:admin|hr|project_manager|site_manager|foreman')
            ->name('bulk-entry');
        Route::post('/bulk-store', [TimesheetController::class, 'bulkStore'])
            ->middleware('role:admin|hr|project_manager|site_manager|foreman')
            ->name('bulk-store');

        Route::post('/', [TimesheetController::class, 'store'])->name('store');
        Route::get('/{timesheet}', [TimesheetController::class, 'show'])->name('show');
        Route::get('/{timesheet}/edit', [TimesheetController::class, 'edit'])->name('edit');
        Route::patch('/{timesheet}', [TimesheetController::class, 'update'])->name('update');
        Route::delete('/{timesheet}', [TimesheetController::class, 'destroy'])->name('destroy');

        // Approval Actions
        Route::post('/{timesheet}/submit', [TimesheetController::class, 'submitForApproval'])
            ->name('submit');
        Route::post('/{timesheet}/approve', [TimesheetController::class, 'approve'])
            ->middleware('role:admin|hr|project_manager|site_manager|foreman')
            ->name('approve');
        Route::post('/{timesheet}/reject', [TimesheetController::class, 'reject'])
            ->middleware('role:admin|hr|project_manager|site_manager|foreman')
            ->name('reject');
        Route::post('/bulk-approve', [TimesheetController::class, 'bulkApprove'])
            ->middleware('role:admin|hr|project_manager|site_manager|foreman')
            ->name('bulk-approve');

        // QR Code Entry
        Route::post('/qr-entry', [TimesheetController::class, 'qrEntry'])->name('qr-entry');

        // Reports
        Route::get('/reports/generate', [TimesheetController::class, 'report'])->name('reports.generate');
        Route::get('/export', [TimesheetController::class, 'export'])->name('export');
    });

    // Timesheet Bulk Entry Routes (Toplu Puantaj Giriş Sistemi)
    Route::prefix('timesheets/bulk')->name('timesheets.bulk.')->group(function () {
        // Toplu Giriş (Ana Ekran)
        Route::get('/entry', [TimesheetBulkController::class, 'bulkEntry'])
            ->middleware('role:admin|hr|project_manager|site_manager|foreman')
            ->name('entry');
        Route::post('/store', [TimesheetBulkController::class, 'bulkStore'])
            ->middleware('role:admin|hr|project_manager|site_manager|foreman')
            ->name('store');

        // Kayıt Silme
        Route::delete('/{timesheet}', [TimesheetBulkController::class, 'destroy'])->name('destroy');

        // Raporlar
        Route::get('/reports/monthly', [TimesheetBulkController::class, 'monthlyReport'])
            ->name('reports.monthly');
    });

    // Timesheet V2 Approval Routes (Puantaj Onay Sistemi)
    Route::prefix('timesheets-v2')->name('timesheets-v2.')->middleware('auth')->group(function () {
        // Onay bekleyen puantajlar
        Route::get('/pending-approvals', [TimesheetController::class, 'pendingApprovals'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('pending-approvals');

        // Onay istatistikleri (API)
        Route::get('/approval-stats', [TimesheetController::class, 'approvalStats'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('approval-stats');

        // Aylık toplu onay
        Route::post('/approve-monthly', [TimesheetController::class, 'approveMonthly'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('approve-monthly');

        // Tekil işlemler
        Route::post('/{timesheet}/submit', [TimesheetController::class, 'submit'])
            ->name('submit');

        Route::post('/{timesheet}/approve', [TimesheetController::class, 'approve'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('approve');

        Route::post('/{timesheet}/reject', [TimesheetController::class, 'reject'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('reject');

        // İK müdahalesi (sadece admin ve hr)
        Route::post('/{timesheet}/hr-override', [TimesheetController::class, 'hrOverride'])
            ->middleware('role:admin|hr')
            ->name('hr-override');
    });

    // Shift Management Routes (Vardiya Yönetimi)
    Route::prefix('shifts')->name('shifts.')
        ->middleware('role:admin|hr|project_manager')
        ->group(function () {
            Route::get('/', [ShiftController::class, 'index'])->name('index');
            Route::get('/create', [ShiftController::class, 'create'])->name('create');
            Route::post('/', [ShiftController::class, 'store'])->name('store');
            Route::get('/{shift}', [ShiftController::class, 'show'])->name('show');
            Route::get('/{shift}/edit', [ShiftController::class, 'edit'])->name('edit');
            Route::patch('/{shift}', [ShiftController::class, 'update'])->name('update');
            Route::delete('/{shift}', [ShiftController::class, 'destroy'])->name('destroy');

            // API
            Route::get('/api/active', [ShiftController::class, 'getActive'])->name('api.active');
        });

    // Timesheet Approval Management Routes
    Route::prefix('timesheet-approvals')->name('timesheet-approvals.')->group(function () {
        Route::get('/', [TimesheetApprovalController::class, 'index'])->name('index');
        Route::get('/{approval}', [TimesheetApprovalController::class, 'show'])->name('show');

        // Approval Actions
        Route::post('/{approval}/approve', [TimesheetApprovalController::class, 'approve'])
            ->name('approve');
        Route::post('/{approval}/reject', [TimesheetApprovalController::class, 'reject'])
            ->name('reject');
        Route::post('/{approval}/request-revision', [TimesheetApprovalController::class, 'requestRevision'])
            ->name('request-revision');

        // Bulk Actions
        Route::post('/bulk-approve', [TimesheetApprovalController::class, 'bulkApprove'])
            ->name('bulk-approve');
        Route::post('/bulk-reject', [TimesheetApprovalController::class, 'bulkReject'])
            ->name('bulk-reject');

        // Mobile/API Routes
        Route::get('/queue/mobile', [TimesheetApprovalController::class, 'queue'])
            ->name('queue');
        Route::get('/statistics', [TimesheetApprovalController::class, 'statistics'])
            ->name('statistics');
        Route::get('/export', [TimesheetApprovalController::class, 'export'])
            ->name('export');
    });

    // Project Management Routes
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::get('/create', [ProjectController::class, 'create'])
            ->middleware('role:admin|hr|project_manager')
            ->name('create');
        Route::post('/', [ProjectController::class, 'store'])
            ->middleware('role:admin|hr|project_manager')
            ->name('store');
        Route::get('/{project}', [ProjectController::class, 'show'])->name('show');
        Route::get('/{project}/edit', [ProjectController::class, 'edit'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('edit');
        Route::put('/{project}', [ProjectController::class, 'update'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('update');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])
            ->middleware('role:admin|hr')
            ->name('destroy');

        // Project Actions
        Route::post('/{project}/assign-employee', [ProjectController::class, 'assignEmployee'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('assign-employee');
        Route::delete('/{project}/remove-employee', [ProjectController::class, 'removeEmployee'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('remove-employee');
        Route::post('/{project}/create-department', [ProjectController::class, 'createDepartment'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('create-department');
        Route::patch('/{project}/status', [ProjectController::class, 'updateStatus'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('update-status');

        // Subcontractor Actions
        Route::post('/{project}/assign-subcontractor', [ProjectController::class, 'assignSubcontractor'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('assign-subcontractor');
        Route::delete('/{project}/remove-subcontractor', [ProjectController::class, 'removeSubcontractor'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('remove-subcontractor');
        Route::patch('/{project}/update-subcontractor', [ProjectController::class, 'updateSubcontractor'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('update-subcontractor');

        // Project Analytics
        Route::get('/{project}/dashboard', [ProjectController::class, 'dashboard'])->name('dashboard');
        Route::get('/{project}/reports', [ProjectController::class, 'report'])->name('reports');
        Route::get('/{project}/export', [ProjectController::class, 'export'])->name('export');
    });

    // Department Management Routes
    Route::prefix('departments')->name('departments.')->middleware('role:admin|hr|project_manager|site_manager|foreman')->group(function () {
        Route::get('/', [DepartmentController::class, 'index'])->name('index');
        Route::get('/create', [DepartmentController::class, 'create'])->name('create');
        Route::post('/', [DepartmentController::class, 'store'])->name('store');
        Route::get('/{department}', [DepartmentController::class, 'show'])->name('show');
        Route::get('/{department}/edit', [DepartmentController::class, 'edit'])->name('edit');
        Route::patch('/{department}', [DepartmentController::class, 'update'])->name('update');
        Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('destroy');

        // Department Actions
        Route::patch('/{department}/status', [DepartmentController::class, 'updateStatus'])->name('update-status');
        Route::post('/{department}/assign-supervisor', [DepartmentController::class, 'assignSupervisor'])->name('assign-supervisor');
    });

    // Purchasing Request Management Routes
    Route::prefix('purchasing-requests')->name('purchasing-requests.')->group(function () {
        Route::get('/', [PurchasingRequestController::class, 'index'])->name('index');
        Route::get('/create', [PurchasingRequestController::class, 'create'])
            ->middleware('role:admin|hr|project_manager|site_manager|foreman')
            ->name('create');
        Route::post('/', [PurchasingRequestController::class, 'store'])
            ->middleware('role:admin|hr|project_manager|site_manager|foreman')
            ->name('store');
        Route::get('/{purchasingRequest}', [PurchasingRequestController::class, 'show'])->name('show');
        Route::get('/{purchasingRequest}/edit', [PurchasingRequestController::class, 'edit'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('edit');
        Route::put('/{purchasingRequest}', [PurchasingRequestController::class, 'update'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('update');
        Route::delete('/{purchasingRequest}', [PurchasingRequestController::class, 'destroy'])
            ->middleware('role:admin|hr|project_manager')
            ->name('destroy');

        // Purchasing Actions
        Route::post('/{purchasingRequest}/submit', [PurchasingRequestController::class, 'submit'])
            ->name('submit');
        Route::post('/{purchasingRequest}/approve-supervisor', [PurchasingRequestController::class, 'approveBySupervisor'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('approve-supervisor');
        Route::post('/{purchasingRequest}/approve-manager', [PurchasingRequestController::class, 'approveByManager'])
            ->middleware('role:admin|hr|project_manager')
            ->name('approve-manager');
        Route::post('/{purchasingRequest}/reject', [PurchasingRequestController::class, 'reject'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('reject');
        Route::post('/{purchasingRequest}/cancel', [PurchasingRequestController::class, 'cancel'])
            ->name('cancel');
        Route::post('/{purchasingRequest}/mark-as-delivered', [PurchasingRequestController::class, 'markAsDelivered'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('mark-as-delivered');
    });

    // Material Management Routes
    Route::prefix('materials')->name('materials.')->middleware('role:admin|hr|project_manager|site_manager')->group(function () {
        Route::get('/', [MaterialController::class, 'index'])->name('index');
        Route::get('/create', [MaterialController::class, 'create'])->name('create');
        Route::post('/', [MaterialController::class, 'store'])->name('store');
        Route::get('/{material}', [MaterialController::class, 'show'])->name('show');
        Route::get('/{material}/edit', [MaterialController::class, 'edit'])->name('edit');
        Route::put('/{material}', [MaterialController::class, 'update'])->name('update');
        Route::delete('/{material}', [MaterialController::class, 'destroy'])
            ->middleware('role:admin|hr')
            ->name('destroy');

        // API endpoint for active materials (used in purchasing request forms)
        Route::get('/api/active', [MaterialController::class, 'getActive'])->name('api.active');
    });

    // Subcontractor Management Routes
    Route::prefix('subcontractors')->name('subcontractors.')->group(function () {
        Route::get('/', [SubcontractorController::class, 'index'])->name('index');
        Route::get('/create', [SubcontractorController::class, 'create'])
            ->middleware('role:admin|hr|project_manager')
            ->name('create');
        Route::post('/', [SubcontractorController::class, 'store'])
            ->middleware('role:admin|hr|project_manager')
            ->name('store');
        Route::get('/{subcontractor}', [SubcontractorController::class, 'show'])->name('show');
        Route::get('/{subcontractor}/edit', [SubcontractorController::class, 'edit'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('edit');
        Route::patch('/{subcontractor}', [SubcontractorController::class, 'update'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('update');
        Route::delete('/{subcontractor}', [SubcontractorController::class, 'destroy'])
            ->middleware('role:admin|hr|project_manager')
            ->name('destroy');
    });

    // Daily Report Management Routes
    Route::prefix('daily-reports')->name('daily-reports.')->group(function () {
        Route::get('/', [DailyReportController::class, 'index'])->name('index');
        Route::get('/create', [DailyReportController::class, 'create'])
            ->middleware('role:admin|project_manager|site_manager|foreman')
            ->name('create');
        Route::post('/', [DailyReportController::class, 'store'])
            ->middleware('role:admin|project_manager|site_manager|foreman')
            ->name('store');
        Route::get('/{dailyReport}', [DailyReportController::class, 'show'])->name('show');
        Route::get('/{dailyReport}/edit', [DailyReportController::class, 'edit'])
            ->middleware('role:admin|project_manager|site_manager|foreman')
            ->name('edit');
        Route::put('/{dailyReport}', [DailyReportController::class, 'update'])
            ->middleware('role:admin|project_manager|site_manager|foreman')
            ->name('update');
        Route::delete('/{dailyReport}', [DailyReportController::class, 'destroy'])
            ->middleware('role:admin|project_manager|site_manager|foreman')
            ->name('destroy');

        // Daily Report Actions
        Route::post('/{dailyReport}/submit', [DailyReportController::class, 'submit'])
            ->middleware('role:admin|project_manager|site_manager|foreman')
            ->name('submit');
        Route::post('/{dailyReport}/approve', [DailyReportController::class, 'approve'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('approve');
        Route::post('/{dailyReport}/reject', [DailyReportController::class, 'reject'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('reject');
    });

    // Leave Request Management Routes
    Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
        Route::get('/', [LeaveRequestController::class, 'index'])->name('index');
        Route::get('/create', [LeaveRequestController::class, 'create'])->name('create');
        Route::post('/', [LeaveRequestController::class, 'store'])->name('store');
        Route::get('/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('show');
        Route::get('/{leaveRequest}/edit', [LeaveRequestController::class, 'edit'])->name('edit');
        Route::patch('/{leaveRequest}', [LeaveRequestController::class, 'update'])->name('update');
        Route::delete('/{leaveRequest}', [LeaveRequestController::class, 'destroy'])->name('destroy');

        // Leave Actions
        Route::post('/{leaveRequest}/submit', [LeaveRequestController::class, 'submit'])->name('submit');
        Route::post('/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('approve');
        Route::post('/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('reject');
        Route::post('/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])->name('cancel');

        // Leave Management
        Route::get('/calendar/view', [LeaveRequestController::class, 'calendar'])->name('calendar');
        Route::post('/bulk-approve', [LeaveRequestController::class, 'bulkApprove'])
            ->middleware('role:admin|hr|project_manager')
            ->name('bulk-approve');

        // API - Get employee project settings
        Route::get('/api/employee-project-settings', [LeaveRequestController::class, 'getEmployeeProjectSettings'])
            ->name('api.employee-project-settings');
    });

    // İzin Yönetimi Rotaları (System Admin + HR)
    Route::middleware(['auth', 'leave.management'])->prefix('leave-management')->name('leave-management.')->group(function () {

        // İzin Parametreleri
        Route::prefix('parameters')->name('parameters.')->group(function () {
            Route::get('/', [LeaveParameterController::class, 'index'])->name('index');
            Route::get('/create', [LeaveParameterController::class, 'create'])
                ->middleware('can:create-leave-parameters')->name('create');
            Route::post('/', [LeaveParameterController::class, 'store'])
                ->middleware('can:create-leave-parameters')->name('store');
            Route::get('/{parameter}', [LeaveParameterController::class, 'show'])->name('show');
            Route::get('/{parameter}/edit', [LeaveParameterController::class, 'edit'])
                ->middleware('can:edit-leave-parameters')->name('edit');
            Route::patch('/{parameter}', [LeaveParameterController::class, 'update'])
                ->middleware('can:edit-leave-parameters')->name('update');
            Route::delete('/{parameter}', [LeaveParameterController::class, 'destroy'])
                ->middleware('can:delete-leave-parameters')->name('destroy');
        });

        // İzin Türleri
        Route::prefix('types')->name('types.')->group(function () {
            Route::get('/', [LeaveTypeController::class, 'index'])->name('index');
            Route::get('/create', [LeaveTypeController::class, 'create'])
                ->middleware('can:create-leave-types')->name('create');
            Route::post('/', [LeaveTypeController::class, 'store'])
                ->middleware('can:create-leave-types')->name('store');
            Route::get('/{leaveType}', [LeaveTypeController::class, 'show'])->name('show');
            Route::get('/{leaveType}/edit', [LeaveTypeController::class, 'edit'])
                ->middleware('can:edit-leave-types')->name('edit');
            Route::patch('/{leaveType}', [LeaveTypeController::class, 'update'])
                ->middleware('can:edit-leave-types')->name('update');
            Route::delete('/{leaveType}', [LeaveTypeController::class, 'destroy'])
                ->middleware('can:delete-leave-types')->name('destroy');
        });

        // İzin Hesaplamaları
        Route::prefix('calculations')->name('calculations.')->group(function () {
            Route::get('/', [LeaveCalculationController::class, 'index'])->name('index');
            Route::post('/recalculate/{employee}', [LeaveCalculationController::class, 'recalculate'])
                ->middleware('can:recalculate-employee-leaves')->name('recalculate');
            Route::post('/bulk-recalculate', [LeaveCalculationController::class, 'bulkRecalculate'])
                ->middleware('can:recalculate-employee-leaves')->name('bulk-recalculate');
            Route::get('/{calculation}', [LeaveCalculationController::class, 'show'])->name('show');
            Route::patch('/{calculation}/approve', [LeaveCalculationController::class, 'approve'])
                ->middleware('can:edit-leave-calculations')->name('approve');
        });

        // İzin Bakiyesi Logları
        Route::prefix('balance-logs')->name('balance-logs.')->group(function () {
            Route::get('/', [LeaveBalanceLogController::class, 'index'])->name('index');
            Route::get('/employee/{employee}', [LeaveBalanceLogController::class, 'byEmployee'])->name('by-employee');
            Route::post('/adjust', [LeaveBalanceLogController::class, 'adjust'])
                ->middleware('can:adjust-leave-balances')->name('adjust');
        });

        // İzin Raporları
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [LeaveReportController::class, 'index'])->name('index');
            Route::get('/compliance', [LeaveReportController::class, 'compliance'])->name('compliance');
            Route::get('/usage-analysis', [LeaveReportController::class, 'usageAnalysis'])->name('usage-analysis');
            Route::post('/export', [LeaveReportController::class, 'export'])
                ->middleware('can:export-leave-data')->name('export');
        });
    });

    // Sistem Yönetimi Rotaları (Sadece System Admin)
    Route::middleware(['auth', 'system.admin'])->prefix('system')->name('system.')->group(function () {
        Route::get('/settings', [SystemController::class, 'settings'])->name('settings');
        Route::patch('/settings', [SystemController::class, 'updateSettings'])->name('settings.update');
        Route::get('/logs', [SystemController::class, 'logs'])->name('logs');
        Route::post('/backup', [SystemController::class, 'backup'])->name('backup');
        Route::get('/users', [SystemController::class, 'users'])->name('users');
        Route::patch('/users/{user}/role', [SystemController::class, 'updateUserRole'])->name('users.update-role');
    });

    // Document Management Routes
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::get('/create', [DocumentController::class, 'create'])->name('create');
        Route::post('/', [DocumentController::class, 'store'])->name('store');
        Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
        Route::get('/{document}/edit', [DocumentController::class, 'edit'])->name('edit');
        Route::patch('/{document}', [DocumentController::class, 'update'])->name('update');
        Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy');

        // Document Actions
        Route::get('/{document}/download', [DocumentController::class, 'download'])->name('download');
        Route::post('/{document}/verify', [DocumentController::class, 'verify'])
            ->middleware('role:admin|hr|project_manager')
            ->name('verify');
        Route::post('/{document}/reject', [DocumentController::class, 'reject'])
            ->middleware('role:admin|hr|project_manager')
            ->name('reject');
        Route::post('/{document}/archive', [DocumentController::class, 'archive'])
            ->middleware('role:admin|hr|project_manager')
            ->name('archive');
        Route::post('/{document}/restore', [DocumentController::class, 'restore'])
            ->middleware('role:admin|hr|project_manager')
            ->name('restore');

        // Document Management
        Route::get('/expiring/list', [DocumentController::class, 'expiring'])
            ->middleware('role:admin|hr|project_manager')
            ->name('expiring');
        Route::post('/bulk-action', [DocumentController::class, 'bulkAction'])
            ->middleware('role:admin|hr|project_manager')
            ->name('bulk-action');
    });

    // Notification Management Routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/settings', [NotificationController::class, 'settings'])->name('settings');
        Route::patch('/settings', [NotificationController::class, 'updateSettings'])->name('settings.update');

        // Notification Actions
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::post('/{id}/mark-unread', [NotificationController::class, 'markAsUnread'])->name('mark-unread');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');

        // Bulk Actions
        Route::post('/bulk-mark-read', [NotificationController::class, 'bulkMarkAsRead'])->name('bulk-mark-read');
        Route::post('/bulk-delete', [NotificationController::class, 'bulkDelete'])->name('bulk-delete');

        // API Routes for frontend
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::get('/recent', [NotificationController::class, 'recent'])->name('recent');

        // Admin/Manager Notification Management
        Route::middleware('role:admin|project_manager|site_manager')->group(function () {
            Route::post('/send', [NotificationController::class, 'send'])->name('send');
            Route::get('/templates', [NotificationController::class, 'templates'])->name('templates');
            Route::post('/schedule-bulk', [NotificationController::class, 'scheduleBulk'])
                ->middleware('role:admin|project_manager')
                ->name('schedule-bulk');
        });

        // Test and Statistics
        Route::post('/send-test', [NotificationController::class, 'sendTest'])->name('send-test');
        Route::get('/statistics', [NotificationController::class, 'statistics'])->name('statistics');
        Route::get('/export', [NotificationController::class, 'export'])->name('export');
    });

    // File Upload Management Routes
    Route::prefix('files')->name('files.')->group(function () {
        // File Upload
        Route::post('/upload', [FileUploadController::class, 'upload'])->name('upload');
        Route::post('/upload-multiple', [FileUploadController::class, 'uploadMultiple'])->name('upload-multiple');
        Route::post('/upload-base64', [FileUploadController::class, 'uploadBase64'])->name('upload-base64');

        // File Management
        Route::get('/{id}/info', [FileUploadController::class, 'getFileInfo'])->name('info');
        Route::get('/{id}/download', [FileUploadController::class, 'download'])->name('download');
        Route::delete('/{id}', [FileUploadController::class, 'delete'])->name('delete');

        // File Processing
        Route::post('/{id}/generate-thumbnail', [FileUploadController::class, 'generateThumbnail'])->name('generate-thumbnail');
        Route::get('/upload-progress', [FileUploadController::class, 'getUploadProgress'])->name('upload-progress');

        // Security and Validation
        Route::post('/validate-signature', [FileUploadController::class, 'validateFileSignature'])->name('validate-signature');
        Route::post('/scan-virus', [FileUploadController::class, 'scanFile'])->name('scan-virus');
    });

    // Reporting Routes
    Route::prefix('reports')->name('reports.')->middleware('role:admin|hr|project_manager|site_manager')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');

        // Employee Reports
        Route::get('/employee-attendance', [ReportController::class, 'employeeAttendance'])->name('employee-attendance');
        Route::get('/employee-performance', [ReportController::class, 'employeePerformance'])->name('employee-performance');

        // Project Reports
        Route::get('/project-progress', [ReportController::class, 'projectProgress'])->name('project-progress');
        Route::get('/financial-summary', [ReportController::class, 'financialSummary'])
            ->middleware('role:admin|project_manager')
            ->name('financial-summary');

        // Leave Reports
        Route::get('/leave-report', [ReportController::class, 'leaveReport'])->name('leave-report');

        // Export Routes
        Route::post('/export-pdf', [ReportController::class, 'exportPdf'])->name('export-pdf');
        Route::post('/export-excel', [ReportController::class, 'exportExcel'])->name('export-excel');
    });

    // QR Code Management Routes
    Route::prefix('qr')->name('qr.')->group(function () {
        Route::get('/', [QRCodeController::class, 'index'])->name('index');

        // QR Code Generation
        Route::post('/generate-employee', [QRCodeController::class, 'generateEmployeeQR'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('generate-employee');
        Route::post('/generate-project', [QRCodeController::class, 'generateProjectQR'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('generate-project');
        Route::post('/bulk-generate', [QRCodeController::class, 'bulkGenerate'])
            ->middleware('role:admin|hr|project_manager')
            ->name('bulk-generate');

        // QR Code Scanning
        Route::post('/scan', [QRCodeController::class, 'scanQR'])->name('scan');

        // QR Code Management
        Route::get('/download/{type}/{token}', [QRCodeController::class, 'downloadQR'])->name('download');
        Route::get('/scan-history', [QRCodeController::class, 'scanHistory'])->name('scan-history');
        Route::delete('/delete', [QRCodeController::class, 'deleteQR'])->name('delete');
    });

    // API Data Routes (for AJAX calls)
    Route::prefix('api/data')->name('api.data.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'getDashboardData'])->name('dashboard');
        Route::get('/employees/search', [EmployeeController::class, 'search'])->name('employees.search');
        Route::get('/projects/search', [ProjectController::class, 'search'])->name('projects.search');
        Route::get('/departments/{project}', [DepartmentController::class, 'getByProject'])->name('departments.by-project');
    });

    // Admin Only Routes
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        // System Settings
        Route::get('/settings', function () {
            return Inertia::render('Admin/Settings');
        })->name('settings');

        // User Management
        Route::get('/users', function () {
            return Inertia::render('Admin/Users');
        })->name('users');

        // System Logs
        Route::get('/logs', function () {
            return Inertia::render('Admin/Logs');
        })->name('logs');

        // Database Backup
        Route::get('/backup', function () {
            return Inertia::render('Admin/Backup');
        })->name('backup');
    });

    // Mobile/Kiosk Routes
    Route::prefix('kiosk')->name('kiosk.')->group(function () {
        Route::get('/', function () {
            return Inertia::render('Kiosk/Dashboard');
        })->name('dashboard');

        Route::get('/checkin', function () {
            return Inertia::render('Kiosk/CheckIn');
        })->name('checkin');

        Route::get('/checkout', function () {
            return Inertia::render('Kiosk/CheckOut');
        })->name('checkout');
    });

    // Help and Documentation
    Route::prefix('help')->name('help.')->group(function () {
        Route::get('/', function () {
            return Inertia::render('Help/Index');
        })->name('index');

        Route::get('/user-guide', function () {
            return Inertia::render('Help/UserGuide');
        })->name('user-guide');

        Route::get('/faq', function () {
            return Inertia::render('Help/FAQ');
        })->name('faq');
    });
});

// Project Management Routes (Faz 1)
Route::middleware(['auth', 'verified'])->prefix('projects')->name('projects.')->group(function () {

    // Project Structures (Bloklar/Binalar)
    Route::prefix('{project}/structures')->name('structures.')->group(function () {
        Route::get('/', [ProjectStructureController::class, 'index'])->name('index');
        Route::get('/create', [ProjectStructureController::class, 'create'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('create');
        Route::post('/', [ProjectStructureController::class, 'store'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('store');
        Route::get('/{structure}', [ProjectStructureController::class, 'show'])->name('show');
        Route::get('/{structure}/edit', [ProjectStructureController::class, 'edit'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('edit');
        Route::put('/{structure}', [ProjectStructureController::class, 'update'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('update');
        Route::delete('/{structure}', [ProjectStructureController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');
    });

    // Project Floors (Katlar)
    Route::prefix('structures/{structure}/floors')->name('structures.floors.')->group(function () {
        Route::get('/', [ProjectFloorController::class, 'index'])->name('index');
        Route::get('/create', [ProjectFloorController::class, 'create'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('create');
        Route::post('/', [ProjectFloorController::class, 'store'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('store');
        Route::get('/{floor}', [ProjectFloorController::class, 'show'])->name('show');
        Route::get('/{floor}/edit', [ProjectFloorController::class, 'edit'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('edit');
        Route::put('/{floor}', [ProjectFloorController::class, 'update'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('update');
        Route::delete('/{floor}', [ProjectFloorController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');
    });

    // Project Units (Daireler/Birimler)
    Route::prefix('floors/{floor}/units')->name('floors.units.')->group(function () {
        Route::get('/', [ProjectUnitController::class, 'index'])->name('index');
        Route::get('/create', [ProjectUnitController::class, 'create'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('create');
        Route::post('/', [ProjectUnitController::class, 'store'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('store');
        Route::get('/{unit}', [ProjectUnitController::class, 'show'])->name('show');
        Route::get('/{unit}/edit', [ProjectUnitController::class, 'edit'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('edit');
        Route::put('/{unit}', [ProjectUnitController::class, 'update'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('update');
        Route::delete('/{unit}', [ProjectUnitController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');
    });

    // Work Item Assignments (İş Atamaları)
    Route::prefix('{project}/work-assignments')->name('work-assignments.')->group(function () {
        Route::get('/', [WorkItemAssignmentController::class, 'index'])->name('index');
        Route::get('/create', [WorkItemAssignmentController::class, 'create'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('create');
        Route::post('/', [WorkItemAssignmentController::class, 'store'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('store');
        Route::get('/{assignment}', [WorkItemAssignmentController::class, 'show'])->name('show');
        Route::get('/{assignment}/edit', [WorkItemAssignmentController::class, 'edit'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('edit');
        Route::put('/{assignment}', [WorkItemAssignmentController::class, 'update'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('update');
        Route::delete('/{assignment}', [WorkItemAssignmentController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');
    });
});

// Public API endpoints (for mobile app, external integrations)
Route::prefix('public-api')->name('public-api.')->group(function () {
    // These would typically be in api.php but placing here for completeness
    Route::post('/qr-check', [QRCodeController::class, 'publicQrCheck'])->name('qr-check');
    Route::get('/employee-info/{code}', [EmployeeController::class, 'publicInfo'])->name('employee-info');
});

// HOLIDAY ROUTES - Eklendi
Route::middleware(['auth'])->prefix('api/holidays')->name('api.holidays.')->group(function () {
    Route::get('/', [App\Http\Controllers\HolidayController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\HolidayController::class, 'store'])->name('store');
    Route::put('/{holiday}', [App\Http\Controllers\HolidayController::class, 'update'])->name('update');
    Route::delete('/{holiday}', [App\Http\Controllers\HolidayController::class, 'destroy'])->name('destroy');
    Route::get('/range', [App\Http\Controllers\HolidayController::class, 'getHolidaysInRange'])->name('range');
    Route::post('/seed-2025', [App\Http\Controllers\HolidayController::class, 'seed2025Holidays'])->name('seed-2025');
});

// PROGRESS PAYMENT ROUTES (Hakediş Takibi)
Route::middleware(['auth', 'verified'])->prefix('progress-payments')->name('progress-payments.')->group(function () {
    Route::get('/dashboard', [ProgressPaymentController::class, 'dashboard'])->name('dashboard');
    Route::get('/', [ProgressPaymentController::class, 'index'])->name('index');
    Route::get('/create', [ProgressPaymentController::class, 'create'])
        ->middleware('role:admin|project_manager')
        ->name('create');

    // Metraj Aşımı Raporu (MUST be before /{progressPayment})
    Route::get('/quantity-overrun-report', [ProgressPaymentController::class, 'quantityOverrunReport'])
        ->middleware('role:admin|project_manager')
        ->name('quantity-overrun-report');

    Route::post('/', [ProgressPaymentController::class, 'store'])
        ->middleware('role:admin|project_manager')
        ->name('store');
    Route::get('/{progressPayment}', [ProgressPaymentController::class, 'show'])->name('show');
    Route::get('/{progressPayment}/edit', [ProgressPaymentController::class, 'edit'])
        ->middleware('role:admin|project_manager')
        ->name('edit');
    Route::put('/{progressPayment}', [ProgressPaymentController::class, 'update'])
        ->middleware('role:admin|project_manager')
        ->name('update');
    Route::delete('/{progressPayment}', [ProgressPaymentController::class, 'destroy'])
        ->middleware('role:admin|project_manager')
        ->name('destroy');

    // Approval actions
    Route::post('/{progressPayment}/approve', [ProgressPaymentController::class, 'approve'])
        ->middleware('role:admin|project_manager')
        ->name('approve');
    Route::post('/{progressPayment}/mark-as-paid', [ProgressPaymentController::class, 'markAsPaid'])
        ->middleware('role:admin|accountant')
        ->name('mark-as-paid');
});

// PROGRESS TRACKING API ROUTES
Route::middleware(['auth'])->prefix('api/progress')->name('api.progress.')->group(function () {
    Route::get('/project/{project}', [ProgressPaymentController::class, 'projectProgress'])->name('project');
    Route::get('/structure/{structure}', [ProgressPaymentController::class, 'structureProgress'])->name('structure');
    Route::get('/floor/{floor}', [ProgressPaymentController::class, 'floorProgress'])->name('floor');
});

// QUANTITY ROUTES (Keşif & Metraj Yönetimi - Faz 2)
Route::middleware(['auth', 'verified'])->prefix('quantities')->name('quantities.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\QuantityController::class, 'dashboard'])->name('dashboard');
    Route::get('/', [App\Http\Controllers\QuantityController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\QuantityController::class, 'create'])
        ->middleware('role:admin|project_manager')
        ->name('create');

    // Search for hakediş integration (MOVED BEFORE /{quantity})
    Route::get('/search', [App\Http\Controllers\QuantityController::class, 'search'])
        ->name('search');

    Route::post('/', [App\Http\Controllers\QuantityController::class, 'store'])
        ->middleware('role:admin|project_manager')
        ->name('store');
    Route::get('/{quantity}', [App\Http\Controllers\QuantityController::class, 'show'])->name('show');
    Route::get('/{quantity}/edit', [App\Http\Controllers\QuantityController::class, 'edit'])
        ->middleware('role:admin|project_manager')
        ->name('edit');
    Route::put('/{quantity}', [App\Http\Controllers\QuantityController::class, 'update'])
        ->middleware('role:admin|project_manager')
        ->name('update');
    Route::delete('/{quantity}', [App\Http\Controllers\QuantityController::class, 'destroy'])
        ->middleware('role:admin|project_manager')
        ->name('destroy');

    // Verification and approval actions
    Route::post('/{quantity}/verify', [App\Http\Controllers\QuantityController::class, 'verify'])
        ->middleware('role:admin|project_manager')
        ->name('verify');
    Route::post('/{quantity}/approve', [App\Http\Controllers\QuantityController::class, 'approve'])
        ->middleware('role:admin|project_manager')
        ->name('approve');
});

// FINANCIAL MANAGEMENT ROUTES (Finansal Yönetim - Faz 2)
Route::middleware(['auth', 'verified'])->prefix('financial')->name('financial.')->group(function () {
    // Financial Dashboard
    Route::get('/dashboard', [FinancialController::class, 'dashboard'])
        ->middleware('role:admin|project_manager|hr')
        ->name('dashboard');

    // Transactions
    Route::get('/', [FinancialController::class, 'index'])
        ->middleware('role:admin|project_manager|hr')
        ->name('index');
    Route::get('/create', [FinancialController::class, 'create'])
        ->middleware('role:admin|project_manager|hr')
        ->name('create');
    Route::post('/', [FinancialController::class, 'store'])
        ->middleware('role:admin|project_manager|hr')
        ->name('store');
    Route::get('/{transaction}', [FinancialController::class, 'show'])
        ->middleware('role:admin|project_manager|hr')
        ->name('show');
    Route::get('/{transaction}/edit', [FinancialController::class, 'edit'])
        ->middleware('role:admin|project_manager|hr')
        ->name('edit');
    Route::put('/{transaction}', [FinancialController::class, 'update'])
        ->middleware('role:admin|project_manager|hr')
        ->name('update');
    Route::delete('/{transaction}', [FinancialController::class, 'destroy'])
        ->middleware('role:admin|project_manager|hr')
        ->name('destroy');

    // Reports
    Route::get('/reports/profit-loss', [FinancialController::class, 'profitLoss'])
        ->middleware('role:admin|project_manager|hr')
        ->name('reports.profit-loss');
});

// CONTRACT MANAGEMENT ROUTES (Sözleşme Yönetimi - Faz 2)
Route::middleware(['auth', 'verified'])->prefix('contracts')->name('contracts.')->group(function () {
    // Contract Dashboard
    Route::get('/dashboard', [ContractController::class, 'dashboard'])
        ->middleware('role:admin|project_manager')
        ->name('dashboard');

    // Contract CRUD
    Route::get('/', [ContractController::class, 'index'])
        ->middleware('role:admin|project_manager')
        ->name('index');
    Route::get('/create', [ContractController::class, 'create'])
        ->middleware('role:admin|project_manager')
        ->name('create');
    Route::post('/', [ContractController::class, 'store'])
        ->middleware('role:admin|project_manager')
        ->name('store');
    Route::get('/{contract}', [ContractController::class, 'show'])
        ->middleware('role:admin|project_manager')
        ->name('show');
    Route::get('/{contract}/edit', [ContractController::class, 'edit'])
        ->middleware('role:admin|project_manager')
        ->name('edit');
    Route::put('/{contract}', [ContractController::class, 'update'])
        ->middleware('role:admin|project_manager')
        ->name('update');
    Route::delete('/{contract}', [ContractController::class, 'destroy'])
        ->middleware('role:admin|project_manager')
        ->name('destroy');

    // Contract Actions
    Route::post('/{contract}/activate', [ContractController::class, 'activate'])
        ->middleware('role:admin|project_manager')
        ->name('activate');
    Route::post('/{contract}/terminate', [ContractController::class, 'terminate'])
        ->middleware('role:admin|project_manager')
        ->name('terminate');
    Route::post('/{contract}/complete', [ContractController::class, 'complete'])
        ->middleware('role:admin|project_manager')
        ->name('complete');
});

// CONSTRUCTION PERMITS ROUTES (İnşaat Ruhsat ve İzin Yönetimi - Faz 2)
Route::middleware(['auth', 'verified'])->prefix('construction-permits')->name('construction-permits.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\ConstructionPermitController::class, 'dashboard'])
        ->middleware('role:admin|project_manager')
        ->name('dashboard');

    // CRUD
    Route::get('/', [\App\Http\Controllers\ConstructionPermitController::class, 'index'])
        ->middleware('role:admin|project_manager|site_manager')
        ->name('index');
    Route::get('/create', [\App\Http\Controllers\ConstructionPermitController::class, 'create'])
        ->middleware('role:admin|project_manager')
        ->name('create');
    Route::post('/', [\App\Http\Controllers\ConstructionPermitController::class, 'store'])
        ->middleware('role:admin|project_manager')
        ->name('store');
    Route::get('/{constructionPermit}', [\App\Http\Controllers\ConstructionPermitController::class, 'show'])
        ->middleware('role:admin|project_manager|site_manager')
        ->name('show');
    Route::get('/{constructionPermit}/edit', [\App\Http\Controllers\ConstructionPermitController::class, 'edit'])
        ->middleware('role:admin|project_manager')
        ->name('edit');
    Route::put('/{constructionPermit}', [\App\Http\Controllers\ConstructionPermitController::class, 'update'])
        ->middleware('role:admin|project_manager')
        ->name('update');
    Route::delete('/{constructionPermit}', [\App\Http\Controllers\ConstructionPermitController::class, 'destroy'])
        ->middleware('role:admin|project_manager')
        ->name('destroy');

    // Document Management
    Route::post('/{constructionPermit}/documents', [\App\Http\Controllers\ConstructionPermitController::class, 'uploadDocument'])
        ->middleware('role:admin|project_manager')
        ->name('documents.upload');
    Route::delete('/{constructionPermit}/documents/{index}', [\App\Http\Controllers\ConstructionPermitController::class, 'deleteDocument'])
        ->middleware('role:admin|project_manager')
        ->name('documents.delete');
});

// SALES & DEED MANAGEMENT ROUTES (Satış ve Tapu Yönetimi - Faz 3)
Route::middleware(['auth', 'verified'])->prefix('sales')->name('sales.')->group(function () {

    // API Routes for Cascade Dropdowns
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/projects/{project}/structures', [\App\Http\Controllers\Api\ProjectDataController::class, 'getStructures'])->name('structures');
        Route::get('/structures/{structure}/floors', [\App\Http\Controllers\Api\ProjectDataController::class, 'getFloors'])->name('floors');
        Route::get('/floors/{floor}/units', [\App\Http\Controllers\Api\ProjectDataController::class, 'getUnits'])->name('units');
        Route::get('/floors/{floor}/available-units', [\App\Http\Controllers\Api\ProjectDataController::class, 'getAvailableUnits'])->name('available-units');
    });

    // Customer Management
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [App\Http\Controllers\CustomerController::class, 'index'])
            ->middleware('role:admin|project_manager|sales_manager')
            ->name('index');
        Route::get('/create', [App\Http\Controllers\CustomerController::class, 'create'])
            ->middleware('role:admin|project_manager|sales_manager')
            ->name('create');
        Route::post('/', [App\Http\Controllers\CustomerController::class, 'store'])
            ->middleware('role:admin|project_manager|sales_manager')
            ->name('store');
        Route::get('/{customer}', [App\Http\Controllers\CustomerController::class, 'show'])
            ->middleware('role:admin|project_manager|sales_manager')
            ->name('show');
        Route::get('/{customer}/edit', [App\Http\Controllers\CustomerController::class, 'edit'])
            ->middleware('role:admin|project_manager|sales_manager')
            ->name('edit');
        Route::put('/{customer}', [App\Http\Controllers\CustomerController::class, 'update'])
            ->middleware('role:admin|project_manager|sales_manager')
            ->name('update');
        Route::delete('/{customer}', [App\Http\Controllers\CustomerController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');
    });

    // Unit Sales Management
    Route::prefix('unit-sales')->name('unit-sales.')->group(function () {
        Route::get('/', [App\Http\Controllers\UnitSaleController::class, 'index'])
            ->middleware('role:admin|project_manager|sales_manager')
            ->name('index');
        Route::get('/create', [App\Http\Controllers\UnitSaleController::class, 'create'])
            ->middleware('role:admin|project_manager|sales_manager')
            ->name('create');
        Route::post('/', [App\Http\Controllers\UnitSaleController::class, 'store'])
            ->middleware('role:admin|project_manager|sales_manager')
            ->name('store');
        Route::get('/{unitSale}', [App\Http\Controllers\UnitSaleController::class, 'show'])
            ->middleware('role:admin|project_manager|sales_manager')
            ->name('show');
        Route::get('/{unitSale}/edit', [App\Http\Controllers\UnitSaleController::class, 'edit'])
            ->middleware('role:admin|project_manager|sales_manager')
            ->name('edit');
        Route::put('/{unitSale}', [App\Http\Controllers\UnitSaleController::class, 'update'])
            ->middleware('role:admin|project_manager|sales_manager')
            ->name('update');
        Route::delete('/{unitSale}', [App\Http\Controllers\UnitSaleController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');
    });

    // Sale Payments Management
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [App\Http\Controllers\SalePaymentController::class, 'index'])
            ->middleware('role:admin|project_manager|sales_manager|accountant')
            ->name('index');
        Route::get('/create', [App\Http\Controllers\SalePaymentController::class, 'create'])
            ->middleware('role:admin|project_manager|sales_manager|accountant')
            ->name('create');
        Route::post('/', [App\Http\Controllers\SalePaymentController::class, 'store'])
            ->middleware('role:admin|project_manager|sales_manager|accountant')
            ->name('store');
        Route::get('/{payment}', [App\Http\Controllers\SalePaymentController::class, 'show'])
            ->middleware('role:admin|project_manager|sales_manager|accountant')
            ->name('show');
        Route::get('/{payment}/edit', [App\Http\Controllers\SalePaymentController::class, 'edit'])
            ->middleware('role:admin|project_manager|sales_manager|accountant')
            ->name('edit');
        Route::put('/{payment}', [App\Http\Controllers\SalePaymentController::class, 'update'])
            ->middleware('role:admin|project_manager|sales_manager|accountant')
            ->name('update');
        Route::delete('/{payment}', [App\Http\Controllers\SalePaymentController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');

        // Payment Actions
        Route::post('/{payment}/mark-as-paid', [App\Http\Controllers\SalePaymentController::class, 'markAsPaid'])
            ->middleware('role:admin|project_manager|sales_manager|accountant')
            ->name('mark-as-paid');
    });

    // Sales Status Visualization (Satış Durumu Görselleştirme)
    Route::prefix('sales-status')->name('sales-status.')->group(function () {
        Route::get('/', [App\Http\Controllers\SalesStatusController::class, 'index'])
            ->middleware('role:admin|project_manager|sales_manager')
            ->name('index');
        Route::get('/{project}', [App\Http\Controllers\SalesStatusController::class, 'show'])
            ->middleware('role:admin|project_manager|sales_manager')
            ->name('show');

        // API Routes
        Route::get('/api/structure/{structure}', [App\Http\Controllers\SalesStatusController::class, 'getStructureDetails'])
            ->middleware('role:admin|project_manager|sales_manager')
            ->name('api.structure');
        Route::get('/api/floor/{floor}/units', [App\Http\Controllers\SalesStatusController::class, 'getFloorUnits'])
            ->middleware('role:admin|project_manager|sales_manager')
            ->name('api.floor-units');
    });

    // Deed Management (Tapu Yönetimi)
    Route::prefix('unit-sales/{unitSale}/deed')->name('unit-sales.deed.')->group(function () {
        Route::post('/update-status', [App\Http\Controllers\UnitSaleController::class, 'updateDeedStatus'])
            ->middleware('role:admin|project_manager|sales_manager')
            ->name('update-status');
        Route::post('/upload-document', [App\Http\Controllers\UnitSaleController::class, 'uploadDeedDocument'])
            ->middleware('role:admin|project_manager|sales_manager')
            ->name('upload-document');
    });
});

// INSPECTION MANAGEMENT ROUTES (Yapı Denetim Yönetimi - Faz 2)
Route::middleware(['auth', 'verified'])->group(function () {

    // Inspection Companies (Denetim Kuruluşları)
    Route::prefix('inspection-companies')->name('inspection-companies.')->group(function () {
        Route::get('/', [App\Http\Controllers\InspectionCompanyController::class, 'index'])
            ->middleware('role:admin|project_manager')
            ->name('index');
        Route::get('/create', [App\Http\Controllers\InspectionCompanyController::class, 'create'])
            ->middleware('role:admin|project_manager')
            ->name('create');
        Route::post('/', [App\Http\Controllers\InspectionCompanyController::class, 'store'])
            ->middleware('role:admin|project_manager')
            ->name('store');
        Route::get('/{inspectionCompany}/edit', [App\Http\Controllers\InspectionCompanyController::class, 'edit'])
            ->middleware('role:admin|project_manager')
            ->name('edit');
        Route::put('/{inspectionCompany}', [App\Http\Controllers\InspectionCompanyController::class, 'update'])
            ->middleware('role:admin|project_manager')
            ->name('update');
        Route::delete('/{inspectionCompany}', [App\Http\Controllers\InspectionCompanyController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');
    });

    // Inspections (Denetimler)
    Route::prefix('inspections')->name('inspections.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\InspectionController::class, 'dashboard'])
            ->middleware('role:admin|project_manager|site_supervisor')
            ->name('dashboard');

        // CRUD
        Route::get('/', [App\Http\Controllers\InspectionController::class, 'index'])
            ->middleware('role:admin|project_manager|site_supervisor')
            ->name('index');
        Route::get('/create', [App\Http\Controllers\InspectionController::class, 'create'])
            ->middleware('role:admin|project_manager|site_supervisor')
            ->name('create');
        Route::post('/', [App\Http\Controllers\InspectionController::class, 'store'])
            ->middleware('role:admin|project_manager|site_supervisor')
            ->name('store');
        Route::get('/{inspection}', [App\Http\Controllers\InspectionController::class, 'show'])
            ->middleware('role:admin|project_manager|site_supervisor')
            ->name('show');
        Route::get('/{inspection}/edit', [App\Http\Controllers\InspectionController::class, 'edit'])
            ->middleware('role:admin|project_manager|site_supervisor')
            ->name('edit');
        Route::put('/{inspection}', [App\Http\Controllers\InspectionController::class, 'update'])
            ->middleware('role:admin|project_manager|site_supervisor')
            ->name('update');
        Route::delete('/{inspection}', [App\Http\Controllers\InspectionController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');

        // Report Management
        Route::post('/{inspection}/upload-report', [App\Http\Controllers\InspectionController::class, 'uploadReport'])
            ->middleware('role:admin|project_manager|site_supervisor')
            ->name('upload-report');
        Route::delete('/{inspection}/delete-report', [App\Http\Controllers\InspectionController::class, 'deleteReport'])
            ->middleware('role:admin|project_manager')
            ->name('delete-report');

        // Attachment Management
        Route::post('/{inspection}/upload-attachment', [App\Http\Controllers\InspectionController::class, 'uploadAttachment'])
            ->middleware('role:admin|project_manager|site_supervisor')
            ->name('upload-attachment');
        Route::delete('/{inspection}/delete-attachment', [App\Http\Controllers\InspectionController::class, 'deleteAttachment'])
            ->middleware('role:admin|project_manager')
            ->name('delete-attachment');

        // Non-Conformities & Corrective Actions (JSON API)
        Route::post('/{inspection}/update-non-conformities', [App\Http\Controllers\InspectionController::class, 'updateNonConformities'])
            ->middleware('role:admin|project_manager|site_supervisor')
            ->name('update-non-conformities');
        Route::post('/{inspection}/update-corrective-actions', [App\Http\Controllers\InspectionController::class, 'updateCorrectiveActions'])
            ->middleware('role:admin|project_manager|site_supervisor')
            ->name('update-corrective-actions');
    });

    // Warehouses (Depolar)
    Route::prefix('warehouses')->name('warehouses.')->group(function () {
        Route::get('/', [App\Http\Controllers\WarehouseController::class, 'index'])
            ->middleware('role:admin|project_manager|procurement_manager')
            ->name('index');
        Route::get('/create', [App\Http\Controllers\WarehouseController::class, 'create'])
            ->middleware('role:admin|project_manager|procurement_manager')
            ->name('create');
        Route::post('/', [App\Http\Controllers\WarehouseController::class, 'store'])
            ->middleware('role:admin|project_manager|procurement_manager')
            ->name('store');
        Route::get('/{warehouse}', [App\Http\Controllers\WarehouseController::class, 'show'])
            ->middleware('role:admin|project_manager|procurement_manager')
            ->name('show');
        Route::get('/{warehouse}/edit', [App\Http\Controllers\WarehouseController::class, 'edit'])
            ->middleware('role:admin|project_manager|procurement_manager')
            ->name('edit');
        Route::put('/{warehouse}', [App\Http\Controllers\WarehouseController::class, 'update'])
            ->middleware('role:admin|project_manager|procurement_manager')
            ->name('update');
        Route::delete('/{warehouse}', [App\Http\Controllers\WarehouseController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');
    });

    // Stock Movements (Stok Hareketleri)
    Route::prefix('stock-movements')->name('stock-movements.')->group(function () {
        Route::get('/', [App\Http\Controllers\StockMovementController::class, 'index'])
            ->middleware('role:admin|project_manager|procurement_manager')
            ->name('index');
        Route::get('/create', [App\Http\Controllers\StockMovementController::class, 'create'])
            ->middleware('role:admin|project_manager|procurement_manager')
            ->name('create');
        Route::post('/', [App\Http\Controllers\StockMovementController::class, 'store'])
            ->middleware('role:admin|project_manager|procurement_manager')
            ->name('store');
        Route::get('/{stockMovement}', [App\Http\Controllers\StockMovementController::class, 'show'])
            ->middleware('role:admin|project_manager|procurement_manager')
            ->name('show');
        Route::get('/{stockMovement}/edit', [App\Http\Controllers\StockMovementController::class, 'edit'])
            ->middleware('role:admin|project_manager|procurement_manager')
            ->name('edit');
        Route::put('/{stockMovement}', [App\Http\Controllers\StockMovementController::class, 'update'])
            ->middleware('role:admin|project_manager|procurement_manager')
            ->name('update');
        Route::delete('/{stockMovement}', [App\Http\Controllers\StockMovementController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');
    });

    // Stock Transfers (Depolar Arası Transfer - Faz 3)
    Route::prefix('stock-transfers')->name('stock-transfers.')->group(function () {
        Route::get('/', [App\Http\Controllers\StockTransferController::class, 'index'])
            ->middleware('role:admin|project_manager|procurement_manager')
            ->name('index');
        Route::get('/create', [App\Http\Controllers\StockTransferController::class, 'create'])
            ->middleware('role:admin|project_manager|procurement_manager')
            ->name('create');
        Route::post('/', [App\Http\Controllers\StockTransferController::class, 'store'])
            ->middleware('role:admin|project_manager|procurement_manager')
            ->name('store');
        Route::get('/{stockTransfer}', [App\Http\Controllers\StockTransferController::class, 'show'])
            ->middleware('role:admin|project_manager|procurement_manager')
            ->name('show');
        Route::get('/warehouse-stock', [App\Http\Controllers\StockTransferController::class, 'getWarehouseStock'])
            ->middleware('role:admin|project_manager|procurement_manager')
            ->name('warehouse-stock');
    });

    // Stock Counts (Stok Sayımları - Faz 3)
    Route::prefix('stock-counts')->name('stock-counts.')->group(function () {
        Route::get('/', [App\Http\Controllers\StockCountController::class, 'index'])
            ->middleware('role:admin|project_manager|procurement_manager')
            ->name('index');
        Route::get('/create', [App\Http\Controllers\StockCountController::class, 'create'])
            ->middleware('role:admin|project_manager|procurement_manager')
            ->name('create');
        Route::post('/', [App\Http\Controllers\StockCountController::class, 'store'])
            ->middleware('role:admin|project_manager|procurement_manager')
            ->name('store');
        Route::get('/{stockCount}', [App\Http\Controllers\StockCountController::class, 'show'])
            ->middleware('role:admin|project_manager|procurement_manager')
            ->name('show');
        Route::post('/{stockCount}/approve', [App\Http\Controllers\StockCountController::class, 'approve'])
            ->middleware('role:admin|project_manager')
            ->name('approve');
        Route::post('/{stockCount}/reject', [App\Http\Controllers\StockCountController::class, 'reject'])
            ->middleware('role:admin|project_manager')
            ->name('reject');
        Route::get('/system-stock', [App\Http\Controllers\StockCountController::class, 'getSystemStock'])
            ->middleware('role:admin|project_manager|procurement_manager')
            ->name('system-stock');
    });

    // SAFETY MANAGEMENT ROUTES (İş Sağlığı ve Güvenliği - Faz 3)
    // Safety Incidents (İş Kazaları ve Olaylar)
    Route::prefix('safety-incidents')->name('safety-incidents.')->group(function () {
        Route::get('/', [SafetyIncidentController::class, 'index'])
            ->middleware('role:admin|project_manager|site_manager|safety_officer')
            ->name('index');
        Route::get('/create', [SafetyIncidentController::class, 'create'])
            ->middleware('role:admin|project_manager|site_manager|safety_officer')
            ->name('create');
        Route::post('/', [SafetyIncidentController::class, 'store'])
            ->middleware('role:admin|project_manager|site_manager|safety_officer')
            ->name('store');
        Route::get('/{safetyIncident}', [SafetyIncidentController::class, 'show'])
            ->middleware('role:admin|project_manager|site_manager|safety_officer')
            ->name('show');
        Route::get('/{safetyIncident}/edit', [SafetyIncidentController::class, 'edit'])
            ->middleware('role:admin|project_manager|site_manager|safety_officer')
            ->name('edit');
        Route::put('/{safetyIncident}', [SafetyIncidentController::class, 'update'])
            ->middleware('role:admin|project_manager|site_manager|safety_officer')
            ->name('update');
        Route::delete('/{safetyIncident}', [SafetyIncidentController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');
    });

    // Safety Trainings (İSG Eğitimleri)
    Route::prefix('safety-trainings')->name('safety-trainings.')->group(function () {
        Route::get('/', [SafetyTrainingController::class, 'index'])
            ->middleware('role:admin|project_manager|site_manager|safety_officer|hr')
            ->name('index');
        Route::get('/create', [SafetyTrainingController::class, 'create'])
            ->middleware('role:admin|project_manager|safety_officer|hr')
            ->name('create');
        Route::post('/', [SafetyTrainingController::class, 'store'])
            ->middleware('role:admin|project_manager|safety_officer|hr')
            ->name('store');
        Route::get('/{safetyTraining}', [SafetyTrainingController::class, 'show'])
            ->middleware('role:admin|project_manager|site_manager|safety_officer|hr')
            ->name('show');
        Route::get('/{safetyTraining}/edit', [SafetyTrainingController::class, 'edit'])
            ->middleware('role:admin|project_manager|safety_officer|hr')
            ->name('edit');
        Route::put('/{safetyTraining}', [SafetyTrainingController::class, 'update'])
            ->middleware('role:admin|project_manager|safety_officer|hr')
            ->name('update');
        Route::delete('/{safetyTraining}', [SafetyTrainingController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');
    });

    // Safety Inspections (Güvenlik Denetimleri)
    Route::prefix('safety-inspections')->name('safety-inspections.')->group(function () {
        Route::get('/', [SafetyInspectionController::class, 'index'])
            ->middleware('role:admin|project_manager|site_manager|safety_officer')
            ->name('index');
        Route::get('/create', [SafetyInspectionController::class, 'create'])
            ->middleware('role:admin|project_manager|site_manager|safety_officer')
            ->name('create');
        Route::post('/', [SafetyInspectionController::class, 'store'])
            ->middleware('role:admin|project_manager|site_manager|safety_officer')
            ->name('store');
        Route::get('/{safetyInspection}', [SafetyInspectionController::class, 'show'])
            ->middleware('role:admin|project_manager|site_manager|safety_officer')
            ->name('show');
        Route::get('/{safetyInspection}/edit', [SafetyInspectionController::class, 'edit'])
            ->middleware('role:admin|project_manager|site_manager|safety_officer')
            ->name('edit');
        Route::put('/{safetyInspection}', [SafetyInspectionController::class, 'update'])
            ->middleware('role:admin|project_manager|site_manager|safety_officer')
            ->name('update');
        Route::delete('/{safetyInspection}', [SafetyInspectionController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');
    });

    // Risk Assessments (Risk Değerlendirmeleri)
    Route::prefix('risk-assessments')->name('risk-assessments.')->group(function () {
        Route::get('/', [RiskAssessmentController::class, 'index'])
            ->middleware('role:admin|project_manager|site_manager|safety_officer')
            ->name('index');
        Route::get('/create', [RiskAssessmentController::class, 'create'])
            ->middleware('role:admin|project_manager|safety_officer')
            ->name('create');
        Route::post('/', [RiskAssessmentController::class, 'store'])
            ->middleware('role:admin|project_manager|safety_officer')
            ->name('store');
        Route::get('/{riskAssessment}', [RiskAssessmentController::class, 'show'])
            ->middleware('role:admin|project_manager|site_manager|safety_officer')
            ->name('show');
        Route::get('/{riskAssessment}/edit', [RiskAssessmentController::class, 'edit'])
            ->middleware('role:admin|project_manager|safety_officer')
            ->name('edit');
        Route::put('/{riskAssessment}', [RiskAssessmentController::class, 'update'])
            ->middleware('role:admin|project_manager|safety_officer')
            ->name('update');
        Route::delete('/{riskAssessment}', [RiskAssessmentController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');

        // Status Actions
        Route::post('/{riskAssessment}/approve', [RiskAssessmentController::class, 'approve'])
            ->middleware('role:admin|project_manager')
            ->name('approve');
    });

    // PPE Assignments (KKD Atamaları)
    Route::prefix('ppe-assignments')->name('ppe-assignments.')->group(function () {
        Route::get('/', [PpeAssignmentController::class, 'index'])
            ->middleware('role:admin|project_manager|site_manager|safety_officer|hr')
            ->name('index');
        Route::get('/create', [PpeAssignmentController::class, 'create'])
            ->middleware('role:admin|project_manager|site_manager|safety_officer|hr')
            ->name('create');
        Route::post('/', [PpeAssignmentController::class, 'store'])
            ->middleware('role:admin|project_manager|site_manager|safety_officer|hr')
            ->name('store');
        Route::get('/{ppeAssignment}', [PpeAssignmentController::class, 'show'])
            ->middleware('role:admin|project_manager|site_manager|safety_officer|hr')
            ->name('show');
        Route::get('/{ppeAssignment}/edit', [PpeAssignmentController::class, 'edit'])
            ->middleware('role:admin|project_manager|site_manager|safety_officer|hr')
            ->name('edit');
        Route::put('/{ppeAssignment}', [PpeAssignmentController::class, 'update'])
            ->middleware('role:admin|project_manager|site_manager|safety_officer|hr')
            ->name('update');
        Route::delete('/{ppeAssignment}', [PpeAssignmentController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');

        // Return PPE
        Route::post('/{ppeAssignment}/return', [PpeAssignmentController::class, 'returnPpe'])
            ->middleware('role:admin|project_manager|site_manager|safety_officer|hr')
            ->name('return');
    });

    // ========================================
    // EQUIPMENT MANAGEMENT - Ekipman Yönetimi
    // ========================================

    // Equipments - Ekipmanlar
    Route::prefix('equipments')->name('equipments.')->group(function () {
        Route::get('/', [EquipmentController::class, 'index'])
            ->middleware('role:admin|project_manager|site_manager|procurement_officer')
            ->name('index');
        Route::get('/create', [EquipmentController::class, 'create'])
            ->middleware('role:admin|project_manager|procurement_officer')
            ->name('create');
        Route::post('/', [EquipmentController::class, 'store'])
            ->middleware('role:admin|project_manager|procurement_officer')
            ->name('store');
        Route::get('/{equipment}', [EquipmentController::class, 'show'])
            ->middleware('role:admin|project_manager|site_manager|procurement_officer')
            ->name('show');
        Route::get('/{equipment}/edit', [EquipmentController::class, 'edit'])
            ->middleware('role:admin|project_manager|procurement_officer')
            ->name('edit');
        Route::put('/{equipment}', [EquipmentController::class, 'update'])
            ->middleware('role:admin|project_manager|procurement_officer')
            ->name('update');
        Route::delete('/{equipment}', [EquipmentController::class, 'destroy'])
            ->middleware('role:admin')
            ->name('destroy');
    });

    // Equipment Usages - Ekipman Kullanımları
    Route::prefix('equipment-usages')->name('equipment-usages.')->group(function () {
        Route::get('/', [EquipmentUsageController::class, 'index'])
            ->middleware('role:admin|project_manager|site_manager|procurement_officer')
            ->name('index');
        Route::get('/create', [EquipmentUsageController::class, 'create'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('create');
        Route::post('/', [EquipmentUsageController::class, 'store'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('store');
        Route::get('/{equipmentUsage}/edit', [EquipmentUsageController::class, 'edit'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('edit');
        Route::put('/{equipmentUsage}', [EquipmentUsageController::class, 'update'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('update');
        Route::delete('/{equipmentUsage}', [EquipmentUsageController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');
    });

    // Equipment Maintenance - Ekipman Bakımları
    Route::prefix('equipment-maintenance')->name('equipment-maintenance.')->group(function () {
        Route::get('/', [EquipmentMaintenanceController::class, 'index'])
            ->middleware('role:admin|project_manager|site_manager|procurement_officer')
            ->name('index');
        Route::get('/create', [EquipmentMaintenanceController::class, 'create'])
            ->middleware('role:admin|project_manager|procurement_officer')
            ->name('create');
        Route::post('/', [EquipmentMaintenanceController::class, 'store'])
            ->middleware('role:admin|project_manager|procurement_officer')
            ->name('store');
        Route::get('/{equipmentMaintenance}/edit', [EquipmentMaintenanceController::class, 'edit'])
            ->middleware('role:admin|project_manager|procurement_officer')
            ->name('edit');
        Route::put('/{equipmentMaintenance}', [EquipmentMaintenanceController::class, 'update'])
            ->middleware('role:admin|project_manager|procurement_officer')
            ->name('update');
        Route::delete('/{equipmentMaintenance}', [EquipmentMaintenanceController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');
    });

    // ========================================
    // USER PROJECT ROLES - Proje Rol Yönetimi
    // ========================================
    Route::prefix('user-project-roles')->name('user-project-roles.')->group(function () {
        Route::get('/', [UserProjectRoleController::class, 'index'])
            ->middleware('role:admin|hr')
            ->name('index');
        Route::get('/create', [UserProjectRoleController::class, 'create'])
            ->middleware('role:admin|hr')
            ->name('create');
        Route::post('/', [UserProjectRoleController::class, 'store'])
            ->middleware('role:admin|hr')
            ->name('store');
        Route::get('/{userProjectRole}/edit', [UserProjectRoleController::class, 'edit'])
            ->middleware('role:admin|hr')
            ->name('edit');
        Route::put('/{userProjectRole}', [UserProjectRoleController::class, 'update'])
            ->middleware('role:admin|hr')
            ->name('update');
        Route::delete('/{userProjectRole}', [UserProjectRoleController::class, 'destroy'])
            ->middleware('role:admin|hr')
            ->name('destroy');
        Route::post('/{userProjectRole}/activate', [UserProjectRoleController::class, 'activate'])
            ->middleware('role:admin|hr')
            ->name('activate');
        Route::post('/{userProjectRole}/deactivate', [UserProjectRoleController::class, 'deactivate'])
            ->middleware('role:admin|hr')
            ->name('deactivate');
        Route::get('/user/{user}', [UserProjectRoleController::class, 'byUser'])
            ->middleware('role:admin|hr|project_manager')
            ->name('by-user');
        Route::get('/project/{project}', [UserProjectRoleController::class, 'byProject'])
            ->middleware('role:admin|hr|project_manager')
            ->name('by-project');
    });

    // ========================================
    // ACTIVITY LOGS - Aktivite Logları
    // ========================================
    Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])
            ->middleware('role:admin|hr')
            ->name('index');
        Route::get('/{activityLog}', [ActivityLogController::class, 'show'])
            ->middleware('role:admin|hr')
            ->name('show');
        Route::get('/user/{user}', [ActivityLogController::class, 'userActivity'])
            ->middleware('role:admin|hr|project_manager')
            ->name('user-activity');
        Route::get('/project/{project}', [ActivityLogController::class, 'projectActivity'])
            ->middleware('role:admin|hr|project_manager')
            ->name('project-activity');
        Route::get('/export', [ActivityLogController::class, 'export'])
            ->middleware('role:admin|hr')
            ->name('export');
    });

    // ========================================
    // ROUTE PERMISSIONS - Route Yetki Yönetimi
    // ========================================
    Route::prefix('route-permissions')->name('route-permissions.')->group(function () {
        Route::get('/', [RoutePermissionController::class, 'index'])
            ->middleware('role:admin')
            ->name('index');
        Route::put('/{routePermission}', [RoutePermissionController::class, 'update'])
            ->middleware('role:admin')
            ->name('update');
        Route::post('/bulk-update', [RoutePermissionController::class, 'bulkUpdate'])
            ->middleware('role:admin')
            ->name('bulk-update');
        Route::post('/sync', [RoutePermissionController::class, 'syncFromRoutes'])
            ->middleware('role:admin')
            ->name('sync');
        Route::post('/batch-assign', [RoutePermissionController::class, 'batchAssignByModule'])
            ->middleware('role:admin')
            ->name('batch-assign');
    });

    // ========================================
    // PROJECT SCHEDULING - Gantt/Timeline
    // ========================================

    // Project Schedules - Proje Takvimi
    Route::prefix('project-schedules')->name('project-schedules.')->group(function () {
        Route::get('/', [ProjectScheduleController::class, 'index'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('index');
        Route::get('/create', [ProjectScheduleController::class, 'create'])
            ->middleware('role:admin|project_manager')
            ->name('create');
        Route::post('/', [ProjectScheduleController::class, 'store'])
            ->middleware('role:admin|project_manager')
            ->name('store');
        Route::get('/{projectSchedule}', [ProjectScheduleController::class, 'show'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('show');
        Route::get('/{projectSchedule}/edit', [ProjectScheduleController::class, 'edit'])
            ->middleware('role:admin|project_manager')
            ->name('edit');
        Route::put('/{projectSchedule}', [ProjectScheduleController::class, 'update'])
            ->middleware('role:admin|project_manager')
            ->name('update');
        Route::delete('/{projectSchedule}', [ProjectScheduleController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');
        Route::post('/{projectSchedule}/progress', [ProjectScheduleController::class, 'updateProgress'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('update-progress');
    });

    // Gantt Chart View
    Route::get('/projects/{project}/gantt', [ProjectScheduleController::class, 'gantt'])
        ->middleware('role:admin|project_manager|site_manager')
        ->name('projects.gantt');

    // ========================================
    // REPORTING MODULE - Raporlama Katmanı (Faz 3)
    // ========================================

    // KPI Definitions - KPI Tanımları
    Route::prefix('kpis')->name('kpis.')->group(function () {
        Route::get('/', [App\Http\Controllers\KpiController::class, 'index'])
            ->middleware('role:admin|project_manager')
            ->name('index');
        Route::get('/create', [App\Http\Controllers\KpiController::class, 'create'])
            ->middleware('role:admin|project_manager')
            ->name('create');
        Route::post('/', [App\Http\Controllers\KpiController::class, 'store'])
            ->middleware('role:admin|project_manager')
            ->name('store');
        Route::get('/{kpi}/edit', [App\Http\Controllers\KpiController::class, 'edit'])
            ->middleware('role:admin|project_manager')
            ->name('edit');
        Route::put('/{kpi}', [App\Http\Controllers\KpiController::class, 'update'])
            ->middleware('role:admin|project_manager')
            ->name('update');
        Route::delete('/{kpi}', [App\Http\Controllers\KpiController::class, 'destroy'])
            ->middleware('role:admin')
            ->name('destroy');
        Route::post('/{kpi}/calculate', [App\Http\Controllers\KpiController::class, 'calculate'])
            ->middleware('role:admin|project_manager')
            ->name('calculate');
    });

    // ========================================
    // TEMPORARY ASSIGNMENTS - Geçici Görevlendirme
    // ========================================
    Route::prefix('temporary-assignments')->name('temporary-assignments.')->group(function () {
        Route::get('/', [App\Http\Controllers\TemporaryAssignmentController::class, 'index'])
            ->middleware('role:admin|hr|project_manager')
            ->name('index');
        Route::get('/create', [App\Http\Controllers\TemporaryAssignmentController::class, 'create'])
            ->middleware('role:admin|hr|project_manager')
            ->name('create');
        Route::post('/', [App\Http\Controllers\TemporaryAssignmentController::class, 'store'])
            ->middleware('role:admin|hr|project_manager')
            ->name('store');
        Route::get('/{temporaryAssignment}', [App\Http\Controllers\TemporaryAssignmentController::class, 'show'])
            ->middleware('role:admin|hr|project_manager')
            ->name('show');
        Route::get('/{temporaryAssignment}/edit', [App\Http\Controllers\TemporaryAssignmentController::class, 'edit'])
            ->middleware('role:admin|hr|project_manager')
            ->name('edit');
        Route::put('/{temporaryAssignment}', [App\Http\Controllers\TemporaryAssignmentController::class, 'update'])
            ->middleware('role:admin|hr|project_manager')
            ->name('update');
        Route::delete('/{temporaryAssignment}', [App\Http\Controllers\TemporaryAssignmentController::class, 'destroy'])
            ->middleware('role:admin|hr|project_manager')
            ->name('destroy');

        // Assignment Actions
        Route::post('/{temporaryAssignment}/approve', [App\Http\Controllers\TemporaryAssignmentController::class, 'approve'])
            ->middleware('role:admin|hr|project_manager')
            ->name('approve');
        Route::post('/{temporaryAssignment}/reject', [App\Http\Controllers\TemporaryAssignmentController::class, 'reject'])
            ->middleware('role:admin|hr|project_manager')
            ->name('reject');
        Route::post('/{temporaryAssignment}/complete', [App\Http\Controllers\TemporaryAssignmentController::class, 'complete'])
            ->middleware('role:admin|hr|project_manager')
            ->name('complete');

        // API Routes
        Route::get('/employee/{employee}', [App\Http\Controllers\TemporaryAssignmentController::class, 'byEmployee'])
            ->middleware('role:admin|hr|project_manager')
            ->name('by-employee');
        Route::get('/project/{project}', [App\Http\Controllers\TemporaryAssignmentController::class, 'byProject'])
            ->middleware('role:admin|hr|project_manager')
            ->name('by-project');
        Route::post('/check-conflicts', [App\Http\Controllers\TemporaryAssignmentController::class, 'checkConflicts'])
            ->middleware('role:admin|hr|project_manager')
            ->name('check-conflicts');
    });

    // ========================================
    // DWG IMPORTS - AutoCAD DWG/DXF İçe Aktarımı
    // ========================================
    Route::prefix('dwg-imports')->name('dwg-imports.')->group(function () {
        Route::get('/', [DwgImportController::class, 'index'])
            ->middleware('role:admin|project_manager')
            ->name('index');
        Route::get('/create', [DwgImportController::class, 'create'])
            ->middleware('role:admin|project_manager')
            ->name('create');
        Route::post('/', [DwgImportController::class, 'store'])
            ->middleware('role:admin|project_manager')
            ->name('store');
        Route::get('/{dwgImport}', [DwgImportController::class, 'show'])
            ->middleware('role:admin|project_manager')
            ->name('show');
        Route::delete('/{dwgImport}', [DwgImportController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');

        // Import Actions
        Route::post('/{dwgImport}/update-mappings', [DwgImportController::class, 'updateMappings'])
            ->middleware('role:admin|project_manager')
            ->name('update-mappings');
        Route::post('/{dwgImport}/approve', [DwgImportController::class, 'approve'])
            ->middleware('role:admin|project_manager')
            ->name('approve');
    });
});
