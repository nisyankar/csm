<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProgressPayment;
use App\Models\Project;
use App\Models\Subcontractor;
use App\Models\WorkItem;
use App\Models\ProjectStructure;
use App\Models\ProjectFloor;

class ProgressPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing data
        $projects = Project::all();
        $subcontractors = Subcontractor::all();
        $workItems = WorkItem::all();

        if ($projects->isEmpty() || $subcontractors->isEmpty() || $workItems->isEmpty()) {
            $this->command->warn('Please ensure you have projects, subcontractors, and work items seeded first.');
            return;
        }

        $this->command->info('Creating progress payment records...');

        // Create progress payments for each project
        foreach ($projects->take(3) as $project) {
            $structures = $project->structures;

            // For each subcontractor
            foreach ($subcontractors->take(3) as $subcontractor) {
                // For each work item type
                foreach ($workItems->take(5) as $workItem) {
                    $structure = $structures->random();
                    $floor = $structure->floors->isNotEmpty() ? $structure->floors->random() : null;

                    // Create 2-3 progress payments per combination
                    $count = rand(2, 3);
                    for ($i = 0; $i < $count; $i++) {
                        $plannedQuantity = rand(100, 1000);
                        // Randomly make some 100% complete
                        $isComplete = rand(0, 3) === 0; // 25% chance of being complete
                        $completedQuantity = $isComplete ? $plannedQuantity : rand(0, $plannedQuantity);
                        $unitPrice = rand(50, 500);

                        $status = $this->determineStatus($completedQuantity, $plannedQuantity);

                        ProgressPayment::create([
                            'project_id' => $project->id,
                            'subcontractor_id' => $subcontractor->id,
                            'work_item_id' => $workItem->id,
                            'project_structure_id' => $structure->id ?? null,
                            'project_floor_id' => $floor?->id ?? null,
                            'planned_quantity' => $plannedQuantity,
                            'completed_quantity' => $completedQuantity,
                            'unit' => $workItem->unit ?? 'm²',
                            'unit_price' => $unitPrice,
                            'status' => $status,
                            'period_year' => now()->year,
                            'period_month' => rand(1, 12),
                            'payment_date' => $status === 'paid' ? now()->subDays(rand(1, 30)) : null,
                            'approved_by' => $status === 'approved' || $status === 'paid' ? 1 : null,
                            'approved_at' => $status === 'approved' || $status === 'paid' ? now()->subDays(rand(1, 15)) : null,
                            'notes' => rand(0, 1) ? 'Test hakediş kaydı - ' . fake()->sentence() : null,
                        ]);
                    }
                }
            }
        }

        $totalCreated = ProgressPayment::count();
        $this->command->info("Created {$totalCreated} progress payment records.");

        // Show summary by status
        $this->command->info("\nSummary by status:");
        $statusCounts = ProgressPayment::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        foreach ($statusCounts as $statusCount) {
            $this->command->info("  {$statusCount->status}: {$statusCount->count}");
        }
    }

    /**
     * Determine status based on completion
     */
    private function determineStatus(float $completed, float $planned): string
    {
        $percentage = ($completed / $planned) * 100;

        if ($percentage == 0) {
            return 'planned';
        } elseif ($percentage < 100) {
            return rand(0, 1) ? 'in_progress' : 'planned';
        } elseif ($percentage >= 100) {
            // Randomly assign completed, approved, or paid
            $rand = rand(0, 2);
            return match($rand) {
                0 => 'completed',
                1 => 'approved',
                2 => 'paid',
            };
        }

        return 'in_progress';
    }
}