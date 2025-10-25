<?php

namespace App\Listeners;

use App\Events\ProgressPaymentPaidEvent;
use App\Services\Financial\FinancialTransactionService;
use Illuminate\Support\Facades\Log;

class CreateFinancialTransactionForProgressPayment
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
    public function handle(ProgressPaymentPaidEvent $event): void
    {
        try {
            $this->financialService->createFromProgressPayment($event->progressPayment);

            Log::info("Financial transaction created for progress payment", [
                'progress_payment_id' => $event->progressPayment->id,
                'subcontractor_id' => $event->progressPayment->subcontractor_id,
                'amount' => $event->progressPayment->total_amount,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to create financial transaction for progress payment", [
                'progress_payment_id' => $event->progressPayment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
