<?php

namespace Database\Factories;

use App\Models\Timesheet;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class TimesheetFactory extends Factory
{
    protected $model = Timesheet::class;

    public function definition(): array
    {
        $workDate = $this->faker->dateTimeBetween('-3 months', 'now');
        $isWeekend = Carbon::parse($workDate)->isWeekend();
        
        // Çalışma saatleri (inşaat sektörü standartları)
        $startTime = $this->faker->time('H:i:s', '08:30:00'); // Sabah 6:30-8:30 arası
        $workMinutes = $isWeekend ? 
            $this->faker->numberBetween(240, 480) : // Hafta sonu: 4-8 saat
            $this->faker->numberBetween(480, 600);  // Hafta içi: 8-10 saat
            
        $endTime = Carbon::parse($startTime)->addMinutes($workMinutes)->format('H:i:s');
        
        // Mola süreleri
        $breakMinutes = $this->faker->numberBetween(30, 90); // 30-90 dakika mola
        $regularMinutes = min(480, $workMinutes); // Günlük max 8 saat normal mesai
        $overtimeMinutes = max(0, $workMinutes - 480); // 8 saatten fazlası fazla mesai
        
        return [
            'employee_id' => Employee::factory(),
            'project_id' => Project::factory(),
            'department_id' => Department::factory(),
            'work_date' => $workDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'break_start' => $this->faker->optional(0.8)->time('H:i:s', '12:30:00'),
            'break_end' => function (array $attributes) {
                return $attributes['break_start'] ? 
                    Carbon::parse($attributes['break_start'])->addMinutes($this->faker->numberBetween(30, 90))->format('H:i:s') : null;
            },
            'total_minutes' => $workMinutes,
            'regular_minutes' => $regularMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'break_minutes' => $breakMinutes,
            'shift_type' => $isWeekend ? 'weekend' : 'day',
            'attendance_type' => $this->faker->randomElement(['present', 'late', 'early_leave']),
            'entry_method' => $this->faker->randomElement(['manual', 'qr_code']),
            'entered_by' => null, // Seeder'da set edilecek
            'entered_at' => $this->faker->dateTimeBetween($workDate, 'now'),
            'approval_status' => $this->faker->randomElement(['draft', 'pending', 'approved']),
            'submitted_at' => $this->faker->optional(0.7)->dateTimeBetween($workDate, 'now'),
            'entry_location' => $this->faker->optional(0.6)->address(),
            'exit_location' => $this->faker->optional(0.6)->address(),
            'notes' => $this->faker->optional(0.3)->sentence(),
            'daily_rate' => $this->faker->randomFloat(2, 350, 800),
            'hourly_rate' => $this->faker->randomFloat(2, 40, 100),
            'calculated_wage' => function (array $attributes) {
                return ($attributes['regular_minutes'] / 60) * $attributes['hourly_rate'] + 
                       ($attributes['overtime_minutes'] / 60) * $attributes['hourly_rate'] * 1.5;
            },
            'is_revised' => $this->faker->boolean(15),
            'revision_count' => 0,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'approved',
            'first_approved_at' => $this->faker->dateTimeBetween($attributes['work_date'], 'now'),
        ]);
    }

    public function weekend(): static
    {
        return $this->state(function (array $attributes) {
            $weekendDate = $this->faker->dateTimeBetween('-2 months', 'now');
            $carbon = Carbon::parse($weekendDate);
            if (!$carbon->isWeekend()) {
                $weekendDate = $carbon->next(Carbon::SATURDAY);
            }

            return [
                'work_date' => $weekendDate,
                'shift_type' => 'weekend',
                'overtime_minutes' => $this->faker->numberBetween(240, 480),
                'regular_minutes' => 0,
            ];
        });
    }
}