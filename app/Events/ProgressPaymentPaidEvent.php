<?php

namespace App\Events;

use App\Models\ProgressPayment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProgressPaymentPaidEvent
{
    use Dispatchable, SerializesModels;

    public ProgressPayment $progressPayment;

    /**
     * Create a new event instance.
     */
    public function __construct(ProgressPayment $progressPayment)
    {
        $this->progressPayment = $progressPayment;
    }
}
