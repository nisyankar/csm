<?php

namespace App\Listeners;

use App\Events\PurchaseOrderApprovedEvent;
use App\Services\Financial\FinancialTransactionService;
use Illuminate\Support\Facades\Log;

class CreateFinancialTransactionForPurchase
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
    public function handle(PurchaseOrderApprovedEvent $event): void
    {
        try {
            $this->financialService->createFromPurchaseOrder($event->purchaseOrder);

            Log::info("Financial transaction created for purchase order", [
                'purchase_order_id' => $event->purchaseOrder->id,
                'supplier_id' => $event->purchaseOrder->supplier_id,
                'amount' => $event->purchaseOrder->total_amount,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to create financial transaction for purchase order", [
                'purchase_order_id' => $event->purchaseOrder->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
