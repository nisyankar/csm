<?php

namespace App\Services\Leave;

use App\Models\Employee;
use App\Models\LeaveBalance;
use Carbon\Carbon;

/**
 * Türk İş Kanunu'na uygun yasal izin hakları hesaplama servisi
 *
 * Madde 53 - Yıllık ücretli izin süresi:
 * - 1-5 yıl arası: 14 gün
 * - 5-15 yıl arası (5 dahil): 20 gün
 * - 15 yıl ve üzeri (15 dahil): 26 gün
 * - 18 yaş altı ve 50 yaş üstü: +6 gün (toplam 20 gün minimum)
 */
class LeaveEntitlementService
{
    /**
     * Çalışanın yasal yıllık izin hakkını hesapla
     */
    public function calculateAnnualLeaveEntitlement(Employee $employee, int $year = null): float
    {
        $year = $year ?? now()->year;

        // Kıdem yılını hesapla
        $seniorityYears = $this->calculateSeniorityYears($employee, $year);

        // Yaş kontrolü
        $age = $this->calculateAge($employee, $year);

        // Temel izin hakkı (Türk İş Kanunu Madde 53)
        $baseDays = $this->getBaseLeaveDays($seniorityYears);

        // Yaş bonusu (18 yaş altı veya 50 yaş üstü için +6 gün)
        $ageBonus = 0;
        if ($age < 18 || $age >= 50) {
            $ageBonus = 6;
        }

        return $baseDays + $ageBonus;
    }

    /**
     * Kıdem yılını hesapla
     */
    public function calculateSeniorityYears(Employee $employee, int $forYear = null): int
    {
        $forYear = $forYear ?? now()->year;
        $endOfYear = Carbon::create($forYear, 12, 31);

        if (!$employee->start_date) {
            return 0;
        }

        $startDate = Carbon::parse($employee->start_date);

        // Yılın sonuna kadar kaç yıl çalışmış olacak
        return $startDate->diffInYears($endOfYear);
    }

    /**
     * Yaşı hesapla
     */
    private function calculateAge(Employee $employee, int $forYear): int
    {
        if (!$employee->birth_date) {
            return 25; // Default yaş
        }

        $birthDate = Carbon::parse($employee->birth_date);
        $endOfYear = Carbon::create($forYear, 12, 31);

        return $birthDate->diffInYears($endOfYear);
    }

    /**
     * Kıdeme göre temel izin günü (Türk İş Kanunu Madde 53)
     */
    private function getBaseLeaveDays(int $seniorityYears): int
    {
        if ($seniorityYears < 1) {
            return 0; // İlk yıl izin hakkı yok (bazı firmalar veriyor ama yasal değil)
        } elseif ($seniorityYears < 5) {
            return 14;
        } elseif ($seniorityYears < 15) {
            return 20;
        } else {
            return 26;
        }
    }

    /**
     * Yıl içinde işe başlayan için orantılı izin hesapla
     */
    public function calculateProratedLeave(Employee $employee, int $year): array
    {
        $startDate = Carbon::parse($employee->start_date);
        $yearStart = Carbon::create($year, 1, 1);
        $yearEnd = Carbon::create($year, 12, 31);

        // Eğer yıl başlamadan önce başlamışsa orantılı değil
        if ($startDate->lt($yearStart)) {
            return [
                'is_prorated' => false,
                'days' => $this->calculateAnnualLeaveEntitlement($employee, $year),
                'start_date' => $yearStart,
                'end_date' => $yearEnd,
            ];
        }

        // Yıl içinde başlamış - orantılı hesapla
        $workedDays = $startDate->diffInDays($yearEnd) + 1;
        $totalDaysInYear = $yearStart->diffInDays($yearEnd) + 1;
        $fullEntitlement = $this->calculateAnnualLeaveEntitlement($employee, $year);

        $proratedDays = ($workedDays / $totalDaysInYear) * $fullEntitlement;

        return [
            'is_prorated' => true,
            'days' => round($proratedDays, 2),
            'full_entitlement' => $fullEntitlement,
            'worked_days' => $workedDays,
            'total_days_in_year' => $totalDaysInYear,
            'start_date' => $startDate,
            'end_date' => $yearEnd,
        ];
    }

