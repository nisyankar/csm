<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        $category = $this->faker->randomElement(['worker', 'foreman', 'engineer', 'manager']);
        $wageType = $this->faker->randomElement(['daily', 'hourly', 'monthly']);
        
        // Ücret hesaplama (Türkiye şartlarına uygun)
        $dailyWage = null;
        $hourlyWage = null;
        
        if ($wageType === 'daily') {
            $dailyWage = $this->getDailyWageByCategory($category);
        } else if ($wageType === 'hourly') {
            $hourlyWage = $this->getHourlyWageByCategory($category);
        }

        return [
            'employee_code' => $this->generateEmployeeCode(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'tc_number' => $this->generateTcNumber(),
            'birth_date' => $this->faker->dateTimeBetween('-60 years', '-18 years'),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->optional(0.7)->unique()->safeEmail(),
            'address' => $this->faker->address(),
            
            // İş Bilgileri
            'position' => $this->getPositionByCategory($category),
            'category' => $category,
            'start_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'end_date' => $this->faker->optional(0.1)->dateTimeBetween('now', '+1 year'), // %10 sonlandırılmış
            'daily_wage' => $dailyWage,
            'hourly_wage' => $hourlyWage,
            'wage_type' => $wageType,
            
            // Hiyerarşi (Seeder'da ayarlanacak)
            'manager_id' => null,
            'user_id' => $this->faker->optional(0.3)->randomElement([1, 2, 3, 4, 5]), // %30 login var
            'current_project_id' => null, // Seeder'da atanacak
            
            // Durum
            'status' => $this->faker->randomElement(['active', 'inactive', 'terminated']),
            'qr_code' => $this->generateQrCode(),
            'photo_path' => $this->faker->optional(0.4)->imageUrl(200, 200, 'people'),
            
            // İzin Hakları
            'annual_leave_days' => $this->getAnnualLeaveDays($category),
            'used_leave_days' => $this->faker->numberBetween(0, 10),
        ];
    }

    /**
     * Benzersiz personel kodu oluştur
     */
    private function generateEmployeeCode(): string
    {
        do {
            $code = 'EMP' . str_pad($this->faker->numberBetween(1000, 9999), 4, '0', STR_PAD_LEFT);
        } while (Employee::where('employee_code', $code)->exists());

        return $code;
    }

    /**
     * Geçerli TC kimlik numarası oluştur
     */
    private function generateTcNumber(): string
    {
        do {
            $tc = $this->faker->numerify('###########');
        } while (Employee::where('tc_number', $tc)->exists() || !$this->isValidTcNumber($tc));

        return $tc;
    }

    /**
     * TC kimlik numarası doğrulama
     */
    private function isValidTcNumber(string $tc): bool
    {
        if (strlen($tc) !== 11 || !ctype_digit($tc)) {
            return false;
        }

        $digits = str_split($tc);
        
        // İlk hane 0 olamaz
        if ($digits[0] == 0) {
            return false;
        }

        // TC kimlik algoritması
        $sum1 = $sum2 = 0;
        for ($i = 0; $i < 9; $i++) {
            if ($i % 2 == 0) {
                $sum1 += (int)$digits[$i];
            } else {
                $sum2 += (int)$digits[$i];
            }
        }

        $check1 = ((($sum1 * 7) - $sum2) % 10);
        $check2 = (($sum1 + $sum2 + (int)$digits[9]) % 10);

        return ($check1 == (int)$digits[9] && $check2 == (int)$digits[10]);
    }

    /**
     * QR kod oluştur
     */
    private function generateQrCode(): string
    {
        do {
            $qr = 'QR' . str_pad($this->faker->numberBetween(100000, 999999), 6, '0', STR_PAD_LEFT);
        } while (Employee::where('qr_code', $qr)->exists());

        return $qr;
    }

    /**
     * Kategoriye göre günlük ücret hesapla
     */
    private function getDailyWageByCategory(string $category): float
    {
        $wages = [
            'worker' => [350, 450],      // İşçi: 350-450 TL
            'foreman' => [500, 650],     // Forman: 500-650 TL
            'engineer' => [600, 900],    // Mühendis: 600-900 TL
            'manager' => [800, 1200],    // Yönetici: 800-1200 TL
        ];

        $range = $wages[$category] ?? [350, 450];
        return $this->faker->randomFloat(2, $range[0], $range[1]);
    }

    /**
     * Kategoriye göre saatlik ücret hesapla
     */
    private function getHourlyWageByCategory(string $category): float
    {
        $wages = [
            'worker' => [40, 55],        // İşçi: 40-55 TL/saat
            'foreman' => [60, 80],       // Forman: 60-80 TL/saat
            'engineer' => [75, 110],     // Mühendis: 75-110 TL/saat
            'manager' => [100, 150],     // Yönetici: 100-150 TL/saat
        ];

        $range = $wages[$category] ?? [40, 55];
        return $this->faker->randomFloat(2, $range[0], $range[1]);
    }

    /**
     * Kategoriye göre pozisyon belirle
     */
    private function getPositionByCategory(string $category): string
    {
        $positions = [
            'worker' => [
                'İnşaat İşçisi', 'Kaynakçı', 'Sıvacı', 'Boyacı', 'Elektrikçi',
                'Tesisatçı', 'Demiryolu İşçisi', 'Çelik Konstrüksiyon İşçisi'
            ],
            'foreman' => [
                'İnşaat Formanı', 'Elektrik Formanı', 'Tesisat Formanı',
                'Betonarme Formanı', 'Finishing Formanı'
            ],
            'engineer' => [
                'İnşaat Mühendisi', 'Elektrik Mühendisi', 'Makine Mühendisi',
                'Çevre Mühendisi', 'Harita Mühendisi', 'Mimarlık'
            ],
            'manager' => [
                'Şantiye Şefi', 'Proje Yöneticisi', 'Bölge Müdürü',
                'Teknik Müdür', 'İnsan Kaynakları Müdürü'
            ],
        ];

        return $this->faker->randomElement($positions[$category] ?? $positions['worker']);
    }

    /**
     * Kategoriye göre yıllık izin günü
     */
    private function getAnnualLeaveDays(string $category): int
    {
        $leaveDays = [
            'worker' => 14,
            'foreman' => 20,
            'engineer' => 26,
            'manager' => 30,
        ];

        return $leaveDays[$category] ?? 14;
    }

    /**
     * Aktif çalışan durumu
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'end_date' => null,
        ]);
    }

    /**
     * İşçi kategorisi
     */
    public function worker(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'worker',
            'position' => $this->getPositionByCategory('worker'),
            'daily_wage' => $this->getDailyWageByCategory('worker'),
            'annual_leave_days' => 14,
        ]);
    }

    /**
     * Forman kategorisi
     */
    public function foreman(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'foreman',
            'position' => $this->getPositionByCategory('foreman'),
            'daily_wage' => $this->getDailyWageByCategory('foreman'),
            'annual_leave_days' => 20,
        ]);
    }

    /**
     * Mühendis kategorisi
     */
    public function engineer(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'engineer',
            'position' => $this->getPositionByCategory('engineer'),
            'wage_type' => 'monthly',
            'daily_wage' => null,
            'hourly_wage' => null,
            'annual_leave_days' => 26,
        ]);
    }

    /**
     * Yönetici kategorisi
     */
    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'manager',
            'position' => $this->getPositionByCategory('manager'),
            'wage_type' => 'monthly',
            'daily_wage' => null,
            'hourly_wage' => null,
            'annual_leave_days' => 30,
        ]);
    }

    /**
     * Sonlandırılmış çalışan
     */
    public function terminated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'terminated',
            'end_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * Login hesabı olan çalışan
     */
    public function withUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory(),
        ]);
    }
}