<?php

namespace App\Services;

use App\Models\TimesheetV2;
use App\Models\TimesheetCarryover;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Puantaj Hesaplama Servisi
 * .NET PuantajCalculatorHaftalik mantığına göre uyarlanmıştır
 */
class TimesheetCalculatorService
{
    /**
     * Haftalık hesaplama
     */
    public function calculateWeekly(
        int $employeeId,
        Carbon $weekStart,
        ?TimesheetCarryover $carryover = null
    ): array {
        $weekEnd = $weekStart->copy()->endOfWeek();
        
        $timesheets = TimesheetV2::forEmployee($employeeId)
            ->inDateRange($weekStart, $weekEnd)
            ->with('shift')
            ->orderBy('work_date')
            ->get();
        
        if ($timesheets->isEmpty()) {
            return $this->emptyResult();
        }
        
        return $this->performWeeklyCalculation($timesheets, $carryover);
    }

    /**
     * Aylık hesaplama (haftalara bölünerek)
     */
    public function calculateMonthly(
        int $employeeId,
        int $year,
        int $month,
        ?TimesheetCarryover $carryover = null
    ): array {
        $timesheets = TimesheetV2::forEmployee($employeeId)
            ->forMonth($year, $month)
            ->with('shift')
            ->orderBy('work_date')
            ->get();
        
        if ($timesheets->isEmpty()) {
            return $this->emptyResult();
        }
        
        $weeklyResults = [];
        $weeks = $this->groupByWeeks($timesheets);
        
        foreach ($weeks as $weekTimesheets) {
            $weeklyResults[] = $this->performWeeklyCalculation($weekTimesheets, null);
        }
        
        return $this->aggregateWeeklyResults($weeklyResults, $carryover);
    }

    /**
     * Haftalık hesaplama mantığı (.NET'ten uyarlandı)
     * 45 saat kuralı: Haftalık 45 saati geçen çalışma fazla mesai olarak hesaplanır
     */
    protected function performWeeklyCalculation(
        Collection $timesheets,
        ?TimesheetCarryover $carryover
    ): array {
        $result = [
            'total_hours' => 0,              // Toplam çalışma
            'weekly_obligation' => 0,         // Haftalık yükümlülük
            'weekly_actual_work' => 0,        // Haftalık fiili çalışma
            'overtime_hours' => 0,            // Fazla mesai
            'shortage_hours' => 0,            // Eksik saat
            'weekend_hours' => 0,             // Hafta tatili mesaisi
            'holiday_hours' => 0,             // Bayram mesaisi
            'paid_leave_days' => 0,           // Ücretli izin günü
            'unpaid_leave_days' => 0,         // Ücretsiz izin günü
            'sick_leave_days' => 0,           // Rapor günü
            'legal_leave_entitlement' => 0,   // Kanuni izin hakkı
            'work_days' => 0,                 // Çalışma günü sayısı
        ];

        $weeklyFiiliCalisma = 0;          // Haftalık fiili çalışma (normal günler)
        $weeklyCalismaYukumlulugu = 0;    // Haftalık yükümlülük
        $tatilMesai = 0;                   // Tatil mesaisi

        foreach ($timesheets as $timesheet) {
            $shift = $timesheet->shift;
            $hours = $timesheet->hours_worked;
            $isWorkDay = $shift->counts_as_work_day;

            // Normal çalışma günü
            if ($shift->isNormalWorkDay()) {
                $weeklyFiiliCalisma += $hours;
                $weeklyCalismaYukumlulugu += $shift->daily_hours;
                $result['work_days']++;
                $result['total_hours'] += $hours;
            }
            // Hafta sonu çalışması
            elseif ($shift->shift_type === 'weekend' || $shift->shift_type === 'rest_day') {
                $result['weekend_hours'] += $hours;
                $tatilMesai += $hours;
            }
            // Bayram çalışması
            elseif ($shift->shift_type === 'holiday') {
                $result['holiday_hours'] += $hours;
                $tatilMesai += $hours;
            }
            // Yıllık izin
            elseif ($shift->shift_type === 'annual_leave') {
                $result['paid_leave_days']++;
                $weeklyCalismaYukumlulugu += $shift->daily_hours;
            }
            // Hastalık raporu (ücretli)
            elseif ($shift->shift_type === 'sick_leave') {
                $result['sick_leave_days']++;
                // Rapor günü yükümlülükten düşülmez ama eksik olarak da sayılmaz
            }
            // Ücretsiz izin
            elseif ($shift->shift_type === 'unpaid_leave') {
                $result['unpaid_leave_days']++;
                // Ücretsiz izin çalışma yükümlülüğünden düşer
                $weeklyCalismaYukumlulugu -= (7.5 - $hours); // Günlük standart saat
            }
            // Mazeret izni (ücretli)
            elseif ($shift->shift_type === 'excused_leave') {
                $result['paid_leave_days']++;
                $weeklyCalismaYukumlulugu += $shift->daily_hours;
            }
            // Doğum izni (ücretsiz)
            elseif ($shift->shift_type === 'maternity_leave') {
                $result['unpaid_leave_days']++;
                $weeklyCalismaYukumlulugu -= 7.5;
            }
            // Arefe (yarım gün - 4 saat)
            elseif ($shift->shift_type === 'half_day') {
                $weeklyFiiliCalisma += $hours;
                $weeklyCalismaYukumlulugu += 4;
                $result['total_hours'] += $hours;
            }
        }

        // Kanuni izin hakkı: Her 7 günde 1 gün
        if ($result['work_days'] > 0) {
            $result['legal_leave_entitlement'] = floor($result['work_days'] / 7);
        }

        $result['weekly_obligation'] = $weeklyCalismaYukumlulugu;
        $result['weekly_actual_work'] = $weeklyFiiliCalisma;

        // HAFTALIK 45 SAAT KURALI
        // Fiili çalışma - Yükümlülük = Fark
        $weeklyDiff = $weeklyFiiliCalisma - $weeklyCalismaYukumlulugu;

        if ($weeklyDiff > 0) {
            // Fazla çalışma var
            $result['overtime_hours'] = $weeklyDiff;
            $result['shortage_hours'] = 0;
        } else {
            // Eksik çalışma var
            $result['overtime_hours'] = 0;
            $result['shortage_hours'] = abs($weeklyDiff);
        }

        // Devir işlemleri
        if ($carryover) {
            if ($carryover->isOvertime()) {
                // Önceki dönemden gelen fazla mesai ekle
                $result['overtime_hours'] += $carryover->hours;
            } elseif ($carryover->isShortage()) {
                // Önceki dönemden gelen eksik saati fazla mesaiden düş
                $netOvertime = $result['overtime_hours'] - $carryover->hours;

                if ($netOvertime >= 0) {
                    $result['overtime_hours'] = $netOvertime;
                } else {
                    $result['overtime_hours'] = 0;
                    $result['shortage_hours'] += abs($netOvertime);
                }
            }
        }

        return $result;
    }