    /**
     * Çalışan için belirli yıla ait izin bakiyesi oluştur/güncelle
     */
    public function ensureLeaveBalance(Employee $employee, string $leaveType = 'annual', int $year = null): LeaveBalance
    {
        $year = $year ?? now()->year;

        // Mevcut bakiye varsa getir
        $balance = LeaveBalance::forEmployee($employee->id)
            ->forYear($year)
            ->byLeaveType($leaveType)
            ->first();

        if ($balance) {
            return $balance;
        }

        // Yeni bakiye oluştur
        $entitlement = $this->calculateAnnualLeaveEntitlement($employee, $year);
        $prorated = $this->calculateProratedLeave($employee, $year);
        $seniorityYears = $this->calculateSeniorityYears($employee, $year);

        // Önceki yıldan devir varsa kontrol et
        $carriedForward = $this->calculateCarryForward($employee, $year - 1);

        $balance = LeaveBalance::create([
            'employee_id' => $employee->id,
            'year' => $year,
            'leave_type' => $leaveType,
            'entitled_days' => $prorated['is_prorated'] ? $prorated['days'] : $entitlement,
            'carried_forward_days' => $carriedForward,
            'earned_days' => 0,
            'adjustment_days' => 0,
            'total_days' => ($prorated['is_prorated'] ? $prorated['days'] : $entitlement) + $carriedForward,
            'used_days' => 0,
            'planned_days' => 0,
            'remaining_days' => ($prorated['is_prorated'] ? $prorated['days'] : $entitlement) + $carriedForward,
            'seniority_years' => $seniorityYears,
            'legal_entitlement' => $entitlement,
            'is_prorated' => $prorated['is_prorated'],
            'calculation_date' => now(),
            'entitlement_start_date' => $prorated['start_date'],
            'entitlement_end_date' => $prorated['end_date'],
            'max_carry_forward' => 10, // Yasal olarak kullanılmayan izinler devredebilir
            'expiry_date' => Carbon::create($year + 1, 12, 31), // Bir sonraki yıl sonu
            'status' => 'active',
            'calculation_details' => [
                'calculation_method' => 'turkish_labor_law',
                'base_days' => $this->getBaseLeaveDays($seniorityYears),
                'age_bonus' => $prorated['is_prorated'] ? $prorated['days'] - $entitlement : 0,
                'prorated_calculation' => $prorated,
                'seniority_years' => $seniorityYears,
                'calculated_at' => now()->toDateTimeString(),
            ],
        ]);

        return $balance;
    }

    /**
     * Önceki yıldan devir edilecek izin miktarını hesapla
     */
    private function calculateCarryForward(Employee $employee, int $fromYear): float
    {
        $previousBalance = LeaveBalance::forEmployee($employee->id)
            ->forYear($fromYear)
            ->byLeaveType('annual')
            ->first();

        if (!$previousBalance || $previousBalance->remaining_days <= 0) {
            return 0;
        }

        // Maksimum devir miktarını kontrol et
        $maxCarryForward = $previousBalance->max_carry_forward ?? 10;

        return min($previousBalance->remaining_days, $maxCarryForward);
    }

    /**
     * Tüm çalışanlar için belirli yıla ait izin bakiyelerini toplu oluştur
     */
    public function calculateForAllEmployees(int $year = null): array
    {
        $year = $year ?? now()->year;
        $results = [
            'success' => [],
            'skipped' => [],
            'errors' => [],
        ];

        $employees = Employee::active()->get();

        foreach ($employees as $employee) {
            try {
                // İşe başlama tarihi yoksa veya gelecekte başlayacaksa atla
                if (!$employee->start_date || Carbon::parse($employee->start_date)->year > $year) {
                    $results['skipped'][] = [
                        'employee_id' => $employee->id,
                        'reason' => 'No start date or future hire',
                    ];
                    continue;
                }

                $balance = $this->ensureLeaveBalance($employee, 'annual', $year);

                $results['success'][] = [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->full_name,
                    'balance_id' => $balance->id,
                    'entitled_days' => $balance->entitled_days,
                    'remaining_days' => $balance->remaining_days,
                ];
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'employee_id' => $employee->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Hastalık izni hakkını hesapla
     * Not: Hastalık izni Türk İş Kanunu'nda sınırsızdır ancak rapor gerektirir
     */
    public function calculateSickLeaveEntitlement(Employee $employee, int $year = null): array
    {
        return [
            'entitled_days' => -1, // Sınırsız (-1 ile gösterilir)
            'requires_documentation' => true,
            'max_days_without_doc' => 3, // 3 güne kadar raporsu belge olarak kabul edilebilir
            'notes' => 'Hastalık izni sınırsızdır ancak sağlık raporu gerektirir.',
        ];
    }

    /**
     * Evlilik izni hesapla (3 gün)
     */
    public function calculateMarriageLeaveEntitlement(Employee $employee): array
    {
        return [
            'entitled_days' => 3,
            'is_paid' => true,
            'requires_documentation' => true,
            'notes' => 'Evlenme durumunda 3 gün ücretli izin hakkı vardır.',
        ];
    }

    /**
     * Cenaze izni hesapla
     */
    public function calculateFuneralLeaveEntitlement(Employee $employee, string $relation): array
    {
        // Anne, baba, eş, çocuk, kardeş vefatında 3 gün
        $firstDegreeRelations = ['anne', 'baba', 'eş', 'çocuk', 'kardeş'];

        $days = in_array(strtolower($relation), $firstDegreeRelations) ? 3 : 1;

        return [
            'entitled_days' => $days,
            'is_paid' => true,
            'relation' => $relation,
            'notes' => $days === 3 ? 'Birinci derece yakın için 3 gün' : 'Diğer yakınlar için 1 gün',
        ];
    }

    /**
     * Doğum izni hesapla (kadın çalışan için)
     */
    public function calculateMaternityLeaveEntitlement(Employee $employee): array
    {
        return [
            'pre_birth_days' => 56, // 8 hafta (doğum öncesi)
            'post_birth_days' => 56, // 8 hafta (doğum sonrası)
            'total_days' => 112, // 16 hafta toplam
            'is_paid' => true, // SGK öder
            'notes' => 'Çoğul gebelikte toplam 18 hafta (126 gün)',
            'multiple_pregnancy_days' => 126,
        ];
    }

    /**
     * Babalık izni hesapla
     */
    public function calculatePaternityLeaveEntitlement(Employee $employee): array
    {
        return [
            'entitled_days' => 5,
            'is_paid' => true,
            'notes' => 'Eşinin doğum yapması durumunda 5 gün ücretli izin',
        ];
    }
}
