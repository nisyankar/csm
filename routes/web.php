<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeProjectAssignmentController;
use App\Http\Controllers\TimesheetController;
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
use App\Http\Controllers\SystemController;
use App\Http\Controllers\PurchasingRequestController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\SubcontractorController;
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

// Public API endpoints (for mobile app, external integrations)
Route::prefix('public-api')->name('public-api.')->group(function () {
    // These would typically be in api.php but placing here for completeness
    Route::post('/qr-check', [QRCodeController::class, 'publicQrCheck'])->name('qr-check');
    Route::get('/employee-info/{code}', [EmployeeController::class, 'publicInfo'])->name('employee-info');
});