    /**
     * Timesheets'leri haftalara grupla
     */
    protected function groupByWeeks(Collection $timesheets): Collection
    {
        return $timesheets->groupBy(function ($timesheet) {
            return Carbon::parse($timesheet->work_date)->startOfWeek()->format('Y-m-d');
        });
    }

    /**
     * Haftalık sonuçları topla
     */
    protected function aggregateWeeklyResults(
        array $weeklyResults,
        ?TimesheetCarryover $carryover
    ): array {
        $aggregated = $this->emptyResult();

        foreach ($weeklyResults as $weekResult) {
            $aggregated['total_hours'] += $weekResult['total_hours'];
            $aggregated['weekly_obligation'] += $weekResult['weekly_obligation'];
            $aggregated['weekly_actual_work'] += $weekResult['weekly_actual_work'];
            $aggregated['overtime_hours'] += $weekResult['overtime_hours'];
            $aggregated['shortage_hours'] += $weekResult['shortage_hours'];
            $aggregated['weekend_hours'] += $weekResult['weekend_hours'];
            $aggregated['holiday_hours'] += $weekResult['holiday_hours'];
            $aggregated['paid_leave_days'] += $weekResult['paid_leave_days'];
            $aggregated['unpaid_leave_days'] += $weekResult['unpaid_leave_days'];
            $aggregated['sick_leave_days'] += $weekResult['sick_leave_days'];
            $aggregated['work_days'] += $weekResult['work_days'];
        }

        $aggregated['legal_leave_entitlement'] = floor($aggregated['work_days'] / 7);

        // Devir uygula
        if ($carryover) {
            if ($carryover->isOvertime()) {
                $aggregated['overtime_hours'] += $carryover->hours;
            } elseif ($carryover->isShortage()) {
                $netOvertime = $aggregated['overtime_hours'] - $carryover->hours;
                
                if ($netOvertime >= 0) {
                    $aggregated['overtime_hours'] = $netOvertime;
                } else {
                    $aggregated['overtime_hours'] = 0;
                    $aggregated['shortage_hours'] += abs($netOvertime);
                }
            }
        }

        return $aggregated;
    }

    /**
     * Boş sonuç
     */
    protected function emptyResult(): array
    {
        return [
            'total_hours' => 0,
            'weekly_obligation' => 0,
            'weekly_actual_work' => 0,
            'overtime_hours' => 0,
            'shortage_hours' => 0,
            'weekend_hours' => 0,
            'holiday_hours' => 0,
            'paid_leave_days' => 0,
            'unpaid_leave_days' => 0,
            'sick_leave_days' => 0,
            'legal_leave_entitlement' => 0,
            'work_days' => 0,
        ];
    }

    /**
     * Aylık devir kaydı oluştur
     */
    public function createMonthlyCarryover(
        int $employeeId,
        int $year,
        int $month,
        array $calculationResult
    ): ?TimesheetCarryover {
        $overtime = $calculationResult['overtime_hours'] ?? 0;
        $shortage = $calculationResult['shortage_hours'] ?? 0;

        if ($overtime > 0) {
            return TimesheetCarryover::create([
                'employee_id' => $employeeId,
                'from_year' => $year,
                'from_month' => $month,
                'carryover_type' => 'overtime',
                'hours' => $overtime,
                'is_applied' => false,
            ]);
        } elseif ($shortage > 0) {
            return TimesheetCarryover::create([
                'employee_id' => $employeeId,
                'from_year' => $year,
                'from_month' => $month,
                'carryover_type' => 'shortage',
                'hours' => $shortage,
                'is_applied' => false,
            ]);
        }

        return null;
    }
}