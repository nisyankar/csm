<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\TimesheetResource;
use App\Models\Timesheet;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TimesheetController extends Controller
{
    /**
     * Puantaj listesi
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Timesheet::with(['employee', 'project']);

        // Kullanıcı employee ise sadece kendi puantajlarını görebilir
        $user = $request->user();
        if ($user->user_type === 'employee' && $user->employee) {
            $query->where('employee_id', $user->employee->id);
        }

        // Filtreler
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('date')) {
            $query->whereDate('work_date', $request->date);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('work_date', [$request->start_date, $request->end_date]);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Sıralama
        $query->orderBy('work_date', 'desc')->orderBy('check_in_time', 'desc');

        // Sayfalama
        $perPage = $request->input('per_page', 15);
        $timesheets = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => TimesheetResource::collection($timesheets),
            'meta' => [
                'current_page' => $timesheets->currentPage(),
                'last_page' => $timesheets->lastPage(),
                'per_page' => $timesheets->perPage(),
                'total' => $timesheets->total(),
            ],
        ], 200);
    }

    /**
     * Mobil uygulama için clock-in (giriş yapma)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clockIn(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'check_in_time' => 'nullable|date_format:H:i:s',
            'check_in_method' => 'nullable|in:manual,qr,gps,biometric',
            'check_in_location' => 'nullable|array',
            'check_in_location.latitude' => 'nullable|numeric',
            'check_in_location.longitude' => 'nullable|numeric',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        // Kullanıcının employee kaydı olmalı
        if (!$user->employee) {
            return response()->json([
                'success' => false,
                'message' => 'Personel kaydınız bulunamadı.',
            ], 400);
        }

        $employee = $user->employee;

        // Bugün için zaten giriş yapmış mı kontrol et
        $today = Carbon::today();
        $existingTimesheet = Timesheet::where('employee_id', $employee->id)
            ->whereDate('work_date', $today)
            ->first();

        if ($existingTimesheet && $existingTimesheet->check_in_time) {
            return response()->json([
                'success' => false,
                'message' => 'Bugün için zaten giriş yapmışsınız.',
                'data' => new TimesheetResource($existingTimesheet),
            ], 400);
        }

        // Proje erişimi kontrolü
        if (!$user->canAccessProject($request->project_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Bu projeye erişim yetkiniz yok.',
            ], 403);
        }

        // GPS doğrulama
        $checkInMethod = $request->check_in_method ?? 'manual';
        if ($checkInMethod === 'gps') {
            $gpsValidation = $this->validateGpsLocation($request->project_id, $request->check_in_location);
            if (!$gpsValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $gpsValidation['message'],
                    'data' => $gpsValidation['data'] ?? null,
                ], 400);
            }
        }

        try {
            DB::beginTransaction();

            $checkInTime = $request->check_in_time ?? Carbon::now()->format('H:i:s');

            $timesheet = Timesheet::create([
                'employee_id' => $employee->id,
                'project_id' => $request->project_id,
                'work_date' => $today,
                'check_in_time' => $checkInTime,
                'check_in_method' => $checkInMethod,
                'check_in_location' => $request->check_in_location,
                'entered_by' => $user->id,
                'status' => 'active',
                'approval_status' => 'draft',
                'notes' => $request->notes,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Giriş başarıyla kaydedildi',
                'data' => new TimesheetResource($timesheet->load(['employee', 'project'])),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Giriş kaydedilirken bir hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mobil uygulama için clock-out (çıkış yapma)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clockOut(Request $request)
    {
        $request->validate([
            'timesheet_id' => 'nullable|exists:timesheets,id',
            'check_out_time' => 'nullable|date_format:H:i:s',
            'check_out_method' => 'nullable|in:manual,qr,gps,biometric',
            'check_out_location' => 'nullable|array',
            'check_out_location.latitude' => 'nullable|numeric',
            'check_out_location.longitude' => 'nullable|numeric',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        if (!$user->employee) {
            return response()->json([
                'success' => false,
                'message' => 'Personel kaydınız bulunamadı.',
            ], 400);
        }

        $employee = $user->employee;

        // Timesheet bul
        if ($request->has('timesheet_id')) {
            $timesheet = Timesheet::find($request->timesheet_id);
        } else {
            // Bugün için açık puantaj kaydı bul
            $timesheet = Timesheet::where('employee_id', $employee->id)
                ->whereDate('work_date', Carbon::today())
                ->whereNotNull('check_in_time')
                ->whereNull('check_out_time')
                ->first();
        }

        if (!$timesheet) {
            return response()->json([
                'success' => false,
                'message' => 'Açık puantaj kaydı bulunamadı. Önce giriş yapmanız gerekiyor.',
            ], 404);
        }

        // Kullanıcı kendi kaydına mı erişiyor kontrol et
        if ($timesheet->employee_id !== $employee->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bu kayda erişim yetkiniz yok.',
            ], 403);
        }

        // GPS doğrulama
        $checkOutMethod = $request->check_out_method ?? 'manual';
        if ($checkOutMethod === 'gps') {
            $gpsValidation = $this->validateGpsLocation($timesheet->project_id, $request->check_out_location);
            if (!$gpsValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $gpsValidation['message'],
                    'data' => $gpsValidation['data'] ?? null,
                ], 400);
            }
        }

        try {
            DB::beginTransaction();

            $checkOutTime = $request->check_out_time ?? Carbon::now()->format('H:i:s');

            // Toplam çalışma saati hesapla
            $checkIn = Carbon::parse($timesheet->work_date->format('Y-m-d') . ' ' . $timesheet->check_in_time);
            $checkOut = Carbon::parse($timesheet->work_date->format('Y-m-d') . ' ' . $checkOutTime);

            // Eğer çıkış saati giriş saatinden önceyse, ertesi güne geçmiş demektir
            if ($checkOut->lessThan($checkIn)) {
                $checkOut->addDay();
            }

            $totalMinutes = $checkIn->diffInMinutes($checkOut);
            $totalHours = round($totalMinutes / 60, 2);

            // Mola süresi varsa çıkar (varsayılan 1 saat)
            $breakDuration = $timesheet->break_duration ?? 60;
            $workingMinutes = max(0, $totalMinutes - $breakDuration);
            $workingHours = round($workingMinutes / 60, 2);

            // Mesai hesaplama (8 saatten fazlası)
            $regularHours = min($workingHours, 8);
            $overtimeHours = max(0, $workingHours - 8);

            $timesheet->update([
                'check_out_time' => $checkOutTime,
                'check_out_method' => $checkOutMethod,
                'check_out_location' => $request->check_out_location,
                'total_hours' => $workingHours,
                'regular_hours' => $regularHours,
                'overtime_hours' => $overtimeHours,
                'status' => 'completed',
                'notes' => $request->notes ?? $timesheet->notes,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Çıkış başarıyla kaydedildi',
                'data' => new TimesheetResource($timesheet->load(['employee', 'project'])),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Çıkış kaydedilirken bir hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bugünkü puantaj durumu
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function todayStatus(Request $request)
    {
        $user = $request->user();

        if (!$user->employee) {
            return response()->json([
                'success' => false,
                'message' => 'Personel kaydınız bulunamadı.',
            ], 400);
        }

        $timesheet = Timesheet::where('employee_id', $user->employee->id)
            ->whereDate('work_date', Carbon::today())
            ->with(['employee', 'project'])
            ->first();

        // Employee'nin atandığı proje bilgisini al
        $employee = $user->employee;
        $currentProject = $employee->current_project_id ? $employee->currentProject : null;

        if (!$timesheet) {
            return response()->json([
                'success' => true,
                'message' => 'Bugün için puantaj kaydı yok',
                'data' => [
                    'has_clocked_in' => false,
                    'has_clocked_out' => false,
                    'timesheet' => null,
                    'current_project' => $currentProject ? [
                        'id' => $currentProject->id,
                        'name' => $currentProject->name,
                        'project_code' => $currentProject->project_code,
                        'allowed_check_in_methods' => $currentProject->allowed_check_in_methods ?? ['manual'],
                    ] : null,
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'has_clocked_in' => !is_null($timesheet->check_in_time),
                'has_clocked_out' => !is_null($timesheet->check_out_time),
                'timesheet' => new TimesheetResource($timesheet),
                'working_hours' => $this->calculateCurrentWorkingHours($timesheet),
                'current_project' => $currentProject ? [
                    'id' => $currentProject->id,
                    'name' => $currentProject->name,
                    'project_code' => $currentProject->project_code,
                    'allowed_check_in_methods' => $currentProject->allowed_check_in_methods ?? ['manual'],
                ] : null,
            ],
        ], 200);
    }

    /**
     * Haftalık puantaj özeti
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function weekSummary(Request $request)
    {
        $user = $request->user();

        if (!$user->employee) {
            return response()->json([
                'success' => false,
                'message' => 'Personel kaydınız bulunamadı.',
            ], 400);
        }

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $timesheets = Timesheet::where('employee_id', $user->employee->id)
            ->whereBetween('work_date', [$startOfWeek, $endOfWeek])
            ->with(['project'])
            ->orderBy('work_date', 'asc')
            ->get();

        $summary = [
            'total_days' => $timesheets->count(),
            'total_hours' => $timesheets->sum('total_hours'),
            'total_regular_hours' => $timesheets->sum('regular_hours'),
            'total_overtime_hours' => $timesheets->sum('overtime_hours'),
            'timesheets' => TimesheetResource::collection($timesheets),
        ];

        return response()->json([
            'success' => true,
            'data' => $summary,
        ], 200);
    }

    /**
     * Aylık puantaj özeti
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function monthSummary(Request $request)
    {
        $user = $request->user();

        if (!$user->employee) {
            return response()->json([
                'success' => false,
                'message' => 'Personel kaydınız bulunamadı.',
            ], 400);
        }

        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $timesheets = Timesheet::where('employee_id', $user->employee->id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->with(['project'])
            ->orderBy('work_date', 'asc')
            ->get();

        $summary = [
            'year' => $year,
            'month' => $month,
            'month_name' => $startOfMonth->format('F'),
            'total_days' => $timesheets->count(),
            'total_hours' => $timesheets->sum('total_hours'),
            'total_regular_hours' => $timesheets->sum('regular_hours'),
            'total_overtime_hours' => $timesheets->sum('overtime_hours'),
            'timesheets' => TimesheetResource::collection($timesheets),
        ];

        return response()->json([
            'success' => true,
            'data' => $summary,
        ], 200);
    }

    /**
     * Belirli bir puantaj kaydını göster
     *
     * @param Timesheet $timesheet
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Timesheet $timesheet)
    {
        $user = $request->user();

        // Yetki kontrolü
        if ($user->user_type === 'employee' && $user->employee) {
            if ($timesheet->employee_id !== $user->employee->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu kayda erişim yetkiniz yok.',
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => new TimesheetResource($timesheet->load(['employee', 'project'])),
        ], 200);
    }

    /**
     * Offline senkronizasyon
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncOfflineData(Request $request)
    {
        $request->validate([
            'timesheets' => 'required|array',
            'timesheets.*.project_id' => 'required|exists:projects,id',
            'timesheets.*.work_date' => 'required|date',
            'timesheets.*.check_in_time' => 'required|date_format:H:i:s',
            'timesheets.*.check_out_time' => 'nullable|date_format:H:i:s',
        ]);

        $user = $request->user();

        if (!$user->employee) {
            return response()->json([
                'success' => false,
                'message' => 'Personel kaydınız bulunamadı.',
            ], 400);
        }

        $results = [
            'success' => [],
            'failed' => [],
        ];

        DB::beginTransaction();

        try {
            foreach ($request->timesheets as $timesheetData) {
                // Aynı tarih için kayıt var mı kontrol et
                $existing = Timesheet::where('employee_id', $user->employee->id)
                    ->whereDate('work_date', $timesheetData['work_date'])
                    ->first();

                if ($existing) {
                    $results['failed'][] = [
                        'date' => $timesheetData['work_date'],
                        'reason' => 'Bu tarih için zaten kayıt mevcut',
                    ];
                    continue;
                }

                // Yeni kayıt oluştur
                $timesheet = Timesheet::create([
                    'employee_id' => $user->employee->id,
                    'project_id' => $timesheetData['project_id'],
                    'work_date' => $timesheetData['work_date'],
                    'check_in_time' => $timesheetData['check_in_time'],
                    'check_out_time' => $timesheetData['check_out_time'] ?? null,
                    'check_in_method' => 'mobile_offline',
                    'check_out_method' => $timesheetData['check_out_time'] ? 'mobile_offline' : null,
                    'entered_by' => $user->id,
                    'status' => $timesheetData['check_out_time'] ? 'completed' : 'active',
                    'approval_status' => 'pending',
                    'notes' => $timesheetData['notes'] ?? 'Offline sync',
                ]);

                $results['success'][] = [
                    'date' => $timesheetData['work_date'],
                    'timesheet_id' => $timesheet->id,
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Senkronizasyon tamamlandı',
                'data' => $results,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Senkronizasyon sırasında hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Anlık çalışma saatini hesapla
     */
    private function calculateCurrentWorkingHours(Timesheet $timesheet)
    {
        if (!$timesheet->check_in_time) {
            return 0;
        }

        // Eğer çıkış yapılmışsa, total_hours'u döndür
        if ($timesheet->check_out_time) {
            return $timesheet->total_hours ?? 0;
        }

        // Aktif kayıt için anlık çalışma saatini hesapla
        $checkIn = Carbon::parse($timesheet->work_date->format('Y-m-d') . ' ' . $timesheet->check_in_time);
        $now = Carbon::now();

        $totalMinutes = $checkIn->diffInMinutes($now);
        $breakDuration = $timesheet->break_duration ?? 60;
        $workingMinutes = max(0, $totalMinutes - $breakDuration);

        return round($workingMinutes / 60, 2);
    }

    /**
     * GPS konumunu doğrula
     *
     * @param int $projectId
     * @param array|null $userLocation
     * @return array
     */
    private function validateGpsLocation($projectId, $userLocation)
    {
        // Konum bilgisi gönderilmemişse
        if (!$userLocation || !isset($userLocation['latitude']) || !isset($userLocation['longitude'])) {
            return [
                'valid' => false,
                'message' => 'GPS konum bilgisi gereklidir. Lütfen konumunuzu etkinleştirin.',
            ];
        }

        // Projeyi getir
        $project = \App\Models\Project::find($projectId);

        if (!$project) {
            return [
                'valid' => false,
                'message' => 'Proje bulunamadı.',
            ];
        }

        // Proje GPS bilgisi yoksa GPS kontrolü yapma (manual gibi davran)
        if (!$project->latitude || !$project->longitude) {
            return [
                'valid' => true,
                'message' => 'Bu proje için GPS kontrolü yapılmamaktadır.',
            ];
        }

        // Mesafe hesapla (metre cinsinden)
        $distance = $this->calculateDistance(
            $userLocation['latitude'],
            $userLocation['longitude'],
            $project->latitude,
            $project->longitude
        );

        // İzin verilen yarıçap (varsayılan 300 metre)
        $allowedRadius = $project->allowed_radius ?? 300;

        // Mesafe kontrolü
        if ($distance > $allowedRadius) {
            return [
                'valid' => false,
                'message' => sprintf(
                    'Proje alanının dışındasınız. Mesafe: %.0f metre (İzin verilen: %d metre)',
                    $distance,
                    $allowedRadius
                ),
                'data' => [
                    'distance' => round($distance, 2),
                    'allowed_radius' => $allowedRadius,
                    'project_location' => [
                        'latitude' => $project->latitude,
                        'longitude' => $project->longitude,
                    ],
                ],
            ];
        }

        return [
            'valid' => true,
            'message' => sprintf('Konum doğrulandı. Mesafe: %.0f metre', $distance),
            'data' => [
                'distance' => round($distance, 2),
                'allowed_radius' => $allowedRadius,
            ],
        ];
    }

    /**
     * İki GPS koordinatı arasındaki mesafeyi hesapla (Haversine formülü)
     *
     * @param float $lat1 Birinci nokta enlem
     * @param float $lon1 Birinci nokta boylam
     * @param float $lat2 İkinci nokta enlem
     * @param float $lon2 İkinci nokta boylam
     * @return float Metre cinsinden mesafe
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        // Dünya yarıçapı (metre)
        $earthRadius = 6371000;

        // Dereceleri radyana çevir
        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);

        // Farkları hesapla
        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLon = $lon2Rad - $lon1Rad;

        // Haversine formülü
        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return $distance;
    }
}
