<?php

namespace Database\Factories;

use App\Models\TimesheetApproval;
use App\Models\Timesheet;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimesheetApprovalFactory extends Factory
{
    protected $model = TimesheetApproval::class;

    public function definition(): array
    {
        $submittedAt = $this->faker->dateTimeBetween('-2 months', 'now');
        $reviewedAt = $this->faker->optional(0.8)->dateTimeBetween($submittedAt, 'now');
        
        return [
            'timesheet_id' => Timesheet::factory(),
            'approver_id' => Employee::factory(),
            'level' => $this->faker->numberBetween(1, 3),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected', 'delegated']),
            'comments' => $this->faker->optional(0.4)->sentence(),
            'submitted_at' => $submittedAt,
            'reviewed_at' => $reviewedAt,
            'delegated_to' => null,
            'delegation_reason' => null,
            'auto_approved' => $this->faker->boolean(15),
            'approval_deadline' => $this->faker->dateTimeBetween($submittedAt, '+1 week'),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'reviewed_at' => $this->faker->dateTimeBetween($attributes['submitted_at'], 'now'),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'reviewed_at' => null,
        ]);
    }
}