<?php

namespace Database\Factories;

use App\Models\TimesheetRevision;
use App\Models\Timesheet;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimesheetRevisionFactory extends Factory
{
    protected $model = TimesheetRevision::class;

    public function definition(): array
    {
        return [
            'timesheet_id' => Timesheet::factory(),
            'revised_by' => Employee::factory(),
            'revision_number' => $this->faker->numberBetween(1, 5),
            'field_name' => $this->faker->randomElement([
                'regular_hours', 'overtime_hours', 'description', 
                'project_id', 'hourly_rate'
            ]),
            'old_value' => $this->faker->word(),
            'new_value' => $this->faker->word(),
            'reason' => $this->faker->sentence(),
            'is_bulk_revision' => $this->faker->boolean(20),
            'bulk_criteria' => null,
            'affected_timesheets_count' => 1,
        ];
    }
}