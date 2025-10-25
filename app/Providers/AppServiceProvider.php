<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Finansal Yönetim Event/Listener kayıtları
        Event::listen(
            \App\Events\TimesheetApprovedEvent::class,
            \App\Listeners\CreateFinancialTransactionForTimesheet::class,
        );

        Event::listen(
            \App\Events\PurchaseOrderApprovedEvent::class,
            \App\Listeners\CreateFinancialTransactionForPurchase::class,
        );

        Event::listen(
            \App\Events\ProgressPaymentPaidEvent::class,
            \App\Listeners\CreateFinancialTransactionForProgressPayment::class,
        );
    }
}
