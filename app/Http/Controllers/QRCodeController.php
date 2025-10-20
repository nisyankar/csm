<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Project;
use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeController extends Controller
{
    /**
     * Display QR code management page
     */
    public function index(): Response
    {
        $user = Auth::user();
        
        // Get employees for QR code generation
        $employeesQuery = Employee::with(['user', 'currentProjects']);

        // Filter based on user role
        if (!$user->hasRole(['admin', 'project_manager'])) {
            if ($user->hasRole('site_manager')) {
                $managedProjects = Project::where('site_manager_id', $user->employee->id)->pluck('id');
                $employeesQuery->whereHas('projects', function ($q) use ($managedProjects) {
                    $q->whereIn('projects.id', $managedProjects);
                });
            } elseif ($user->hasRole('employee')) {
                $employeesQuery->where('id', $user->employee->id);
            }
        }

        $employees = $employeesQuery->get();

        // Get projects for location-based QR codes
        $projectsQuery = Project::select('id', 'name', 'project_code', 'latitude', 'longitude');
        
        if (!$user->hasRole(['admin', 'project_manager'])) {
            if ($user->hasRole('site_manager')) {
                $projectsQuery->where('site_manager_id', $user->employee->id);
            }
        }

        $projects = $projectsQuery->get();

        // Get recent QR scans (from cache or database)
        $recentScans = $this->getRecentScans($user);

        return Inertia::render('QRCode/Index', [
            'employees' => $employees,
            'projects' => $projects,
            'recentScans' => $recentScans,
            'canManageQR' => $user->hasAnyRole(['admin', 'project_manager', 'site_manager']),
        ]);
    }

    /**
     * Generate QR code for employee
     */
    public function generateEmployeeQR(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:checkin,checkout,both',
            'project_id' => 'nullable|exists:projects,id',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        
        // Check authorization
        $this->authorizeQRGeneration($employee);

        // Generate unique token
        $token = Str::random(32);
        
        // QR Code data structure
        $qrData = [
            'type' => 'employee_timesheet',
            'token' => $token,
            'employee_id' => $employee->id,
            'action' => $validated['type'],
            'project_id' => $validated['project_id'] ?? null,
            'generated_at' => now()->toISOString(),
            'expires_at' => $validated['expires_at'] ? Carbon::parse($validated['expires_at'])->toISOString() : null,
            'generated_by' => Auth::id(),
        ];

        // Cache the QR data for verification
        $cacheKey = "qr_employee_{$token}";
        $expiresIn = $validated['expires_at'] ? 
            Carbon::parse($validated['expires_at'])->diffInMinutes(now()) : 
            (24 * 60); // Default 24 hours

        Cache::put($cacheKey, $qrData, $expiresIn);

        // Generate QR code
        $qrCodeData = json_encode($qrData);
        $qrCode = QrCode::format('png')
            ->size(300)
            ->margin(2)
            ->generate($qrCodeData);

        // Save QR code image
        $filename = "qr_employee_{$employee->employee_code}_{$token}.png";
        $path = "qr_codes/employees/{$filename}";
        Storage::disk('public')->put($path, $qrCode);

        // Log QR generation
        $this->logQRActivity('generated', $employee->id, [
            'type' => 'employee',
            'action' => $validated['type'],
            'token' => $token,
            'generated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'qr_code_url' => Storage::url($path),
            'qr_data' => $qrData,
            'expires_at' => $qrData['expires_at'],
            'download_url' => route('qr.download', ['type' => 'employee', 'token' => $token]),
        ]);
    }

    /**
     * Generate QR code for project location
     */
    public function generateProjectQR(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'location_name' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|integer|min:10|max:1000', // meters
            'expires_at' => 'nullable|date|after:now',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        
        // Check authorization
        $this->authorizeProjectQR($project);

        // Generate unique token
        $token = Str::random(32);
        
        // QR Code data structure
        $qrData = [
            'type' => 'project_location',
            'token' => $token,
            'project_id' => $project->id,
            'location_name' => $validated['location_name'],
            'latitude' => $validated['latitude'] ?? $project->latitude,
            'longitude' => $validated['longitude'] ?? $project->longitude,
            'radius' => $validated['radius'] ?? 100, // Default 100 meters
            'generated_at' => now()->toISOString(),
            'expires_at' => $validated['expires_at'] ? Carbon::parse($validated['expires_at'])->toISOString() : null,
            'generated_by' => Auth::id(),
        ];

        // Cache the QR data
        $cacheKey = "qr_project_{$token}";
        $expiresIn = $validated['expires_at'] ? 
            Carbon::parse($validated['expires_at'])->diffInMinutes(now()) : 
            (30 * 24 * 60); // Default 30 days

        Cache::put($cacheKey, $qrData, $expiresIn);

        // Generate QR code
        $qrCodeData = json_encode($qrData);
        $qrCode = QrCode::format('png')
            ->size(400)
            ->margin(2)
            ->generate($qrCodeData);

        // Save QR code image
        $filename = "qr_project_{$project->project_code}_{$token}.png";
        $path = "qr_codes/projects/{$filename}";
        Storage::disk('public')->put($path, $qrCode);

        // Log QR generation
        $this->logQRActivity('generated', null, [
            'type' => 'project',
            'project_id' => $project->id,
            'token' => $token,
            'generated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'qr_code_url' => Storage::url($path),
            'qr_data' => $qrData,
            'expires_at' => $qrData['expires_at'],
            'download_url' => route('qr.download', ['type' => 'project', 'token' => $token]),
        ]);
    }

    /**
     * Scan QR code and process
     */
    public function scanQR(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_data' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
        ]);

        try {
            // Decode QR data
            $qrData = json_decode($validated['qr_data'], true);
            
            if (!$qrData || !isset($qrData['token'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Geçersiz QR kod formatı.',
                ], 422);
            }

            // Verify QR code from cache
            $cacheKey = $qrData['type'] === 'employee_timesheet' ? 
                "qr_employee_{$qrData['token']}" : 
                "qr_project_{$qrData['token']}";

            $cachedData = Cache::get($cacheKey);
            
            if (!$cachedData) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR kod süresi dolmuş veya geçersiz.',
                ], 422);
            }

            // Check expiration
            if (isset($cachedData['expires_at']) && $cachedData['expires_at']) {
                $expiresAt = Carbon::parse($cachedData['expires_at']);
                if (now() > $expiresAt) {
                    Cache::forget($cacheKey);
                    return response()->json([
                        'success' => false,
                        'message' => 'QR kod süresi dolmuş.',
                    ], 422);
                }
            }

            // Process based on QR type
            if ($qrData['type'] === 'employee_timesheet') {
                return $this->processEmployeeTimesheetQR($cachedData, $validated);
            } elseif ($qrData['type'] === 'project_location') {
                return $this->processProjectLocationQR($cachedData, $validated);
            }

            return response()->json([
                'success' => false,
                'message' => 'Desteklenmeyen QR kod türü.',
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'QR kod işlenirken hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download QR code image
     */
    public function downloadQR(Request $request, string $type, string $token)
    {
        // Verify token
        $cacheKey = $type === 'employee' ? "qr_employee_{$token}" : "qr_project_{$token}";
        $qrData = Cache::get($cacheKey);

        if (!$qrData) {
            abort(404, 'QR kod bulunamadı veya süresi dolmuş.');
        }

        // Check authorization
        $user = Auth::user();
        if (!$user->hasAnyRole(['admin', 'project_manager', 'site_manager'])) {
            abort(403, 'Bu işlem için yetkiniz yok.');
        }

        // Find the file
        $pattern = $type === 'employee' ? 
            "qr_codes/employees/qr_employee_*_{$token}.png" : 
            "qr_codes/projects/qr_project_*_{$token}.png";

        $files = Storage::disk('public')->files(dirname($pattern));
        $file = collect($files)->first(function ($file) use ($token) {
            return str_contains($file, $token);
        });

        if (!$file) {
            abort(404, 'QR kod dosyası bulunamadı.');
        }

        $filename = basename($file);
        return Storage::disk('public')->download($file, $filename);
    }

    /**
     * Get QR scan history
     */
    public function scanHistory(Request $request): Response
    {
        $validated = $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'project_id' => 'nullable|exists:projects,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'action' => 'nullable|in:generated,scanned,expired',
        ]);

        // This would typically query a qr_activities table
        // For now, we'll return cached scan data
        $activities = $this->getQRActivities($validated);

        return Inertia::render('QRCode/History', [
            'activities' => $activities,
            'filters' => $validated,
            'employees' => Employee::select('id', 'first_name', 'last_name', 'employee_code')->get(),
            'projects' => Project::select('id', 'name', 'project_code')->get(),
        ]);
    }

    /**
     * Bulk generate QR codes
     */
    public function bulkGenerate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:employees,projects',
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer',
            'settings' => 'required|array',
            'settings.expires_at' => 'nullable|date|after:now',
            'settings.action' => 'required_if:type,employees|in:checkin,checkout,both',
            'settings.project_id' => 'nullable|exists:projects,id',
        ]);

        $user = Auth::user();
        
        if (!$user->hasAnyRole(['admin', 'project_manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bu işlem için yetkiniz yok.',
            ], 403);
        }

        $results = [];
        $errors = [];

        if ($validated['type'] === 'employees') {
            $employees = Employee::whereIn('id', $validated['ids'])->get();
            
            foreach ($employees as $employee) {
                try {
                    $result = $this->generateEmployeeQRCode($employee, $validated['settings']);
                    $results[] = [
                        'employee' => $employee,
                        'qr_data' => $result,
                    ];
                } catch (\Exception $e) {
                    $errors[] = [
                        'employee' => $employee,
                        'error' => $e->getMessage(),
                    ];
                }
            }
        } elseif ($validated['type'] === 'projects') {
            $projects = Project::whereIn('id', $validated['ids'])->get();
            
            foreach ($projects as $project) {
                try {
                    $result = $this->generateProjectQRCode($project, $validated['settings']);
                    $results[] = [
                        'project' => $project,
                        'qr_data' => $result,
                    ];
                } catch (\Exception $e) {
                    $errors[] = [
                        'project' => $project,
                        'error' => $e->getMessage(),
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results,
            'errors' => $errors,
            'total_generated' => count($results),
            'total_errors' => count($errors),
        ]);
    }

    /**
     * Delete/invalidate QR code
     */
    public function deleteQR(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'type' => 'required|in:employee,project',
        ]);

        $user = Auth::user();
        
        if (!$user->hasAnyRole(['admin', 'project_manager', 'site_manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bu işlem için yetkiniz yok.',
            ], 403);
        }

        // Remove from cache
        $cacheKey = $validated['type'] === 'employee' ? 
            "qr_employee_{$validated['token']}" : 
            "qr_project_{$validated['token']}";

        Cache::forget($cacheKey);

        // Delete physical file
        $pattern = $validated['type'] === 'employee' ? 
            "qr_codes/employees/qr_employee_*_{$validated['token']}.png" : 
            "qr_codes/projects/qr_project_*_{$validated['token']}.png";

        $files = Storage::disk('public')->files(dirname($pattern));
        $file = collect($files)->first(function ($file) use ($validated) {
            return str_contains($file, $validated['token']);
        });

        if ($file) {
            Storage::disk('public')->delete($file);
        }

        // Log deletion
        $this->logQRActivity('deleted', null, [
            'type' => $validated['type'],
            'token' => $validated['token'],
            'deleted_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'QR kod başarıyla silindi.',
        ]);
    }

    // Private helper methods

    private function processEmployeeTimesheetQR(array $qrData, array $scanData): JsonResponse
    {
        $employee = Employee::find($qrData['employee_id']);
        
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Çalışan bulunamadı.',
            ], 404);
        }

        // Check if user can scan for this employee
        $user = Auth::user();
        if (!$user->hasAnyRole(['admin', 'project_manager', 'site_manager']) && 
            $user->employee->id !== $employee->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bu çalışan için puantaj girişi yapmaya yetkiniz yok.',
            ], 403);
        }

        // Get today's timesheet
        $today = now()->toDateString();
        $timesheet = Timesheet::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        $action = $qrData['action'];
        $currentTime = now();

        // Process based on action
        if ($action === 'checkin' || $action === 'both') {
            if (!$timesheet) {
                // Create new timesheet entry
                $timesheet = Timesheet::create([
                    'employee_id' => $employee->id,
                    'project_id' => $qrData['project_id'],
                    'department_id' => null, // Will be determined later
                    'date' => $today,
                    'check_in' => $currentTime,
                    'status' => 'present',
                    'check_in_method' => 'qr_code',
                    'check_in_latitude' => $scanData['latitude'] ?? null,
                    'check_in_longitude' => $scanData['longitude'] ?? null,
                    'check_in_accuracy' => $scanData['accuracy'] ?? null,
                ]);

                $message = 'Giriş başarıyla kaydedildi.';
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Bugün için zaten giriş kaydı mevcut.',
                ], 422);
            }
        }

        if ($action === 'checkout' || ($action === 'both' && $timesheet->check_in)) {
            if ($timesheet && $timesheet->check_in && !$timesheet->check_out) {
                // Update checkout
                $timesheet->update([
                    'check_out' => $currentTime,
                    'check_out_method' => 'qr_code',
                    'check_out_latitude' => $scanData['latitude'] ?? null,
                    'check_out_longitude' => $scanData['longitude'] ?? null,
                    'check_out_accuracy' => $scanData['accuracy'] ?? null,
                ]);

                // Calculate work hours
                $timesheet->calculateHours();

                $message = 'Çıkış başarıyla kaydedildi.';
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Çıkış yapmak için önce giriş yapmalısınız.',
                ], 422);
            }
        }

        // Log scan activity
        $this->logQRActivity('scanned', $employee->id, [
            'type' => 'employee',
            'action' => $action,
            'timesheet_id' => $timesheet->id,
            'scanned_by' => Auth::id(),
            'location' => [
                'latitude' => $scanData['latitude'] ?? null,
                'longitude' => $scanData['longitude'] ?? null,
                'accuracy' => $scanData['accuracy'] ?? null,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
            'timesheet' => $timesheet->fresh(),
            'employee' => $employee,
        ]);
    }

    private function processProjectLocationQR(array $qrData, array $scanData): JsonResponse
    {
        // Verify location if coordinates provided
        if (isset($scanData['latitude'], $scanData['longitude']) && 
            isset($qrData['latitude'], $qrData['longitude'])) {
            
            $distance = $this->calculateDistance(
                $scanData['latitude'], $scanData['longitude'],
                $qrData['latitude'], $qrData['longitude']
            );

            if ($distance > $qrData['radius']) {
                return response()->json([
                    'success' => false,
                    'message' => "Proje lokasyonuna çok uzaksınız. Mesafe: {$distance}m (İzin verilen: {$qrData['radius']}m)",
                ], 422);
            }
        }

        // Log successful location verification
        $this->logQRActivity('scanned', null, [
            'type' => 'project_location',
            'project_id' => $qrData['project_id'],
            'scanned_by' => Auth::id(),
            'location' => [
                'latitude' => $scanData['latitude'] ?? null,
                'longitude' => $scanData['longitude'] ?? null,
                'accuracy' => $scanData['accuracy'] ?? null,
                'distance' => $distance ?? null,
            ],
        ]);

        $project = Project::find($qrData['project_id']);

        return response()->json([
            'success' => true,
            'message' => 'Lokasyon doğrulandı.',
            'project' => $project,
            'location' => $qrData['location_name'],
            'verified_location' => [
                'latitude' => $scanData['latitude'] ?? null,
                'longitude' => $scanData['longitude'] ?? null,
                'distance' => $distance ?? null,
            ],
        ]);
    }

    private function authorizeQRGeneration(Employee $employee): void
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['admin', 'project_manager', 'site_manager'])) {
            abort(403, 'QR kod oluşturma yetkiniz yok.');
        }

        if ($user->hasRole('site_manager')) {
            $managedProjects = Project::where('site_manager_id', $user->employee->id)->pluck('id');
            $employeeProjects = $employee->projects()->pluck('projects.id');
            
            if ($managedProjects->intersect($employeeProjects)->isEmpty()) {
                abort(403, 'Bu çalışan için QR kod oluşturma yetkiniz yok.');
            }
        }
    }

    private function authorizeProjectQR(Project $project): void
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['admin', 'project_manager', 'site_manager'])) {
            abort(403, 'Proje QR kodu oluşturma yetkiniz yok.');
        }

        if ($user->hasRole('site_manager') && $project->site_manager_id !== $user->employee->id) {
            abort(403, 'Bu proje için QR kod oluşturma yetkiniz yok.');
        }
    }

    private function generateEmployeeQRCode(Employee $employee, array $settings): array
    {
        $token = Str::random(32);
        
        $qrData = [
            'type' => 'employee_timesheet',
            'token' => $token,
            'employee_id' => $employee->id,
            'action' => $settings['action'],
            'project_id' => $settings['project_id'] ?? null,
            'generated_at' => now()->toISOString(),
            'expires_at' => $settings['expires_at'] ? Carbon::parse($settings['expires_at'])->toISOString() : null,
            'generated_by' => Auth::id(),
        ];

        // Cache and generate QR code similar to generateEmployeeQR method
        // ... (implementation details)

        return $qrData;
    }

    private function generateProjectQRCode(Project $project, array $settings): array
    {
        $token = Str::random(32);
        
        $qrData = [
            'type' => 'project_location',
            'token' => $token,
            'project_id' => $project->id,
            'location_name' => $project->name,
            'latitude' => $project->latitude,
            'longitude' => $project->longitude,
            'radius' => $settings['radius'] ?? 100,
            'generated_at' => now()->toISOString(),
            'expires_at' => $settings['expires_at'] ? Carbon::parse($settings['expires_at'])->toISOString() : null,
            'generated_by' => Auth::id(),
        ];

        // Cache and generate QR code similar to generateProjectQR method
        // ... (implementation details)

        return $qrData;
    }

    private function getRecentScans(User $user): array
    {
        // This would typically query a qr_activities table
        // For demo purposes, return empty array
        return [];
    }

    private function getQRActivities(array $filters): array
    {
        // This would typically query a qr_activities table
        // For demo purposes, return empty array
        return [];
    }

    private function logQRActivity(string $action, ?int $employeeId, array $data): void
    {
        // This would typically log to a qr_activities table
        // For now, we can log to Laravel's log or cache
        $logData = array_merge($data, [
            'action' => $action,
            'employee_id' => $employeeId,
            'timestamp' => now()->toISOString(),
            'user_id' => Auth::id(),
        ]);

        // Cache recent activities
        $cacheKey = "qr_activities_" . Auth::id();
        $activities = Cache::get($cacheKey, []);
        array_unshift($activities, $logData);
        
        // Keep only last 50 activities
        $activities = array_slice($activities, 0, 50);
        
        Cache::put($cacheKey, $activities, 24 * 60); // 24 hours
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Earth radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2); // Distance in meters
    }
}