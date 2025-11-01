<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
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
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\TimesheetController as ApiTimesheetController;
use App\Http\Controllers\Api\ProgressPaymentController as ApiProgressPaymentController;
use App\Http\Controllers\Api\ProjectController as ApiProjectController;
use App\Http\Controllers\Api\SubcontractorController as ApiSubcontractorController;
use App\Http\Controllers\Api\MaterialController as ApiMaterialController;
use App\Http\Controllers\Api\DepartmentController as ApiDepartmentController;
use App\Http\Controllers\Api\NotificationController as ApiNotificationController;
use App\Http\Controllers\Api\FileUploadController as ApiFileUploadController;
use App\Http\Controllers\Api\QuantityController as ApiQuantityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API Routes (No Authentication Required)
Route::prefix('v1/public')->name('api.public.')->group(function () {
    
    // System Status
    Route::get('/status', function () {
        return response()->json([
            'status' => 'ok',
            'version' => '1.0.0',
            'timestamp' => now()->toISOString(),
            'environment' => app()->environment(),
        ]);
    })->name('status');
    
    // QR Code Verification (Public Kiosks)
    Route::post('/qr/verify', [QRCodeController::class, 'publicQrVerify'])->name('qr.verify');
    
    // Employee Info by QR Code (Limited Info)
    Route::get('/employee/{code}/info', [EmployeeController::class, 'publicEmployeeInfo'])->name('employee.info');
    
    // Project Location Verification
    Route::post('/project/location-verify', [QRCodeController::class, 'verifyProjectLocation'])->name('project.location-verify');
});

// Authentication API Routes (Mobile App - Sanctum)
Route::prefix('v1/auth')->name('api.auth.')->group(function () {

    // Login/Logout
    Route::post('/login', [ApiAuthController::class, 'login'])->name('login');
    Route::post('/logout', [ApiAuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
    Route::post('/logout-all', [ApiAuthController::class, 'logoutAll'])->middleware('auth:sanctum')->name('logout-all');

    // Token Management
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [ApiAuthController::class, 'me'])->name('me');
        Route::post('/refresh', [ApiAuthController::class, 'refresh'])->name('refresh');
        Route::post('/change-password', [ApiAuthController::class, 'changePassword'])->name('change-password');

        // FCM Token Registration (for push notifications)
        Route::post('/register-device', [ApiAuthController::class, 'registerDevice'])->name('register-device');
    });

    // QR Code Login (for kiosk mode)
    Route::post('/qr-login', [AuthController::class, 'apiQrLogin'])->name('qr-login');

    // Password Reset (if needed for mobile)
    Route::post('/forgot-password', [AuthController::class, 'apiForgotPassword'])->name('forgot-password');
    Route::post('/reset-password', [AuthController::class, 'apiResetPassword'])->name('reset-password');
});

