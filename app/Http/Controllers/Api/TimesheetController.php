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
            $query->whereDate('date', $request->date);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Sıralama
        $query->orderBy('date', 'desc')->orderBy('check_in_time', 'desc');

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
            ->whereDate('date', $today)
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

        try {
            DB::beginTransaction();

            $checkInTime = $request->check_in_time ?? Carbon::now()->format('H:i:s');

            $timesheet = Timesheet::create([
                'employee_id' => $employee->id,
                'project_id' => $request->project_id,
                'date' => $today,
                'check_in_time' => $checkInTime,
                'check_in_method' => $request->check_in_method ?? 'manual',
                'check_in_location' => $request->check_in_location,
                'entered_by' => $user->id,
                'status' => 'active',
                'approval_status' => 'pending',
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
                ->whereDate('date', Carbon::today())
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

        try {
            DB::beginTransaction();

            $checkOutTime = $request->check_out_time ?? Carbon::now()->format('H:i:s');

            // Toplam çalışma saati hesapla
            $checkIn = Carbon::parse($timesheet->date->format('Y-m-d') . ' ' . $timesheet->check_in_time);
            $checkOut = Carbon::parse($timesheet->date->format('Y-m-d') . ' ' . $checkOutTime);

            // Eğer çıkış saati giriş saatinden önceyse, ertesi güne geçmiş demektir
            if ($checkOut->lessThan($checkIn)) {
                $checkOut->addDay();
            }

            $totalMinutes = $checkOut->diffInMinutes($checkIn);
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
                'check_out_method' => $request->check_out_method ?? 'manual',
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
            ->whereDate('date', Carbon::today())
            ->with(['employee', 'project'])
            ->first();

        if (!$timesheet) {
            return response()->json([
                'success' => true,
                'message' => 'Bugün için puantaj kaydı yok',
                'data' => [
                    'has_clocked_in' => false,
                    'has_clocked_out' => false,
                    'timesheet' => null,
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
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->with(['project'])
            ->orderBy('date', 'asc')
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
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->with(['project'])
            ->orderBy('date', 'asc')
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
            'timesheets.*.date' => 'required|date',
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
                    ->whereDate('date', $timesheetData['date'])
                    ->first();

                if ($existing) {
                    $results['failed'][] = [
                        'date' => $timesheetData['date'],
                        'reason' => 'Bu tarih için zaten kayıt mevcut',
                    ];
                    continue;
                }

                // Yeni kayıt oluştur
                $timesheet = Timesheet::create([
                    'employee_id' => $user->employee->id,
                    'project_id' => $timesheetData['project_id'],
                    'date' => $timesheetData['date'],
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
                    'date' => $timesheetData['date'],
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

        $checkIn = Carbon::parse($timesheet->date->format('Y-m-d') . ' ' . $timesheet->check_in_time);
        $now = Carbon::now();

        $totalMinutes = $now->diffInMinutes($checkIn);
        $breakDuration = $timesheet->break_duration ?? 60;
        $workingMinutes = max(0, $totalMinutes - $breakDuration);

        return round($workingMinutes / 60, 2);
    }
}
