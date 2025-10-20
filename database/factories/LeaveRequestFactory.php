<?php

namespace Database\Factories;

use App\Models\LeaveRequest;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-2 months', '+6 months');
        $endDate = $this->faker->dateTimeBetween($startDate, Carbon::parse($startDate)->addDays(20));
        
        $leaveTypes = [
            'annual' => ['Annual Leave', 1, 21], // multiplier, max_days
            'sick' => ['Sick Leave', 1, 10],
            'maternity' => ['Maternity Leave', 0, 120],
            'paternity' => ['Paternity Leave', 0, 7],
            'personal' => ['Personal Leave', 0.5, 5],
            'emergency' => ['Emergency Leave', 0, 3],
            'bereavement' => ['Bereavement Leave', 0, 5],
            'unpaid' => ['Unpaid Leave', 0, 30],
            'compensatory' => ['Compensatory Leave', 1, 10],
            'study' => ['Study Leave', 0.5, 15],
        ];

        $leaveType = $this->faker->randomKey($leaveTypes);
        $leaveInfo = $leaveTypes[$leaveType];
        
        $requestDate = $this->faker->dateTimeBetween('-1 month', $startDate);
        $totalDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        
        // Calculate working days (exclude weekends)
        $workingDays = 0;
        $current = Carbon::parse($startDate);
        while ($current <= Carbon::parse($endDate)) {
            if (!$current->isWeekend()) {
                $workingDays++;
            }
            $current->addDay();
        }

        return [
            'employee_id' => Employee::factory(),
            'leave_type' => $leaveType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $totalDays,
            'working_days' => $workingDays,
            'reason' => $this->generateLeaveReason($leaveType),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected', 'cancelled']),
            'requested_at' => $requestDate,
            'approved_by' => null, // Will be set based on status
            'approved_at' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
            'cancelled_at' => null,
            'cancellation_reason' => null,
            'substitute_employee_id' => $this->faker->optional(0.4)->randomElement([1, 2, 3, 4, 5]),
            'emergency_contact' => $this->faker->optional(0.3)->name(),
            'emergency_phone' => $this->faker->optional(0.3)->phoneNumber(),
            'is_half_day' => $totalDays <= 1 ? $this->faker->boolean(20) : false,
            'half_day_period' => function (array $attributes) {
                return $attributes['is_half_day'] ? 
                    $this->faker->randomElement(['morning', 'afternoon']) : null;
            },
            'attachments' => $this->faker->optional(0.3)->words(2, true), // File names
            'hr_notes' => $this->faker->optional(0.2)->sentence(),
            'manager_notes' => $this->faker->optional(0.3)->sentence(),
            'return_date' => Carbon::parse($endDate)->addDay(),
            'actual_return_date' => null, // Set after leave is taken
            'deducted_from_balance' => $workingDays * $leaveInfo[1], // Apply multiplier
            'balance_before' => $this->faker->numberBetween(5, 25),
            'balance_after' => function (array $attributes) {
                return max(0, $attributes['balance_before'] - $attributes['deducted_from_balance']);
            },
            'is_paid' => $leaveInfo[1] > 0, // Paid if multiplier > 0
            'salary_deduction' => function (array $attributes) {
                return !$attributes['is_paid'] ? 
                    $this->faker->randomFloat(2, 500, 3000) : 0;
            },
            'metadata' => json_encode([
                'submission_ip' => $this->faker->ipv4(),
                'submission_device' => $this->faker->randomElement(['web', 'mobile']),
                'workflow_stage' => $this->faker->randomElement(['hr_review', 'manager_approval', 'final_approval']),
                'priority_level' => $this->faker->randomElement(['normal', 'urgent', 'high']),
            ]),
        ];
    }

    /**
     * Generate realistic leave reason based on type
     */
    private function generateLeaveReason(string $leaveType): string
    {
        $reasons = [
            'annual' => [
                'Family vacation planned',
                'Personal rest and relaxation', 
                'Long weekend trip',
                'Attending family wedding',
                'Extended holiday break',
                'Personal time off'
            ],
            'sick' => [
                'Feeling unwell with flu symptoms',
                'Doctor appointment and recovery',
                'Medical treatment required',
                'Fever and unable to work',
                'Dental procedure and recovery',
                'Health checkup and rest'
            ],
            'maternity' => [
                'Maternity leave for childbirth',
                'Pre-natal care and delivery',
                'Newborn care and bonding time'
            ],
            'paternity' => [
                'Paternity leave for newborn',
                'Supporting spouse after childbirth',
                'Bonding time with new baby'
            ],
            'personal' => [
                'Personal family matters',
                'House relocation',
                'Important personal appointment',
                'Family emergency assistance',
                'Legal proceedings attendance'
            ],
            'emergency' => [
                'Family medical emergency',
                'Urgent personal matter',
                'Unexpected family situation',
                'Emergency travel required'
            ],
            'bereavement' => [
                'Death in family - funeral attendance',
                'Bereavement period for close relative',
                'Funeral arrangements and mourning'
            ],
            'unpaid' => [
                'Extended personal leave',
                'Career break period',
                'Personal projects and development',
                'Family care responsibilities'
            ],
            'compensatory' => [
                'Compensatory time off for overtime',
                'Time off in lieu of extra hours worked',
                'Recovery from extended work period'
            ],
            'study' => [
                'Exam preparation and attendance',
                'Educational course participation',
                'Professional certification exam',
                'Training and development program'
            ],
        ];

        return $this->faker->randomElement($reasons[$leaveType] ?? ['Personal leave required']);
    }

    /**
     * Pending approval state
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Approved state
     */
    public function approved(): static
    {
        return $this->state(function (array $attributes) {
            $approvedAt = $this->faker->dateTimeBetween($attributes['requested_at'], $attributes['start_date']);
            return [
                'status' => 'approved',
                'approved_by' => Employee::factory(),
                'approved_at' => $approvedAt,
                'manager_notes' => $this->faker->optional(0.5)->sentence(),
            ];
        });
    }

    /**
     * Rejected state
     */
    public function rejected(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'rejected',
                'rejected_by' => Employee::factory(),
                'rejected_at' => $this->faker->dateTimeBetween($attributes['requested_at'], 'now'),
                'rejection_reason' => $this->faker->randomElement([
                    'Insufficient leave balance',
                    'Critical project deadline conflicts',
                    'Staffing shortage during requested period',
                    'Previous leave requests already approved for same period',
                    'Insufficient advance notice provided',
                    'Peak business period - leave not possible',
                    'Required documentation not provided',
                    'Leave policy violation'
                ]),
                'deducted_from_balance' => 0,
                'balance_after' => $attributes['balance_before'],
            ];
        });
    }

    /**
     * Cancelled state
     */
    public function cancelled(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
                'cancelled_at' => $this->faker->dateTimeBetween($attributes['requested_at'], $attributes['start_date']),
                'cancellation_reason' => $this->faker->randomElement([
                    'Personal plans changed',
                    'Work priorities shifted',
                    'Family situation resolved',
                    'Medical condition improved',
                    'Project deadline moved up',
                    'Employee requested cancellation'
                ]),
                'deducted_from_balance' => 0,
                'balance_after' => $attributes['balance_before'],
            ];
        });
    }

    /**
     * Emergency leave state
     */
    public function emergency(): static
    {
        return $this->state(function (array $attributes) {
            $emergencyDate = $this->faker->dateTimeBetween('-1 week', '+1 week');
            return [
                'leave_type' => 'emergency',
                'start_date' => $emergencyDate,
                'end_date' => $this->faker->dateTimeBetween($emergencyDate, Carbon::parse($emergencyDate)->addDays(3)),
                'requested_at' => $this->faker->dateTimeBetween($emergencyDate, 'now'),
                'reason' => 'Urgent family emergency requiring immediate attention',
                'emergency_contact' => $this->faker->name(),
                'emergency_phone' => $this->faker->phoneNumber(),
                'metadata' => json_encode([
                    'submission_ip' => $this->faker->ipv4(),
                    'submission_device' => 'mobile',
                    'priority_level' => 'urgent',
                    'emergency_type' => $this->faker->randomElement(['medical', 'family', 'personal']),
                ]),
            ];
        });
    }

    /**
     * Annual leave state
     */
    public function annual(): static
    {
        return $this->state(function (array $attributes) {
            $futureDate = $this->faker->dateTimeBetween('+1 week', '+3 months');
            $endDate = $this->faker->dateTimeBetween($futureDate, Carbon::parse($futureDate)->addDays(14));
            
            return [
                'leave_type' => 'annual',
                'start_date' => $futureDate,
                'end_date' => $endDate,
                'reason' => 'Annual vacation leave',
                'is_paid' => true,
                'balance_before' => $this->faker->numberBetween(15, 30),
            ];
        });
    }

    /**
     * Sick leave state
     */
    public function sick(): static
    {
        return $this->state(function (array $attributes) {
            $sickDate = $this->faker->dateTimeBetween('-1 week', '+1 week');
            return [
                'leave_type' => 'sick',
                'start_date' => $sickDate,
                'end_date' => $this->faker->dateTimeBetween($sickDate, Carbon::parse($sickDate)->addDays(5)),
                'reason' => 'Medical leave due to illness',
                'attachments' => 'medical_certificate.pdf',
                'is_paid' => true,
            ];
        });
    }

    /**
     * Half day leave state
     */
    public function halfDay(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'total_days' => 0.5,
                'working_days' => 0.5,
                'is_half_day' => true,
                'half_day_period' => $this->faker->randomElement(['morning', 'afternoon']),
                'end_date' => $attributes['start_date'],
                'reason' => 'Personal appointment - half day leave',
                'deducted_from_balance' => 0.5,
            ];
        });
    }

    /**
     * Future leave state
     */
    public function future(): static
    {
        return $this->state(function (array $attributes) {
            $futureDate = $this->faker->dateTimeBetween('+1 month', '+6 months');
            return [
                'start_date' => $futureDate,
                'end_date' => $this->faker->dateTimeBetween($futureDate, Carbon::parse($futureDate)->addDays(10)),
                'status' => $this->faker->randomElement(['pending', 'approved']),
            ];
        });
    }

    /**
     * Past leave state
     */
    public function past(): static
    {
        return $this->state(function (array $attributes) {
            $pastDate = $this->faker->dateTimeBetween('-6 months', '-1 week');
            $endDate = $this->faker->dateTimeBetween($pastDate, Carbon::parse($pastDate)->addDays(7));
            
            return [
                'start_date' => $pastDate,
                'end_date' => $endDate,
                'status' => $this->faker->randomElement(['approved', 'cancelled']),
                'actual_return_date' => $this->faker->optional(0.8)->dateTimeBetween($endDate, Carbon::parse($endDate)->addDays(2)),
            ];
        });
    }
}