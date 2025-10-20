<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Employee;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Update last login information
        $user = Auth::user();
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'failed_login_attempts' => 0,
        ]);

        // Update login history
        $this->updateLoginHistory($user, $request);

        // Redirect based on user type
        return redirect()->intended($this->getRedirectPath($user));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Get user profile information
     */
    public function profile(): Response
    {
        $user = Auth::user();
        $employee = $user->employee;

        return Inertia::render('Auth/Profile', [
            'user' => $user,
            'employee' => $employee,
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'roles' => $user->getRoleNames(),
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'language' => 'required|string|in:tr,en',
            'timezone' => 'required|string',
            'theme_preference' => 'required|string|in:light,dark,auto',
            'notification_preferences' => 'nullable|array',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && \Storage::exists($user->avatar)) {
                \Storage::delete($user->avatar);
            }

            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $avatarPath;
        }

        $user->update($validated);

        return back()->with('success', 'Profil başarıyla güncellendi.');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->update([
            'password' => bcrypt($validated['password']),
            'password_changed_at' => now(),
            'force_password_change' => false,
        ]);

        return back()->with('success', 'Şifre başarıyla güncellendi.');
    }

    /**
     * Enable two-factor authentication
     */
    public function enableTwoFactor(Request $request): RedirectResponse
    {
        $request->validate([
            'two_factor_code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        
        // Here you would verify the 2FA code
        // For now, we'll just enable it
        $user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => encrypt('sample_secret'),
        ]);

        return back()->with('success', 'İki faktörlü kimlik doğrulama etkinleştirildi.');
    }

    /**
     * Disable two-factor authentication
     */
    public function disableTwoFactor(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
        ]);

        return back()->with('success', 'İki faktörlü kimlik doğrulama devre dışı bırakıldı.');
    }

    /**
     * Get dashboard data for authenticated user
     */
    public function getDashboardData(): array
    {
        $user = Auth::user();
        $employee = $user->employee;

        $data = [
            'user' => $user,
            'employee' => $employee,
            'current_project' => $employee?->currentProject,
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'roles' => $user->getRoleNames(),
        ];

        // Add role-specific data
        if ($user->hasRole(['admin', 'hr'])) {
            $data['total_employees'] = Employee::active()->count();
            $data['total_projects'] = \App\Models\Project::active()->count();
            $data['pending_timesheets'] = \App\Models\Timesheet::where('approval_status', 'pending')->count();
            $data['pending_leaves'] = \App\Models\LeaveRequest::where('status', 'pending')->count();
        }

        if ($user->hasRole(['project_manager', 'site_manager'])) {
            $projects = $employee?->managedProjects ?? collect();
            $data['managed_projects'] = $projects;
            $data['team_count'] = $projects->sum(function($project) {
                return $project->employees()->count();
            });
        }

        if ($user->hasRole('employee')) {
            $data['remaining_leave_days'] = $employee?->remaining_leave_days ?? 0;
            $data['this_month_hours'] = $employee?->timesheets()
                ->whereMonth('work_date', now()->month)
                ->sum('total_minutes') / 60 ?? 0;
        }

        return $data;
    }

    /**
     * Update login history
     */
    private function updateLoginHistory(User $user, Request $request): void
    {
        $history = $user->login_history ?? [];
        
        // Add new login record
        array_unshift($history, [
            'timestamp' => now()->toISOString(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'location' => $this->getLocationFromIP($request->ip()),
        ]);
        
        // Keep only last 10 logins
        $history = array_slice($history, 0, 10);
        
        $user->update(['login_history' => $history]);
    }

    /**
     * Get location from IP (simplified)
     */
    private function getLocationFromIP(string $ip): string
    {
        // In a real application, you would use a service like MaxMind GeoIP
        // For now, return a placeholder
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'Localhost';
        }
        
        return 'Unknown Location';
    }

    /**
     * Get redirect path based on user role
     */
    private function getRedirectPath(User $user): string
    {
        if ($user->hasRole('admin')) {
            return '/admin/dashboard';
        }

        if ($user->hasRole(['project_manager', 'site_manager'])) {
            return '/manager/dashboard';
        }

        if ($user->hasRole('foreman')) {
            return '/foreman/dashboard';
        }

        if ($user->hasRole('hr')) {
            return '/hr/dashboard';
        }

        return '/dashboard';
    }

    /**
     * Check if user account is locked
     */
    public function checkAccountLock(Request $request): bool
    {
        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return false;
        }

        return $user->account_locked_until && now() < $user->account_locked_until;
    }

    /**
     * Record failed login attempt
     */
    public function recordFailedLogin(Request $request): void
    {
        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return;
        }

        $attempts = $user->failed_login_attempts + 1;
        
        $updateData = ['failed_login_attempts' => $attempts];
        
        // Lock account after 5 failed attempts for 15 minutes
        if ($attempts >= 5) {
            $updateData['account_locked_until'] = now()->addMinutes(15);
        }
        
        $user->update($updateData);
    }

    /**
     * Get user settings
     */
    public function settings(): Response
    {
        $user = Auth::user();

        return Inertia::render('Auth/Settings', [
            'user' => $user,
            'employee' => $user->employee,
            'login_history' => array_slice($user->login_history ?? [], 0, 5),
            'available_timezones' => [
                'Europe/Istanbul' => 'İstanbul (UTC+3)',
                'UTC' => 'UTC (UTC+0)',
                'Europe/London' => 'Londra (UTC+0/+1)',
                'America/New_York' => 'New York (UTC-5/-4)',
            ],
            'available_languages' => [
                'tr' => 'Türkçe',
                'en' => 'English',
            ],
        ]);
    }

    /**
     * Update user settings
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'language' => 'required|string|in:tr,en',
            'timezone' => 'required|string',
            'theme_preference' => 'required|string|in:light,dark,auto',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'show_help_tooltips' => 'boolean',
            'session_timeout' => 'required|integer|min:15|max:480',
            'auto_logout_enabled' => 'boolean',
        ]);

        $user->update($validated);

        return back()->with('success', 'Ayarlar başarıyla güncellendi.');
    }

    /**
     * Delegate authority to another user
     */
    public function delegateAuthority(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delegate_to' => 'required|exists:users,id',
            'delegation_start' => 'required|date|after_or_equal:today',
            'delegation_end' => 'required|date|after:delegation_start',
            'delegation_reason' => 'required|string|max:500',
        ]);

        $user = Auth::user();
        $user->update($validated);

        return back()->with('success', 'Yetki delegasyonu başarıyla ayarlandı.');
    }

    /**
     * Revoke delegation
     */
    public function revokeDelegation(): RedirectResponse
    {
        $user = Auth::user();
        $user->update([
            'delegate_to' => null,
            'delegation_start' => null,
            'delegation_end' => null,
            'delegation_reason' => null,
        ]);

        return back()->with('success', 'Yetki delegasyonu iptal edildi.');
    }
}