<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| Here is where you can register authentication routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group.
|
*/

// Guest Routes (Not Authenticated)
Route::middleware('guest')->group(function () {
    
    // Login Routes
    Route::get('login', [AuthController::class, 'create'])->name('login');
    Route::post('login', [AuthController::class, 'store'])->name('login.store');
    
    // QR Code Login (for mobile apps and kiosks)
    Route::get('qr-login', [AuthController::class, 'showQrLogin'])->name('qr-login');
    Route::post('qr-login', [AuthController::class, 'qrLogin'])->name('qr-login.store');
    Route::get('qr-login/generate', [AuthController::class, 'generateLoginQr'])->name('qr-login.generate');
    Route::post('qr-login/verify', [AuthController::class, 'verifyLoginQr'])->name('qr-login.verify');
    
    // Employee ID Login (for kiosks without QR scanner)
    Route::get('employee-login', [AuthController::class, 'showEmployeeLogin'])->name('employee-login');
    Route::post('employee-login', [AuthController::class, 'employeeLogin'])->name('employee-login.store');
    
    // Password Reset Routes
    Route::get('forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'update'])->name('password.update');
    
    // Registration Routes (if enabled for certain roles)
    Route::middleware('registration.enabled')->group(function () {
        Route::get('register', [AuthController::class, 'createRegistration'])->name('register');
        Route::post('register', [AuthController::class, 'storeRegistration'])->name('register.store');
    });
    
    // Admin Invitation Routes
    Route::get('invitation/{token}', [AuthController::class, 'showInvitation'])->name('invitation.show');
    Route::post('invitation/{token}', [AuthController::class, 'acceptInvitation'])->name('invitation.accept');
    
    // Employee Onboarding Routes
    Route::get('onboarding/{token}', [AuthController::class, 'showOnboarding'])->name('onboarding.show');
    Route::post('onboarding/{token}', [AuthController::class, 'completeOnboarding'])->name('onboarding.complete');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    
    // Logout
    Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
    
    // Email Verification Routes
    Route::middleware('signed')->group(function () {
        Route::get('verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
            ->name('verification.verify');
    });
    
    Route::middleware('throttle:6,1')->group(function () {
        Route::post('email/verification-notification', [EmailVerificationController::class, 'store'])
            ->name('verification.send');
    });
    
    // Email Verification Notice
    Route::get('verify-email', [EmailVerificationController::class, 'show'])
        ->middleware('throttle:6,1')
        ->name('verification.notice');
    
    // Password Confirmation Routes
    Route::get('confirm-password', [AuthController::class, 'showConfirmPassword'])
        ->name('password.confirm');
    Route::post('confirm-password', [AuthController::class, 'confirmPassword'])
        ->middleware('throttle:6,1')
        ->name('password.confirm.store');
    
    // Two-Factor Authentication Routes
    Route::prefix('two-factor')->name('two-factor.')->group(function () {
        
        // Two-Factor Challenge (when 2FA is required)
        Route::middleware('guest:two-factor')->group(function () {
            Route::get('challenge', [TwoFactorController::class, 'create'])->name('challenge');
            Route::post('challenge', [TwoFactorController::class, 'store'])->name('challenge.store');
        });
        
        // Two-Factor Setup and Management
        Route::middleware('password.confirm')->group(function () {
            
            // Enable/Disable 2FA
            Route::post('enable', [TwoFactorController::class, 'enable'])->name('enable');
            Route::delete('disable', [TwoFactorController::class, 'disable'])->name('disable');
            
            // QR Code and Secret Key
            Route::get('qr-code', [TwoFactorController::class, 'qrCode'])->name('qr-code');
            Route::get('secret-key', [TwoFactorController::class, 'secretKey'])->name('secret-key');
            
            // Recovery Codes
            Route::get('recovery-codes', [TwoFactorController::class, 'recoveryCodes'])->name('recovery-codes');
            Route::post('recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('recovery-codes.regenerate');
            
            // Confirm 2FA Setup
            Route::post('confirm', [TwoFactorController::class, 'confirm'])->name('confirm');
        });
        
        // Recovery Code Login
        Route::post('recovery', [TwoFactorController::class, 'recovery'])->name('recovery');
    });
    
    // Session Management Routes
    Route::prefix('sessions')->name('sessions.')->group(function () {
        Route::get('/', [AuthController::class, 'showSessions'])->name('index');
        Route::delete('/{session}', [AuthController::class, 'destroySession'])->name('destroy');
        Route::delete('/others', [AuthController::class, 'destroyOtherSessions'])
            ->middleware('password.confirm')
            ->name('destroy-others');
    });
    
    // Profile Management Routes
    Route::prefix('profile')->name('profile.')->group(function () {
        
        // Basic Profile
        Route::get('/', [AuthController::class, 'showProfile'])->name('show');
        Route::patch('/', [AuthController::class, 'updateProfile'])->name('update');
        
        // Password Management
        Route::patch('/password', [AuthController::class, 'updatePassword'])
            ->middleware('password.confirm')
            ->name('password.update');
        
        // Avatar/Photo Upload
        Route::post('/avatar', [AuthController::class, 'uploadAvatar'])->name('avatar.upload');
        Route::delete('/avatar', [AuthController::class, 'deleteAvatar'])->name('avatar.delete');
        
        // Account Deletion
        Route::delete('/account', [AuthController::class, 'deleteAccount'])
            ->middleware('password.confirm')
            ->name('delete');
            
        // Privacy Settings
        Route::get('/privacy', [AuthController::class, 'showPrivacySettings'])->name('privacy');
        Route::patch('/privacy', [AuthController::class, 'updatePrivacySettings'])->name('privacy.update');
        
        // Notification Preferences
        Route::get('/notifications', [AuthController::class, 'showNotificationSettings'])->name('notifications');
        Route::patch('/notifications', [AuthController::class, 'updateNotificationSettings'])->name('notifications.update');
        
        // API Token Management
        Route::get('/tokens', [AuthController::class, 'showApiTokens'])->name('tokens');
        Route::post('/tokens', [AuthController::class, 'createApiToken'])->name('tokens.create');
        Route::delete('/tokens/{token}', [AuthController::class, 'deleteApiToken'])->name('tokens.delete');
        
        // Export Personal Data
        Route::post('/export', [AuthController::class, 'exportPersonalData'])->name('export');
    });
    
    // Account Security Routes
    Route::prefix('security')->name('security.')->group(function () {
        
        // Security Overview
        Route::get('/', [AuthController::class, 'showSecurityOverview'])->name('index');
        
        // Login History
        Route::get('/login-history', [AuthController::class, 'showLoginHistory'])->name('login-history');
        
        // Security Events
        Route::get('/events', [AuthController::class, 'showSecurityEvents'])->name('events');
        
        // Trusted Devices
        Route::get('/devices', [AuthController::class, 'showTrustedDevices'])->name('devices');
        Route::delete('/devices/{device}', [AuthController::class, 'removeTrustedDevice'])->name('devices.remove');
        
        // Security Questions (if implemented)
        Route::get('/questions', [AuthController::class, 'showSecurityQuestions'])->name('questions');
        Route::post('/questions', [AuthController::class, 'updateSecurityQuestions'])
            ->middleware('password.confirm')
            ->name('questions.update');
        
        // Backup Email
        Route::get('/backup-email', [AuthController::class, 'showBackupEmail'])->name('backup-email');
        Route::post('/backup-email', [AuthController::class, 'addBackupEmail'])->name('backup-email.add');
        Route::post('/backup-email/verify', [AuthController::class, 'verifyBackupEmail'])->name('backup-email.verify');
        Route::delete('/backup-email', [AuthController::class, 'removeBackupEmail'])->name('backup-email.remove');
    });
    
    // Delegation and Authority Management
    Route::prefix('delegation')->name('delegation.')->middleware('role:admin|project_manager|site_manager')->group(function () {
        
        // View Delegations
        Route::get('/', [AuthController::class, 'showDelegations'])->name('index');
        
        // Create Delegation
        Route::get('/create', [AuthController::class, 'createDelegation'])->name('create');
        Route::post('/', [AuthController::class, 'storeDelegation'])->name('store');
        
        // Manage Delegations
        Route::get('/{delegation}', [AuthController::class, 'showDelegation'])->name('show');
        Route::patch('/{delegation}', [AuthController::class, 'updateDelegation'])->name('update');
        Route::delete('/{delegation}', [AuthController::class, 'revokeDelegation'])->name('revoke');
        
        // Temporary Authority
        Route::post('/temporary', [AuthController::class, 'grantTemporaryAuthority'])->name('temporary');
        Route::delete('/temporary/{authority}', [AuthController::class, 'revokeTemporaryAuthority'])->name('temporary.revoke');
        
        // Delegation History
        Route::get('/history', [AuthController::class, 'delegationHistory'])->name('history');
    });
    
    // Account Linking (for employees with multiple roles)
    Route::prefix('account-linking')->name('account-linking.')->group(function () {
        
        // Link Additional Account
        Route::get('/link', [AuthController::class, 'showAccountLinking'])->name('show');
        Route::post('/link', [AuthController::class, 'linkAccount'])->name('link');
        
        // Switch Between Linked Accounts
        Route::post('/switch/{account}', [AuthController::class, 'switchAccount'])->name('switch');
        
        // Unlink Account
        Route::delete('/unlink/{account}', [AuthController::class, 'unlinkAccount'])
            ->middleware('password.confirm')
            ->name('unlink');
    });
    
    // Emergency Access Routes
    Route::prefix('emergency')->name('emergency.')->group(function () {
        
        // Emergency Contact Management
        Route::get('/contacts', [AuthController::class, 'showEmergencyContacts'])->name('contacts');
        Route::post('/contacts', [AuthController::class, 'addEmergencyContact'])->name('contacts.add');
        Route::delete('/contacts/{contact}', [AuthController::class, 'removeEmergencyContact'])->name('contacts.remove');
        
        // Emergency Access Codes
        Route::post('/access-code', [AuthController::class, 'generateEmergencyAccessCode'])
            ->middleware(['role:admin', 'password.confirm'])
            ->name('access-code.generate');
            
        Route::post('/access-code/use', [AuthController::class, 'useEmergencyAccessCode'])->name('access-code.use');
    });
});

// Admin Routes for User Management
Route::middleware(['auth', 'role:admin'])->prefix('admin/auth')->name('admin.auth.')->group(function () {
    
    // User Account Management
    Route::get('/users', [AuthController::class, 'adminShowUsers'])->name('users.index');
    Route::get('/users/{user}', [AuthController::class, 'adminShowUser'])->name('users.show');
    Route::patch('/users/{user}/status', [AuthController::class, 'adminToggleUserStatus'])->name('users.toggle-status');
    Route::delete('/users/{user}', [AuthController::class, 'adminDeleteUser'])->name('users.delete');
    
    // Force Password Reset
    Route::post('/users/{user}/force-password-reset', [AuthController::class, 'adminForcePasswordReset'])->name('users.force-password-reset');
    
    // User Impersonation (for support purposes)
    Route::post('/users/{user}/impersonate', [AuthController::class, 'impersonateUser'])->name('users.impersonate');
    Route::post('/stop-impersonation', [AuthController::class, 'stopImpersonation'])->name('stop-impersonation');
    
    // Bulk User Operations
    Route::post('/users/bulk-activate', [AuthController::class, 'bulkActivateUsers'])->name('users.bulk-activate');
    Route::post('/users/bulk-deactivate', [AuthController::class, 'bulkDeactivateUsers'])->name('users.bulk-deactivate');
    Route::post('/users/bulk-force-logout', [AuthController::class, 'bulkForceLogout'])->name('users.bulk-force-logout');
    
    // Send User Invitations
    Route::get('/invitations', [AuthController::class, 'showInvitations'])->name('invitations.index');
    Route::post('/invitations/send', [AuthController::class, 'sendUserInvitation'])->name('invitations.send');
    Route::patch('/invitations/{invitation}/resend', [AuthController::class, 'resendInvitation'])->name('invitations.resend');
    Route::delete('/invitations/{invitation}', [AuthController::class, 'cancelInvitation'])->name('invitations.cancel');
    
    // User Activity Monitoring
    Route::get('/activity', [AuthController::class, 'showUserActivity'])->name('activity');
    Route::get('/login-attempts', [AuthController::class, 'showLoginAttempts'])->name('login-attempts');
    Route::get('/security-events', [AuthController::class, 'showSecurityEvents'])->name('security-events');
    
    // System Security Settings
    Route::get('/security-settings', [AuthController::class, 'showSystemSecuritySettings'])->name('security-settings');
    Route::patch('/security-settings', [AuthController::class, 'updateSystemSecuritySettings'])->name('security-settings.update');
    
    // Rate Limiting Management
    Route::get('/rate-limits', [AuthController::class, 'showRateLimits'])->name('rate-limits');
    Route::post('/rate-limits/clear', [AuthController::class, 'clearRateLimits'])->name('rate-limits.clear');
    
    // Authentication Logs
    Route::get('/logs', [AuthController::class, 'showAuthLogs'])->name('logs');
    Route::post('/logs/export', [AuthController::class, 'exportAuthLogs'])->name('logs.export');
});

// Mobile App Specific Auth Routes
Route::prefix('mobile')->name('mobile.')->group(function () {
    
    // Mobile Login
    Route::post('/login', [AuthController::class, 'mobileLogin'])->name('login');
    Route::post('/qr-login', [AuthController::class, 'mobileQrLogin'])->name('qr-login');
    Route::post('/biometric-login', [AuthController::class, 'biometricLogin'])->name('biometric-login');
    
    // Mobile Logout
    Route::post('/logout', [AuthController::class, 'mobileLogout'])
        ->middleware('auth:sanctum')
        ->name('logout');
    
    // Device Registration
    Route::post('/register-device', [AuthController::class, 'registerMobileDevice'])
        ->middleware('auth:sanctum')
        ->name('register-device');
    
    Route::delete('/unregister-device', [AuthController::class, 'unregisterMobileDevice'])
        ->middleware('auth:sanctum')
        ->name('unregister-device');
    
    // Push Notification Token Management
    Route::post('/fcm-token', [AuthController::class, 'updateFcmToken'])
        ->middleware('auth:sanctum')
        ->name('fcm-token');
    
    // Offline Authentication
    Route::post('/offline-token', [AuthController::class, 'generateOfflineToken'])
        ->middleware('auth:sanctum')
        ->name('offline-token');
    
    Route::post('/validate-offline', [AuthController::class, 'validateOfflineAuth'])->name('validate-offline');
});

// Kiosk Specific Auth Routes
Route::prefix('kiosk')->name('kiosk.')->group(function () {
    
    // Kiosk Login (Employee ID + PIN or QR)
    Route::post('/login', [AuthController::class, 'kioskLogin'])->name('login');
    Route::post('/qr-login', [AuthController::class, 'kioskQrLogin'])->name('qr-login');
    Route::post('/logout', [AuthController::class, 'kioskLogout'])->name('logout');
    
    // Kiosk Session Management
    Route::post('/extend-session', [AuthController::class, 'extendKioskSession'])
        ->middleware('auth:kiosk')
        ->name('extend-session');
    
    // Admin Access on Kiosk
    Route::post('/admin-access', [AuthController::class, 'kioskAdminAccess'])->name('admin-access');
    Route::post('/exit-admin', [AuthController::class, 'exitKioskAdmin'])
        ->middleware('auth:kiosk-admin')
        ->name('exit-admin');
});

// SSO Integration Routes (if needed)
Route::prefix('sso')->name('sso.')->group(function () {
    
    // SAML Routes
    Route::get('/saml/login', [AuthController::class, 'samlLogin'])->name('saml.login');
    Route::post('/saml/acs', [AuthController::class, 'samlAcs'])->name('saml.acs');
    Route::get('/saml/metadata', [AuthController::class, 'samlMetadata'])->name('saml.metadata');
    Route::post('/saml/logout', [AuthController::class, 'samlLogout'])->name('saml.logout');
    
    // LDAP Authentication (if implemented)
    Route::post('/ldap/login', [AuthController::class, 'ldapLogin'])->name('ldap.login');
    Route::post('/ldap/sync', [AuthController::class, 'ldapSync'])
        ->middleware(['auth', 'role:admin'])
        ->name('ldap.sync');
});

// API Authentication Routes (for external integrations)
Route::prefix('api-auth')->name('api-auth.')->group(function () {
    
    // Client Credentials Flow
    Route::post('/client-credentials', [AuthController::class, 'clientCredentialsAuth'])->name('client-credentials');
    
    // API Key Authentication
    Route::post('/api-key/validate', [AuthController::class, 'validateApiKey'])->name('api-key.validate');
    Route::post('/api-key/refresh', [AuthController::class, 'refreshApiKey'])
        ->middleware('auth:api-key')
        ->name('api-key.refresh');
    
    // Webhook Authentication
    Route::post('/webhook/verify', [AuthController::class, 'verifyWebhookAuth'])->name('webhook.verify');
});