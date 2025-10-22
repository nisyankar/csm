<?php

namespace App\Services\Timesheet;

use App\Models\TimesheetV3;
use App\Models\Shift;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Haftalık Fazla Mesai Hesaplama Servisi
 *
 * .NET uygulamasındaki PuantajCalculatorHaftalik mantığını implement eder.
 * Kritik: 2 hafta tatili seçilirse, kalan 5 günde 45 saati aşan kısım FM olur.
 */
class WeeklyOvertimeCalculator
{
    const WEEKLY_STANDARD_HOURS = 45.0;
    const DAILY_STANDARD_HOURS = 9.0;

    /**
     * Belirli bir hafta için tüm çalışanların FM hesaplamasını yap
     */
    public function calculateForWeek(int $projectId, int $year, int $weekNumber): array
    {
        // O haftanın tüm puantaj kayıtlarını getir
        $timesheets = TimesheetV3::where('project_id', $projectId)
            ->where('year', $year)
            ->where('week_number', $weekNumber)
            ->with(['employee', 'shift'])
            ->orderBy('employee_id')
            ->orderBy('work_date')
            ->get();

        // Çalışanlara göre grupla
        $groupedByEmployee = $timesheets->groupBy('employee_id');

        $results = [];

        foreach ($groupedByEmployee as $employeeId => $employeeTimesheets) {
            $result = $this->calculateForEmployeeWeek($employeeTimesheets);
            $results[$employeeId] = $result;

            // Her kayda haftalık bilgiyi kaydet
            $this->saveWeeklyCalculation($employeeTimesheets, $result);
        }

        return $results;
    }

    /**
     * Tek bir çalışan için haftalık hesaplama
     */
    public function calculateForEmployeeWeek(Collection $timesheets): array
    {
        $weeklyActualHours = 0.0;      // Fiili çalışma (izin/tatil hariç)
        $weeklyRequiredHours = 0.0;    // Haftalık yükümlülük
        $weeklyRequiredHoursNet = 0.0; // Net yükümlülük (sadece çalışılan günler)
        $holidayOvertimeHours = 0.0;   // Tatil/bayram mesaisi
        $weekendOvertimeHours = 0.0;   // Hafta sonu mesaisi

        foreach ($timesheets as $timesheet) {
            $shift = $timesheet->shift;
            $isWorkDay = true;
            $dailyRequirement = self::DAILY_STANDARD_HOURS;

            if ($shift) {
                // Shift tipine göre iş günü mü kontrol et
                $isWorkDay = !in_array($shift->shift_type, [
                    'annual_leave',
                    'sick_leave',
                    'unpaid_leave',
                    'excused_leave',
                    'maternity_leave',
                    'weekend', // Hafta tatili
                    'holiday',  // Resmi tatil
                ]);

                // Hafta tatili sayacı
                if ($shift->shift_type === 'weekend') {
                    $weekendOvertimeHours += $timesheet->hours_worked;
                }

                // Resmi tatil sayacı
                if ($shift->shift_type === 'holiday') {
                    $holidayOvertimeHours += $timesheet->hours_worked;
                }

                // Günlük çalışma yükümlülüğü
                $dailyRequirement = $shift->daily_hours ?? self::DAILY_STANDARD_HOURS;

                // Maksimum 9 saat yükümlülük
                if ($dailyRequirement > self::DAILY_STANDARD_HOURS) {
                    $dailyRequirement = self::DAILY_STANDARD_HOURS;
                }

                // İzinli günler için özel hesaplama
                if ($shift->is_paid && in_array($shift->shift_type, ['annual_leave', 'sick_leave'])) {
                    $isWorkDay = false;

                    // Eğer izinde çalışma varsa (genelde olmamalı ama kontrol edelim)
                    if ($timesheet->hours_worked > 0) {
                        if ($shift->shift_type === 'weekend') {
                            $weekendOvertimeHours += $timesheet->hours_worked;
                        } else {
                            $holidayOvertimeHours += $timesheet->hours_worked;
                        }
                    }
                }
            }

            // Fiili çalışma toplamı (sadece iş günleri)
            if ($isWorkDay) {
                $weeklyActualHours += $timesheet->hours_worked;
            }

            // Haftalık yükümlülük hesabı
            if ($isWorkDay) {
                $weeklyRequiredHours += $dailyRequirement;

                // Net yükümlülük (sadece vardiya girilmiş günler)
                if ($shift) {
                    $weeklyRequiredHoursNet += $dailyRequirement;
                }
            }
        }

        // KRITIK: Haftalık FM hesaplaması
        // Fiili çalışma - Net yükümlülük = Fazla Mesai
        $weeklyOvertime = $weeklyActualHours - $weeklyRequiredHoursNet;

        // Eğer pozitif ise FM, negatif ise eksik çalışma
        if ($weeklyOvertime > 0) {
            $overtimeHours = $weeklyOvertime;
            $shortageHours = 0.0;
        } else {
            $overtimeHours = 0.0;
            $shortageHours = abs($weeklyOvertime);
        }

        // Toplam fazla mesai (haftalık + tatil/hafta sonu)
        $totalOvertimeHours = $overtimeHours + $holidayOvertimeHours + $weekendOvertimeHours;

        return [
            'weekly_actual_hours' => round($weeklyActualHours, 2),
            'weekly_required_hours' => round($weeklyRequiredHours, 2),
            'weekly_required_hours_net' => round($weeklyRequiredHoursNet, 2),
            'weekly_overtime_hours' => round($overtimeHours, 2),
            'holiday_overtime_hours' => round($holidayOvertimeHours, 2),
            'weekend_overtime_hours' => round($weekendOvertimeHours, 2),
            'total_overtime_hours' => round($totalOvertimeHours, 2),
            'shortage_hours' => round($shortageHours, 2),
        ];
    }

