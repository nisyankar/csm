<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global middleware
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // API middleware
        $middleware->api(append: [
            \App\Http\Middleware\ApiRateLimit::class,
        ]);

        // Middleware aliases
        $middleware->alias([
            // Authentication & Authorization
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,

            // Resource Access Control
            'project.access' => \App\Http\Middleware\ProjectAccess::class,
            'department.access' => \App\Http\Middleware\DepartmentAccess::class,

            // Security & Validation
            'qr.validate' => \App\Http\Middleware\QRCodeValidation::class,
            'file.security' => \App\Http\Middleware\FileUploadSecurity::class,
            'api.limit' => \App\Http\Middleware\ApiRateLimit::class,

            // Standard Laravel middleware
            'auth' => \App\Http\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            'signed' => \App\Http\Middleware\ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

            'system.admin' => \App\Http\Middleware\EnsureSystemAdmin::class,
            'leave.management' => \App\Http\Middleware\EnsureLeaveManagement::class,
        ]);

        // Middleware groups
        $middleware->group('admin', [
            'auth',
            'role:admin'
        ]);

        $middleware->group('hr', [
            'auth',
            'role:admin,hr'
        ]);

        $middleware->group('manager', [
            'auth',
            'role:admin,hr,project_manager,site_manager'
        ]);

        $middleware->group('foreman', [
            'auth',
            'role:admin,hr,project_manager,site_manager,foreman'
        ]);

        $middleware->group('employee', [
            'auth',
            'role:admin,hr,project_manager,site_manager,foreman,employee'
        ]);

        $middleware->group('api.auth', [
            'auth:sanctum',
            'api.limit'
        ]);

        $middleware->group('api.public', [
            'api.limit:50' // Limited rate for public API
        ]);

        $middleware->group('qr.timesheet', [
            'auth:sanctum',
            'qr.validate:timesheet',
            'api.limit'
        ]);

        $middleware->group('file.upload', [
            'auth',
            'file.security'
        ]);

        $middleware->group('project.management', [
            'auth',
            'role:admin,hr,project_manager,site_manager',
            'project.access'
        ]);

        $middleware->group('department.management', [
            'auth',
            'role:admin,hr,project_manager,site_manager,foreman',
            'department.access'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
