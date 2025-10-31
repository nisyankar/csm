<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TemporaryAssignmentService;
use Illuminate\Support\Facades\Log;

class AutoCompleteAssignments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assignments:auto-complete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-complete expired temporary assignments';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Expired temporary assignments auto-completing starting...');

        try {
            $service = new TemporaryAssignmentService();
            $count = $service->autoCompleteExpired();

            $message = "Auto-completed {$count} expired temporary assignments.";
            $this->info($message);
            Log::info('AutoCompleteAssignments Command', ['completed_count' => $count]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $message = "Error auto-completing assignments: " . $e->getMessage();
            $this->error($message);
            Log::error('AutoCompleteAssignments Command Error', ['error' => $e->getMessage()]);

            return Command::FAILURE;
        }
    }
}