// Authenticated API Routes
Route::middleware(['auth:sanctum'])->prefix('v1')->name('api.v1.')->group(function () {
    
    // Dashboard API
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/data', [DashboardController::class, 'apiDashboardData'])->name('data');
        Route::get('/stats', [DashboardController::class, 'apiStats'])->name('stats');
        Route::get('/recent-activity', [DashboardController::class, 'apiRecentActivity'])->name('recent-activity');
        Route::get('/alerts', [DashboardController::class, 'apiAlerts'])->name('alerts');
    });
    
    // Employee API
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/', [EmployeeController::class, 'apiIndex'])->name('index');
        Route::post('/', [EmployeeController::class, 'apiStore'])
            ->middleware('role:admin|hr|project_manager')
            ->name('store');
        Route::get('/{employee}', [EmployeeController::class, 'apiShow'])->name('show');
        Route::put('/{employee}', [EmployeeController::class, 'apiUpdate'])
            ->middleware('role:admin|hr|project_manager')
            ->name('update');
        Route::delete('/{employee}', [EmployeeController::class, 'apiDestroy'])
            ->middleware('role:admin|hr')
            ->name('destroy');
        
        // Employee Actions
        Route::post('/{employee}/assign-project', [EmployeeController::class, 'apiAssignProject'])
            ->middleware('role:admin|hr|project_manager')
            ->name('assign-project');
        Route::delete('/{employee}/remove-project/{project}', [EmployeeController::class, 'apiRemoveFromProject'])
            ->middleware('role:admin|hr|project_manager')
            ->name('remove-project');
        Route::patch('/{employee}/status', [EmployeeController::class, 'apiToggleStatus'])
            ->middleware('role:admin|hr|project_manager')
            ->name('toggle-status');
        
        // Search and Filters
        Route::get('/search/{query}', [EmployeeController::class, 'apiSearch'])->name('search');
        Route::get('/by-project/{project}', [EmployeeController::class, 'apiByProject'])->name('by-project');
        Route::get('/by-department/{department}', [EmployeeController::class, 'apiByDepartment'])->name('by-department');
        
        // QR Code Management
        Route::get('/{employee}/qr-code', [EmployeeController::class, 'apiGenerateQrCode'])->name('qr-code');
        Route::post('/bulk-qr-generate', [EmployeeController::class, 'apiBulkGenerateQrCodes'])
            ->middleware('role:admin|hr|project_manager')
            ->name('bulk-qr-generate');
    });
    
    // Timesheet API
    Route::prefix('timesheets')->name('timesheets.')->group(function () {
        Route::get('/', [ApiTimesheetController::class, 'index'])->name('index');
        Route::post('/', [ApiTimesheetController::class, 'store'])->name('store');
        Route::get('/{timesheet}', [ApiTimesheetController::class, 'show'])->name('show');
        Route::put('/{timesheet}', [ApiTimesheetController::class, 'update'])->name('update');
        Route::delete('/{timesheet}', [ApiTimesheetController::class, 'destroy'])->name('destroy');
        
        // Timesheet Actions
        Route::post('/{timesheet}/submit', [TimesheetController::class, 'apiSubmitForApproval'])->name('submit');
        Route::post('/bulk-submit', [TimesheetController::class, 'apiBulkSubmit'])->name('bulk-submit');
        
        // QR Code Entry (Mobile App Primary Feature)
        Route::post('/qr-checkin', [TimesheetController::class, 'apiQrCheckIn'])->name('qr-checkin');
        Route::post('/qr-checkout', [TimesheetController::class, 'apiQrCheckOut'])->name('qr-checkout');
        Route::post('/qr-scan', [TimesheetController::class, 'apiQrScan'])->name('qr-scan');
        
        // Mobile Specific
        Route::get('/my-timesheets', [TimesheetController::class, 'apiMyTimesheets'])->name('my-timesheets');
        Route::get('/today', [TimesheetController::class, 'apiTodayTimesheet'])->name('today');
        Route::get('/week/{date?}', [TimesheetController::class, 'apiWeekTimesheets'])->name('week');
        Route::get('/month/{date?}', [TimesheetController::class, 'apiMonthTimesheets'])->name('month');
        
        // Filters and Search
        Route::get('/by-employee/{employee}', [TimesheetController::class, 'apiByEmployee'])->name('by-employee');
        Route::get('/by-project/{project}', [TimesheetController::class, 'apiByProject'])->name('by-project');
        Route::get('/by-date-range', [TimesheetController::class, 'apiByDateRange'])->name('by-date-range');
    });
    
    // Timesheet Approval API
    Route::prefix('timesheet-approvals')->name('timesheet-approvals.')->group(function () {
        Route::get('/', [TimesheetApprovalController::class, 'apiIndex'])->name('index');
        Route::get('/{approval}', [TimesheetApprovalController::class, 'apiShow'])->name('show');
        
        // Approval Actions
        Route::post('/{approval}/approve', [TimesheetApprovalController::class, 'apiApprove'])->name('approve');
        Route::post('/{approval}/reject', [TimesheetApprovalController::class, 'apiReject'])->name('reject');
        Route::post('/{approval}/request-revision', [TimesheetApprovalController::class, 'apiRequestRevision'])->name('request-revision');
        
        // Bulk Actions
        Route::post('/bulk-approve', [TimesheetApprovalController::class, 'apiBulkApprove'])->name('bulk-approve');
        Route::post('/bulk-reject', [TimesheetApprovalController::class, 'apiBulkReject'])->name('bulk-reject');
        
        // Mobile Queue
        Route::get('/queue', [TimesheetApprovalController::class, 'apiQueue'])->name('queue');
        Route::get('/pending-count', [TimesheetApprovalController::class, 'apiPendingCount'])->name('pending-count');
        Route::get('/my-pending', [TimesheetApprovalController::class, 'apiMyPending'])->name('my-pending');
        
        // Statistics
        Route::get('/statistics', [TimesheetApprovalController::class, 'apiStatistics'])->name('statistics');
    });
    
    // Project API
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', [ApiProjectController::class, 'index'])->name('index');
        Route::post('/', [ApiProjectController::class, 'store'])
            ->middleware('role:admin|hr|project_manager')
            ->name('store');
        Route::get('/stats', [ApiProjectController::class, 'stats'])->name('stats');
        Route::get('/{project}', [ApiProjectController::class, 'show'])->name('show');
        Route::get('/{project}/units', [ApiProjectController::class, 'units'])->name('units');
        Route::put('/{project}', [ApiProjectController::class, 'update'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('update');
        Route::delete('/{project}', [ApiProjectController::class, 'destroy'])
            ->middleware('role:admin|hr')
            ->name('destroy');

        // Project Actions
        Route::post('/{project}/assign-employee', [ApiProjectController::class, 'assignEmployee'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('assign-employee');
        Route::post('/{project}/remove-employee', [ApiProjectController::class, 'removeEmployee'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('remove-employee');
        Route::post('/{project}/assign-subcontractor', [ApiProjectController::class, 'assignSubcontractor'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('assign-subcontractor');
        Route::post('/{project}/remove-subcontractor', [ApiProjectController::class, 'removeSubcontractor'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('remove-subcontractor');
        Route::patch('/{project}/status', [ApiProjectController::class, 'updateStatus'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('update-status');

        // Project Dashboard
        Route::get('/{project}/dashboard', [ApiProjectController::class, 'dashboard'])->name('dashboard');
    });

    // Quantities (Metraj) API
    Route::prefix('quantities')->name('quantities.')->group(function () {
        Route::get('/', [ApiQuantityController::class, 'index'])->name('index');
        Route::post('/', [ApiQuantityController::class, 'store'])
            ->middleware('role:admin|project_manager|site_manager|engineer')
            ->name('store');
        Route::get('/stats', [ApiQuantityController::class, 'stats'])->name('stats');
        Route::get('/project/{projectId}', [ApiQuantityController::class, 'byProject'])->name('by-project');
        Route::get('/{quantity}', [ApiQuantityController::class, 'show'])->name('show');
        Route::put('/{quantity}', [ApiQuantityController::class, 'update'])
            ->middleware('role:admin|project_manager|site_manager|engineer')
            ->name('update');
        Route::delete('/{quantity}', [ApiQuantityController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');

        // Quantity Actions
        Route::post('/{quantity}/verify', [ApiQuantityController::class, 'verify'])
            ->middleware('role:admin|project_manager|site_manager|engineer')
            ->name('verify');
        Route::post('/{quantity}/approve', [ApiQuantityController::class, 'approve'])
            ->middleware('role:admin|project_manager')
            ->name('approve');
    });

    // Subcontractor API
    Route::prefix('subcontractors')->name('subcontractors.')->group(function () {
        Route::get('/', [ApiSubcontractorController::class, 'index'])->name('index');
        Route::post('/', [ApiSubcontractorController::class, 'store'])
            ->middleware('role:admin|hr|project_manager|purchasing_manager')
            ->name('store');
        Route::get('/stats', [ApiSubcontractorController::class, 'stats'])->name('stats');
        Route::get('/categories', [ApiSubcontractorController::class, 'categories'])->name('categories');
        Route::get('/category/{categoryId}', [ApiSubcontractorController::class, 'byCategory'])->name('by-category');
        Route::get('/{subcontractor}', [ApiSubcontractorController::class, 'show'])->name('show');
        Route::put('/{subcontractor}', [ApiSubcontractorController::class, 'update'])
            ->middleware('role:admin|hr|project_manager|purchasing_manager')
            ->name('update');
        Route::delete('/{subcontractor}', [ApiSubcontractorController::class, 'destroy'])
            ->middleware('role:admin|hr|project_manager')
            ->name('destroy');

        // Subcontractor Actions
        Route::post('/{subcontractor}/approve', [ApiSubcontractorController::class, 'approve'])
            ->middleware('role:admin|project_manager')
            ->name('approve');
        Route::post('/{subcontractor}/blacklist', [ApiSubcontractorController::class, 'blacklist'])
            ->middleware('role:admin|project_manager')
            ->name('blacklist');
        Route::post('/{subcontractor}/activate', [ApiSubcontractorController::class, 'activate'])
            ->middleware('role:admin|project_manager')
            ->name('activate');
    });

    // Material API
    Route::prefix('materials')->name('materials.')->group(function () {
        Route::get('/', [ApiMaterialController::class, 'index'])->name('index');
        Route::post('/', [ApiMaterialController::class, 'store'])
            ->middleware('role:admin|project_manager|purchasing_manager')
            ->name('store');
        Route::get('/categories', [ApiMaterialController::class, 'categories'])->name('categories');
        Route::get('/active', [ApiMaterialController::class, 'active'])->name('active');
        Route::get('/low-stock', [ApiMaterialController::class, 'lowStock'])->name('low-stock');
        Route::get('/category/{category}', [ApiMaterialController::class, 'byCategory'])->name('by-category');
        Route::get('/{material}', [ApiMaterialController::class, 'show'])->name('show');
        Route::put('/{material}', [ApiMaterialController::class, 'update'])
            ->middleware('role:admin|project_manager|purchasing_manager')
            ->name('update');
        Route::delete('/{material}', [ApiMaterialController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');
    });

    // Department API
    Route::prefix('departments')->name('departments.')->group(function () {
        Route::get('/', [ApiDepartmentController::class, 'index'])->name('index');
        Route::post('/', [ApiDepartmentController::class, 'store'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('store');
        Route::get('/stats', [ApiDepartmentController::class, 'stats'])->name('stats');
        Route::get('/by-project/{project}', [ApiDepartmentController::class, 'byProject'])->name('by-project');
        Route::get('/{department}', [ApiDepartmentController::class, 'show'])->name('show');
        Route::put('/{department}', [ApiDepartmentController::class, 'update'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('update');
        Route::delete('/{department}', [ApiDepartmentController::class, 'destroy'])
            ->middleware('role:admin|hr|project_manager')
            ->name('destroy');

        // Department Actions
        Route::patch('/{department}/status', [ApiDepartmentController::class, 'updateStatus'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('update-status');
        Route::post('/{department}/assign-supervisor', [ApiDepartmentController::class, 'assignSupervisor'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('assign-supervisor');
    });

    // Progress Payment API (Hakediş Yönetimi)
    Route::prefix('progress-payments')->name('progress-payments.')->group(function () {
        Route::get('/', [ApiProgressPaymentController::class, 'index'])->name('index');
        Route::get('/statistics', [ApiProgressPaymentController::class, 'statistics'])->name('statistics');
        Route::get('/pending-approvals', [ApiProgressPaymentController::class, 'pendingApprovals'])->name('pending-approvals');
        Route::get('/{progressPayment}', [ApiProgressPaymentController::class, 'show'])->name('show');

        // Approval Actions
        Route::post('/{progressPayment}/approve', [ApiProgressPaymentController::class, 'approve'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('approve');
        Route::post('/{progressPayment}/reject', [ApiProgressPaymentController::class, 'reject'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('reject');
    });
    
    // Leave Request API
    Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
        Route::get('/', [LeaveRequestController::class, 'apiIndex'])->name('index');
        Route::post('/', [LeaveRequestController::class, 'apiStore'])->name('store');
        Route::get('/{leaveRequest}', [LeaveRequestController::class, 'apiShow'])->name('show');
        Route::put('/{leaveRequest}', [LeaveRequestController::class, 'apiUpdate'])->name('update');
        Route::delete('/{leaveRequest}', [LeaveRequestController::class, 'apiDestroy'])->name('destroy');
        
        // Leave Actions
        Route::post('/{leaveRequest}/submit', [LeaveRequestController::class, 'apiSubmit'])->name('submit');
        Route::post('/{leaveRequest}/approve', [LeaveRequestController::class, 'apiApprove'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('approve');
        Route::post('/{leaveRequest}/reject', [LeaveRequestController::class, 'apiReject'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('reject');
        Route::post('/{leaveRequest}/cancel', [LeaveRequestController::class, 'apiCancel'])->name('cancel');
        
        // Mobile Specific
        Route::get('/my-requests', [LeaveRequestController::class, 'apiMyRequests'])->name('my-requests');
        Route::get('/my-balance', [LeaveRequestController::class, 'apiMyBalance'])->name('my-balance');
        Route::get('/pending-approvals', [LeaveRequestController::class, 'apiPendingApprovals'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('pending-approvals');
        
        // Calendar and Planning
        Route::get('/calendar/{year}/{month}', [LeaveRequestController::class, 'apiCalendar'])->name('calendar');
        Route::get('/check-availability', [LeaveRequestController::class, 'apiCheckAvailability'])->name('check-availability');
        Route::post('/bulk-approve', [LeaveRequestController::class, 'apiBulkApprove'])
            ->middleware('role:admin|hr|project_manager')
            ->name('bulk-approve');
    });
    
    // Document API
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'apiIndex'])->name('index');
        Route::post('/', [DocumentController::class, 'apiStore'])->name('store');
        Route::get('/{document}', [DocumentController::class, 'apiShow'])->name('show');
        Route::put('/{document}', [DocumentController::class, 'apiUpdate'])->name('update');
        Route::delete('/{document}', [DocumentController::class, 'apiDestroy'])->name('destroy');
        
        // Document Actions
        Route::get('/{document}/download', [DocumentController::class, 'apiDownload'])->name('download');
        Route::post('/{document}/verify', [DocumentController::class, 'apiVerify'])
            ->middleware('role:admin|hr|project_manager')
            ->name('verify');
        Route::post('/{document}/reject', [DocumentController::class, 'apiReject'])
            ->middleware('role:admin|hr|project_manager')
            ->name('reject');
        
        // Document Management
        Route::get('/my-documents', [DocumentController::class, 'apiMyDocuments'])->name('my-documents');
        Route::get('/expiring', [DocumentController::class, 'apiExpiring'])
            ->middleware('role:admin|hr|project_manager')
            ->name('expiring');
        Route::get('/pending-verification', [DocumentController::class, 'apiPendingVerification'])
            ->middleware('role:admin|hr|project_manager')
            ->name('pending-verification');
        
        // Search and Filters
        Route::get('/search/{query}', [DocumentController::class, 'apiSearch'])->name('search');
        Route::get('/by-employee/{employee}', [DocumentController::class, 'apiByEmployee'])->name('by-employee');
        Route::get('/by-project/{project}', [DocumentController::class, 'apiByProject'])->name('by-project');
    });
    
    // Notification API
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [ApiNotificationController::class, 'index'])->name('index');
        Route::get('/unread', [ApiNotificationController::class, 'unread'])->name('unread');
        Route::get('/unread-count', [ApiNotificationController::class, 'unreadCount'])->name('unread-count');
        Route::get('/{id}', [ApiNotificationController::class, 'show'])->name('show');

        // Notification Actions
        Route::post('/{id}/mark-as-read', [ApiNotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/mark-all-read', [ApiNotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [ApiNotificationController::class, 'destroy'])->name('destroy');

        // Device Registration (FCM)
        Route::post('/register-device', [ApiNotificationController::class, 'registerDevice'])->name('register-device');
        Route::post('/unregister-device', [ApiNotificationController::class, 'unregisterDevice'])->name('unregister-device');
    });
    
    // File Upload API
    Route::prefix('files')->name('files.')->group(function () {
        Route::post('/upload', [ApiFileUploadController::class, 'upload'])->name('upload');
        Route::post('/upload-image', [ApiFileUploadController::class, 'uploadImage'])->name('upload-image');
        Route::post('/upload-multiple-images', [ApiFileUploadController::class, 'uploadMultipleImages'])->name('upload-multiple-images');
        Route::post('/upload-avatar', [ApiFileUploadController::class, 'uploadAvatar'])->name('upload-avatar');
        Route::post('/upload-base64', [ApiFileUploadController::class, 'uploadBase64'])->name('upload-base64');
        Route::post('/delete', [ApiFileUploadController::class, 'delete'])->name('delete');
    });
    
    // QR Code API
    Route::prefix('qr')->name('qr.')->group(function () {
        Route::get('/', [QRCodeController::class, 'apiIndex'])->name('index');
        
        // QR Code Generation
        Route::post('/generate-employee', [QRCodeController::class, 'apiGenerateEmployeeQR'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('generate-employee');
        Route::post('/generate-project', [QRCodeController::class, 'apiGenerateProjectQR'])
            ->middleware('role:admin|hr|project_manager|site_manager')
            ->name('generate-project');
        
        // QR Code Scanning (Main Mobile Feature)
        Route::post('/scan', [QRCodeController::class, 'apiScanQR'])->name('scan');
        Route::post('/validate', [QRCodeController::class, 'apiValidateQR'])->name('validate');
        
        // QR Code Management
        Route::get('/history', [QRCodeController::class, 'apiScanHistory'])->name('history');
        Route::delete('/delete', [QRCodeController::class, 'apiDeleteQR'])
            ->middleware('role:admin|hr|project_manager')
            ->name('delete');
        
        // Mobile Camera Integration
        Route::post('/scan-image', [QRCodeController::class, 'apiScanFromImage'])->name('scan-image');
        Route::post('/batch-scan', [QRCodeController::class, 'apiBatchScan'])->name('batch-scan');
    });
    
    // Reports API
    Route::prefix('reports')->name('reports.')->middleware('role:admin|hr|project_manager|site_manager')->group(function () {
        Route::get('/', [ReportController::class, 'apiIndex'])->name('index');
        
        // Employee Reports
        Route::get('/employee-attendance', [ReportController::class, 'apiEmployeeAttendance'])->name('employee-attendance');
        Route::get('/employee-performance', [ReportController::class, 'apiEmployeePerformance'])->name('employee-performance');
        
        // Project Reports
        Route::get('/project-progress', [ReportController::class, 'apiProjectProgress'])->name('project-progress');
        Route::get('/financial-summary', [ReportController::class, 'apiFinancialSummary'])
            ->middleware('role:admin|project_manager')
            ->name('financial-summary');
        
        // Leave Reports
        Route::get('/leave-report', [ReportController::class, 'apiLeaveReport'])->name('leave-report');
        
        // Quick Stats for Mobile Dashboard
        Route::get('/quick-stats', [ReportController::class, 'apiQuickStats'])->name('quick-stats');
        Route::get('/dashboard-charts', [ReportController::class, 'apiDashboardCharts'])->name('dashboard-charts');
    });
    
    // Mobile Specific API Routes (Flutter App)
    Route::prefix('mobile')->name('mobile.')->group(function () {

        // Mobile Dashboard
        Route::get('/dashboard', [DashboardController::class, 'apiMobileDashboard'])->name('dashboard');
        Route::get('/quick-actions', [DashboardController::class, 'apiQuickActions'])->name('quick-actions');

        // Mobile Timesheet (Clock In/Out)
        Route::post('/timesheet/clock-in', [ApiTimesheetController::class, 'clockIn'])->name('timesheet.clock-in');
        Route::post('/timesheet/clock-out', [ApiTimesheetController::class, 'clockOut'])->name('timesheet.clock-out');
        Route::get('/timesheet/today-status', [ApiTimesheetController::class, 'todayStatus'])->name('timesheet.today-status');
        Route::get('/timesheet/week-summary', [ApiTimesheetController::class, 'weekSummary'])->name('timesheet.week-summary');
        Route::get('/timesheet/month-summary', [ApiTimesheetController::class, 'monthSummary'])->name('timesheet.month-summary');
        Route::get('/timesheet/{timesheet}', [ApiTimesheetController::class, 'show'])->name('timesheet.show');
        Route::get('/timesheets', [ApiTimesheetController::class, 'index'])->name('timesheets.list');

        // Offline Sync
        Route::post('/sync/timesheets', [ApiTimesheetController::class, 'syncOfflineData'])->name('sync.timesheets');

        // Mobile Approvals
        Route::get('/approvals/pending-count', [TimesheetApprovalController::class, 'apiMobilePendingCount'])->name('approvals.pending-count');
        Route::get('/approvals/quick-list', [TimesheetApprovalController::class, 'apiMobileQuickList'])->name('approvals.quick-list');

        // Mobile Notifications
        Route::get('/notifications/badge-count', [NotificationController::class, 'apiMobileBadgeCount'])->name('notifications.badge-count');
        Route::get('/notifications/latest', [NotificationController::class, 'apiMobileLatest'])->name('notifications.latest');

        // Mobile Profile
        Route::get('/profile', [AuthController::class, 'apiMobileProfile'])->name('profile');
        Route::get('/profile/quick-stats', [AuthController::class, 'apiMobileProfileStats'])->name('profile.stats');
    });
    
    // Admin API Routes
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        
        // System Management
        Route::get('/system/status', function () {
            return response()->json([
                'database' => 'connected',
                'cache' => 'active',
                'queue' => 'running',
                'storage' => 'accessible',
                'memory_usage' => memory_get_usage(true),
                'disk_usage' => disk_total_space('.') - disk_free_space('.'),
                'uptime' => now()->diffInSeconds(cache('app_start_time', now())),
            ]);
        })->name('system.status');
        
        Route::get('/users', [AuthController::class, 'apiAdminUsers'])->name('users');
        Route::post('/users/{user}/toggle-status', [AuthController::class, 'apiAdminToggleUserStatus'])->name('users.toggle-status');
        Route::get('/logs', [AuthController::class, 'apiAdminLogs'])->name('logs');
        Route::post('/backup', [AuthController::class, 'apiAdminBackup'])->name('backup');
        
        // System Statistics
        Route::get('/statistics', [DashboardController::class, 'apiAdminStatistics'])->name('statistics');
        Route::get('/performance-metrics', [DashboardController::class, 'apiPerformanceMetrics'])->name('performance-metrics');
    });
});

// Webhook Routes (for external integrations)
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    
    // Payroll System Integration
    Route::post('/payroll/export', function (Request $request) {
        // Validate webhook signature
        // Export timesheet data for payroll
        return response()->json(['status' => 'received']);
    })->name('payroll.export');
    
    // HR System Integration
    Route::post('/hr/employee-sync', function (Request $request) {
        // Sync employee data with external HR system
        return response()->json(['status' => 'synced']);
    })->name('hr.sync');
    
    // Project Management Tool Integration
    Route::post('/project/status-update', function (Request $request) {
        // Receive project status updates from external tools
        return response()->json(['status' => 'updated']);
    })->name('project.status-update');
});

// Health Check and Monitoring
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0'),
        'environment' => app()->environment(),
        'database' => 'ok',
        'cache' => cache()->get('health_check') ? 'ok' : 'warning',
    ]);
})->name('health');

// Rate Limited Public API
Route::middleware('throttle:60,1')->prefix('public')->name('public.')->group(function () {
    
    // Public Company Info
    Route::get('/company/info', function () {
        return response()->json([
            'name' => config('app.name'),
            'industry' => 'Construction',
            'location' => 'Turkey',
            'established' => '2024',
        ]);
    })->name('company.info');
    
    // Public Project Info (limited)
    Route::get('/projects/active-count', [ProjectController::class, 'apiPublicActiveCount'])->name('projects.active-count');
    
    // Public Career Info
    Route::get('/careers/openings', function () {
        return response()->json([
            'total_openings' => 5,
            'departments' => ['Construction', 'Engineering', 'Management'],
            'contact' => 'hr@company.com',
        ]);
    })->name('careers.openings');
});

// Purchasing Module API Routes (Added for Procurement Management)
Route::middleware(['auth:sanctum'])->prefix('v1/purchasing')->name('api.v1.purchasing.')->group(function () {

    // Purchasing Requests API
    Route::prefix('requests')->name('requests.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\PurchasingRequestController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Api\PurchasingRequestController::class, 'store'])->name('store');
        Route::get('/{purchasingRequest}', [\App\Http\Controllers\Api\PurchasingRequestController::class, 'show'])->name('show');
        Route::put('/{purchasingRequest}', [\App\Http\Controllers\Api\PurchasingRequestController::class, 'update'])->name('update');
        Route::delete('/{purchasingRequest}', [\App\Http\Controllers\Api\PurchasingRequestController::class, 'destroy'])->name('destroy');

        // Request Actions
        Route::post('/{purchasingRequest}/submit', [\App\Http\Controllers\Api\PurchasingRequestController::class, 'submit'])->name('submit');
        Route::post('/{purchasingRequest}/approve-supervisor', [\App\Http\Controllers\Api\PurchasingRequestController::class, 'approveBySupervisor'])
            ->middleware('role:admin|site_manager|foreman')
            ->name('approve-supervisor');
        Route::post('/{purchasingRequest}/approve-manager', [\App\Http\Controllers\Api\PurchasingRequestController::class, 'approveByManager'])
            ->middleware('role:admin|project_manager')
            ->name('approve-manager');
        Route::post('/{purchasingRequest}/reject', [\App\Http\Controllers\Api\PurchasingRequestController::class, 'reject'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('reject');
        Route::post('/{purchasingRequest}/cancel', [\App\Http\Controllers\Api\PurchasingRequestController::class, 'cancel'])->name('cancel');

        // Statistics
        Route::get('/statistics', [\App\Http\Controllers\Api\PurchasingRequestController::class, 'statistics'])->name('statistics');
    });

    // Suppliers API
    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\SupplierController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Api\SupplierController::class, 'store'])
            ->middleware('role:admin|project_manager|purchasing_manager')
            ->name('store');
        Route::get('/{supplier}', [\App\Http\Controllers\Api\SupplierController::class, 'show'])->name('show');
        Route::put('/{supplier}', [\App\Http\Controllers\Api\SupplierController::class, 'update'])
            ->middleware('role:admin|project_manager|purchasing_manager')
            ->name('update');
        Route::delete('/{supplier}', [\App\Http\Controllers\Api\SupplierController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');

        // Supplier Actions
        Route::post('/{supplier}/blacklist', [\App\Http\Controllers\Api\SupplierController::class, 'blacklist'])
            ->middleware('role:admin|project_manager|purchasing_manager')
            ->name('blacklist');
        Route::post('/{supplier}/activate', [\App\Http\Controllers\Api\SupplierController::class, 'activate'])
            ->middleware('role:admin|project_manager|purchasing_manager')
            ->name('activate');

        // Supplier Data
        Route::get('/{supplier}/performance', [\App\Http\Controllers\Api\SupplierController::class, 'performance'])->name('performance');
        Route::post('/compare', [\App\Http\Controllers\Api\SupplierController::class, 'compare'])->name('compare');
        Route::get('/category/{category}', [\App\Http\Controllers\Api\SupplierController::class, 'byCategory'])->name('by-category');
    });

    // Purchase Orders API
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'store'])
            ->middleware('role:admin|project_manager|purchasing_manager')
            ->name('store');
        Route::get('/{purchaseOrder}', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'show'])->name('show');
        Route::put('/{purchaseOrder}', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'update'])
            ->middleware('role:admin|project_manager|purchasing_manager')
            ->name('update');
        Route::delete('/{purchaseOrder}', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');

        // Order Actions
        Route::post('/{purchaseOrder}/approve', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'approve'])
            ->middleware('role:admin|project_manager')
            ->name('approve');
        Route::post('/{purchaseOrder}/cancel', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'cancel'])
            ->middleware('role:admin|project_manager|purchasing_manager')
            ->name('cancel');
        Route::post('/{purchaseOrder}/update-payment-status', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'updatePaymentStatus'])
            ->middleware('role:admin|project_manager|purchasing_manager|accountant')
            ->name('update-payment-status');

        // Order Data
        Route::get('/statistics', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'statistics'])->name('statistics');
        Route::get('/supplier/{supplierId}', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'bySupplier'])->name('by-supplier');
        Route::get('/upcoming-deliveries', [\App\Http\Controllers\Api\PurchaseOrderController::class, 'upcomingDeliveries'])->name('upcoming-deliveries');
    });

    // Deliveries API
    Route::prefix('deliveries')->name('deliveries.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\DeliveryController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Api\DeliveryController::class, 'store'])
            ->middleware('role:admin|project_manager|purchasing_manager|warehouse_manager')
            ->name('store');
        Route::get('/{delivery}', [\App\Http\Controllers\Api\DeliveryController::class, 'show'])->name('show');
        Route::put('/{delivery}', [\App\Http\Controllers\Api\DeliveryController::class, 'update'])
            ->middleware('role:admin|project_manager|purchasing_manager|warehouse_manager')
            ->name('update');
        Route::delete('/{delivery}', [\App\Http\Controllers\Api\DeliveryController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');

        // Delivery Actions
        Route::post('/{delivery}/mark-received', [\App\Http\Controllers\Api\DeliveryController::class, 'markAsReceived'])
            ->middleware('role:admin|project_manager|purchasing_manager|warehouse_manager|site_manager')
            ->name('mark-received');
        Route::post('/{delivery}/reject', [\App\Http\Controllers\Api\DeliveryController::class, 'reject'])
            ->middleware('role:admin|project_manager|purchasing_manager|warehouse_manager|site_manager')
            ->name('reject');

        // Delivery Data
        Route::get('/today', [\App\Http\Controllers\Api\DeliveryController::class, 'today'])->name('today');
        Route::get('/pending', [\App\Http\Controllers\Api\DeliveryController::class, 'pending'])->name('pending');
        Route::get('/statistics', [\App\Http\Controllers\Api\DeliveryController::class, 'statistics'])->name('statistics');
        Route::get('/purchase-order/{purchaseOrderId}', [\App\Http\Controllers\Api\DeliveryController::class, 'byPurchaseOrder'])->name('by-purchase-order');
    });
});

// Project Structure & Work Management API Routes (Faz 1)
Route::middleware(['auth:sanctum'])->prefix('v1/project-management')->name('api.v1.pm.')->group(function () {

    // Project Structures (Bloklar/Binalar)
    Route::prefix('structures')->name('structures.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\ProjectStructureController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Api\ProjectStructureController::class, 'store'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('store');
        Route::get('/{structure}', [\App\Http\Controllers\Api\ProjectStructureController::class, 'show'])->name('show');
        Route::put('/{structure}', [\App\Http\Controllers\Api\ProjectStructureController::class, 'update'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('update');
        Route::delete('/{structure}', [\App\Http\Controllers\Api\ProjectStructureController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');

        // Structure Progress
        Route::get('/{structure}/progress', [\App\Http\Controllers\Api\ProjectStructureController::class, 'progress'])->name('progress');
    });

    // Project Floors (Katlar)
    Route::prefix('floors')->name('floors.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\ProjectFloorController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Api\ProjectFloorController::class, 'store'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('store');
        Route::get('/{floor}', [\App\Http\Controllers\Api\ProjectFloorController::class, 'show'])->name('show');
        Route::put('/{floor}', [\App\Http\Controllers\Api\ProjectFloorController::class, 'update'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('update');
        Route::delete('/{floor}', [\App\Http\Controllers\Api\ProjectFloorController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');
    });

    // Project Units (Daireler/Birimler)
    Route::prefix('units')->name('units.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\ProjectUnitController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Api\ProjectUnitController::class, 'store'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('store');
        Route::get('/{unit}', [\App\Http\Controllers\Api\ProjectUnitController::class, 'show'])->name('show');
        Route::put('/{unit}', [\App\Http\Controllers\Api\ProjectUnitController::class, 'update'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('update');
        Route::delete('/{unit}', [\App\Http\Controllers\Api\ProjectUnitController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');
    });

    // Work Categories (İş Kategorileri)
    Route::prefix('work-categories')->name('work-categories.')->group(function () {
        Route::get('/', function () {
            $categories = \App\Models\WorkCategory::active()->with('workItems')->get();
            return response()->json(['success' => true, 'data' => $categories]);
        })->name('index');
    });

    // Work Items (İş Kalemleri)
    Route::prefix('work-items')->name('work-items.')->group(function () {
        Route::get('/', function () {
            $items = \App\Models\WorkItem::active()->with('category')->get();
            return response()->json(['success' => true, 'data' => $items]);
        })->name('index');

        Route::get('/by-category/{categoryId}', function ($categoryId) {
            $items = \App\Models\WorkItem::active()->where('category_id', $categoryId)->get();
            return response()->json(['success' => true, 'data' => $items]);
        })->name('by-category');
    });

    // Work Item Assignments (İş Atamaları)
    Route::prefix('work-assignments')->name('work-assignments.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\WorkItemAssignmentController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Api\WorkItemAssignmentController::class, 'store'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('store');
        Route::get('/{assignment}', [\App\Http\Controllers\Api\WorkItemAssignmentController::class, 'show'])->name('show');
        Route::put('/{assignment}', [\App\Http\Controllers\Api\WorkItemAssignmentController::class, 'update'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('update');
        Route::delete('/{assignment}', [\App\Http\Controllers\Api\WorkItemAssignmentController::class, 'destroy'])
            ->middleware('role:admin|project_manager')
            ->name('destroy');
    });

    // Work Progress (İlerleme Raporları)
    Route::prefix('work-progress')->name('work-progress.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\WorkProgressController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Api\WorkProgressController::class, 'store'])->name('store');
        Route::get('/{progress}', [\App\Http\Controllers\Api\WorkProgressController::class, 'show'])->name('show');
        Route::put('/{progress}', [\App\Http\Controllers\Api\WorkProgressController::class, 'update'])->name('update');
        Route::delete('/{progress}', [\App\Http\Controllers\Api\WorkProgressController::class, 'destroy'])->name('destroy');

        // Approval Actions
        Route::post('/{progress}/approve', [\App\Http\Controllers\Api\WorkProgressController::class, 'approve'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('approve');
        Route::post('/{progress}/reject', [\App\Http\Controllers\Api\WorkProgressController::class, 'reject'])
            ->middleware('role:admin|project_manager|site_manager')
            ->name('reject');
    });

    // Financial Management (Finansal Yönetim)
    Route::prefix('financial')->name('financial.')->group(function () {

        // Income Categories (Gelir Kategorileri)
        Route::prefix('income-categories')->name('income-categories.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\IncomeCategoryController::class, 'index'])->name('index');
            Route::get('/tree', [\App\Http\Controllers\Api\IncomeCategoryController::class, 'tree'])->name('tree');
            Route::get('/{incomeCategory}', [\App\Http\Controllers\Api\IncomeCategoryController::class, 'show'])->name('show');
        });

        // Expense Categories (Gider Kategorileri)
        Route::prefix('expense-categories')->name('expense-categories.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\ExpenseCategoryController::class, 'index'])->name('index');
            Route::get('/tree', [\App\Http\Controllers\Api\ExpenseCategoryController::class, 'tree'])->name('tree');
            Route::get('/{expenseCategory}', [\App\Http\Controllers\Api\ExpenseCategoryController::class, 'show'])->name('show');
        });

        // Financial Transactions (Finansal İşlemler)
        Route::prefix('transactions')->name('transactions.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\FinancialTransactionController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Api\FinancialTransactionController::class, 'store'])
                ->middleware('role:admin|project_manager|hr')
                ->name('store');
            Route::get('/{financialTransaction}', [\App\Http\Controllers\Api\FinancialTransactionController::class, 'show'])->name('show');
            Route::put('/{financialTransaction}', [\App\Http\Controllers\Api\FinancialTransactionController::class, 'update'])
                ->middleware('role:admin|project_manager|hr')
                ->name('update');
            Route::delete('/{financialTransaction}', [\App\Http\Controllers\Api\FinancialTransactionController::class, 'destroy'])
                ->middleware('role:admin|project_manager')
                ->name('destroy');

            // Transaction Actions
            Route::post('/{financialTransaction}/payment', [\App\Http\Controllers\Api\FinancialTransactionController::class, 'makePayment'])
                ->middleware('role:admin|project_manager|hr')
                ->name('payment');
            Route::post('/{financialTransaction}/approve', [\App\Http\Controllers\Api\FinancialTransactionController::class, 'approve'])
                ->middleware('role:admin|project_manager')
                ->name('approve');

            // Reports
            Route::get('/profit-loss', [\App\Http\Controllers\Api\FinancialTransactionController::class, 'profitLoss'])->name('profit-loss');
            Route::get('/category-breakdown', [\App\Http\Controllers\Api\FinancialTransactionController::class, 'categoryBreakdown'])->name('category-breakdown');
            Route::get('/dashboard-summary', [\App\Http\Controllers\Api\FinancialTransactionController::class, 'dashboardSummary'])->name('dashboard-summary');
        });

        // Contracts (Sözleşme Yönetimi - Faz 2)
        Route::prefix('contracts')->name('contracts.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\ContractController::class, 'search'])->name('search');
            Route::get('/stats', [\App\Http\Controllers\Api\ContractController::class, 'stats'])->name('stats');
            Route::get('/expiring-soon', [\App\Http\Controllers\Api\ContractController::class, 'expiringSoon'])->name('expiring-soon');
            Route::get('/expired', [\App\Http\Controllers\Api\ContractController::class, 'expired'])->name('expired');
            Route::get('/{contract}', [\App\Http\Controllers\Api\ContractController::class, 'show'])->name('show');

            // Contract Filters
            Route::get('/subcontractor/{subcontractorId}', [\App\Http\Controllers\Api\ContractController::class, 'bySubcontractor'])->name('by-subcontractor');
            Route::get('/project/{projectId}', [\App\Http\Controllers\Api\ContractController::class, 'byProject'])->name('by-project');
            Route::get('/project/{projectId}/subcontractor/{subcontractorId}/active', [\App\Http\Controllers\Api\ContractController::class, 'activeContract'])->name('active-contract');
        });

        // Quantities (Keşif/Metraj - Faz 2)
        Route::prefix('quantities')->name('quantities.')->group(function () {
            Route::get('/search', [\App\Http\Controllers\QuantityController::class, 'search'])->name('search');
            Route::get('/project/{projectId}', [\App\Http\Controllers\QuantityController::class, 'byProject'])->name('by-project');
            Route::get('/work-item/{workItemId}', [\App\Http\Controllers\QuantityController::class, 'byWorkItem'])->name('by-work-item');
        });
    });

    // Sales & Deed Management Module (Satış ve Tapu Yönetimi)
    Route::prefix('sales')->name('sales.')->group(function () {

        // Customers (Müşteriler)
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\CustomerController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Api\CustomerController::class, 'store'])
                ->middleware('role:admin|project_manager|sales_manager')
                ->name('store');
            Route::get('/stats', [\App\Http\Controllers\Api\CustomerController::class, 'stats'])->name('stats');
            Route::get('/{customer}', [\App\Http\Controllers\Api\CustomerController::class, 'show'])->name('show');
            Route::put('/{customer}', [\App\Http\Controllers\Api\CustomerController::class, 'update'])
                ->middleware('role:admin|project_manager|sales_manager')
                ->name('update');
            Route::delete('/{customer}', [\App\Http\Controllers\Api\CustomerController::class, 'destroy'])
                ->middleware('role:admin|project_manager')
                ->name('destroy');

            // Customer Actions
            Route::post('/{customer}/activate', [\App\Http\Controllers\Api\CustomerController::class, 'activate'])
                ->middleware('role:admin|project_manager|sales_manager')
                ->name('activate');
            Route::post('/{customer}/deactivate', [\App\Http\Controllers\Api\CustomerController::class, 'deactivate'])
                ->middleware('role:admin|project_manager|sales_manager')
                ->name('deactivate');
            Route::post('/{customer}/blacklist', [\App\Http\Controllers\Api\CustomerController::class, 'blacklist'])
                ->middleware('role:admin|project_manager')
                ->name('blacklist');
        });

        // Unit Sales (Daire Satışları)
        Route::prefix('unit-sales')->name('unit-sales.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\UnitSaleController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Api\UnitSaleController::class, 'store'])
                ->middleware('role:admin|project_manager|sales_manager')
                ->name('store');
            Route::get('/stats', [\App\Http\Controllers\Api\UnitSaleController::class, 'stats'])->name('stats');
            Route::get('/{unitSale}', [\App\Http\Controllers\Api\UnitSaleController::class, 'show'])->name('show');
            Route::put('/{unitSale}', [\App\Http\Controllers\Api\UnitSaleController::class, 'update'])
                ->middleware('role:admin|project_manager|sales_manager')
                ->name('update');
            Route::delete('/{unitSale}', [\App\Http\Controllers\Api\UnitSaleController::class, 'destroy'])
                ->middleware('role:admin|project_manager')
                ->name('destroy');

            // Sale Actions
            Route::post('/{unitSale}/cancel', [\App\Http\Controllers\Api\UnitSaleController::class, 'cancel'])
                ->middleware('role:admin|project_manager|sales_manager')
                ->name('cancel');
            Route::post('/{unitSale}/complete', [\App\Http\Controllers\Api\UnitSaleController::class, 'complete'])
                ->middleware('role:admin|project_manager|sales_manager')
                ->name('complete');
            Route::post('/{unitSale}/transfer-deed', [\App\Http\Controllers\Api\UnitSaleController::class, 'transferDeed'])
                ->middleware('role:admin|project_manager')
                ->name('transfer-deed');
        });

        // Sale Payments (Satış Ödemeleri)
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\SalePaymentController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Api\SalePaymentController::class, 'store'])
                ->middleware('role:admin|project_manager|sales_manager|accountant')
                ->name('store');
            Route::get('/stats', [\App\Http\Controllers\Api\SalePaymentController::class, 'stats'])->name('stats');
            Route::get('/upcoming', [\App\Http\Controllers\Api\SalePaymentController::class, 'upcoming'])->name('upcoming');
            Route::get('/{payment}', [\App\Http\Controllers\Api\SalePaymentController::class, 'show'])->name('show');
            Route::put('/{payment}', [\App\Http\Controllers\Api\SalePaymentController::class, 'update'])
                ->middleware('role:admin|project_manager|sales_manager|accountant')
                ->name('update');
            Route::delete('/{payment}', [\App\Http\Controllers\Api\SalePaymentController::class, 'destroy'])
                ->middleware('role:admin|project_manager')
                ->name('destroy');

            // Payment Actions
            Route::post('/{payment}/mark-as-paid', [\App\Http\Controllers\Api\SalePaymentController::class, 'markAsPaid'])
                ->middleware('role:admin|project_manager|sales_manager|accountant')
                ->name('mark-as-paid');
            Route::post('/{payment}/approve', [\App\Http\Controllers\Api\SalePaymentController::class, 'approve'])
                ->middleware('role:admin|project_manager')
                ->name('approve');
            Route::post('/{payment}/cancel', [\App\Http\Controllers\Api\SalePaymentController::class, 'cancel'])
                ->middleware('role:admin|project_manager|sales_manager')
                ->name('cancel');

            // Bulk Operations
            Route::post('/calculate-late-fees', [\App\Http\Controllers\Api\SalePaymentController::class, 'calculateLateFees'])
                ->middleware('role:admin|project_manager|accountant')
                ->name('calculate-late-fees');
            Route::post('/send-reminders', [\App\Http\Controllers\Api\SalePaymentController::class, 'sendReminders'])
                ->middleware('role:admin|project_manager|sales_manager')
                ->name('send-reminders');
        });
    });
});