    /**
     * Hesaplanan haftalık bilgiyi kayıtlara kaydet
     */
    private function saveWeeklyCalculation(Collection $timesheets, array $result): void
    {
        foreach ($timesheets as $timesheet) {
            $timesheet->update([
                'weekly_total_hours' => $result['weekly_actual_hours'],
                'weekly_required_hours' => $result['weekly_required_hours_net'],
                'weekly_overtime_hours' => $result['total_overtime_hours'],
                'week_calculation_done' => true,
            ]);
        }
    }

    /**
     * Aylık hesaplama için tüm haftaları hesapla
     */
    public function calculateForMonth(int $projectId, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $results = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $weekNumber = $current->weekOfYear;
            $weekYear = $current->year;

            // O haftayı hesapla
            $weekResults = $this->calculateForWeek($projectId, $weekYear, $weekNumber);

            foreach ($weekResults as $employeeId => $result) {
                if (!isset($results[$employeeId])) {
                    $results[$employeeId] = [
                        'total_actual_hours' => 0,
                        'total_required_hours' => 0,
                        'total_overtime_hours' => 0,
                        'total_shortage_hours' => 0,
                        'weeks' => [],
                    ];
                }

                $results[$employeeId]['total_actual_hours'] += $result['weekly_actual_hours'];
                $results[$employeeId]['total_required_hours'] += $result['weekly_required_hours_net'];
                $results[$employeeId]['total_overtime_hours'] += $result['total_overtime_hours'];
                $results[$employeeId]['total_shortage_hours'] += $result['shortage_hours'];
                $results[$employeeId]['weeks'][$weekNumber] = $result;
            }

            $current->addWeek()->startOfWeek();
        }

        return $results;
    }

    /**
     * Tek bir puantaj kaydı eklendiğinde haftasını yeniden hesapla
     */
    public function recalculateWeekForTimesheet(TimesheetV3 $timesheet): array
    {
        return $this->calculateForWeek(
            $timesheet->project_id,
            $timesheet->year,
            $timesheet->week_number
        );
    }

    /**
     * Örnek senaryo hesaplama (test için)
     *
     * Örnek: Cumartesi-Pazar tatil, Pazartesi-Cuma 10'ar saat çalışma
     * Sonuç: 50 - 45 = 5 saat FM
     */
    public function calculateExampleScenario(): array
    {
        // Örnek veri: 5 gün x 10 saat = 50 saat
        // 2 gün tatil (cumartesi-pazar)
        // Beklenen: 50 - 45 = 5 saat FM

        $weeklyActual = 50.0;      // Fiili çalışma
        $weeklyRequired = 45.0;    // Standart haftalık (5 gün x 9 saat)
        $weeklyRequiredNet = 45.0; // Net yükümlülük (vardiya girilmiş günler)

        $overtime = $weeklyActual - $weeklyRequiredNet;

        return [
            'scenario' => 'Cumartesi-Pazar tatil, Pazartesi-Cuma 10 saat çalışma',
            'weekly_actual' => $weeklyActual,
            'weekly_required' => $weeklyRequired,
            'weekly_required_net' => $weeklyRequiredNet,
            'overtime' => $overtime > 0 ? $overtime : 0,
            'shortage' => $overtime < 0 ? abs($overtime) : 0,
            'explanation' => '2 hafta tatili seçildiğinde, kalan 5 günde 45 saati aşan ' . ($overtime > 0 ? $overtime : 0) . ' saat FM olarak hesaplanır.',
        ];
    }
}