<?php

namespace App\Listeners;

use App\Events\TimesheetApprovedEvent;
use App\Services\Financial\FinancialTransactionService;
use Illuminate\Support\Facades\Log;

class CreateFinancialTransactionForTimesheet
{
    protected FinancialTransactionService $financialService;

    /**
     * Create the event listener.
     */
    public function __construct(FinancialTransactionService $financialService)
    {
        $this->financialService = $financialService;
    }

    /**
     * Handle the event.
     */
    public function handle(TimesheetApprovedEvent $event): void
    {
        try {
            $this->financialService->createFromTimesheet($event->timesheet);

            Log::info("Financial transaction created for timesheet", [
                'timesheet_id' => $event->timesheet->id,
                'employee_id' => $event->timesheet->employee_id,
                'amount' => $event->timesheet->calculated_wage,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to create financial transaction for timesheet", [
                'timesheet_id' => $event->timesheet->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
