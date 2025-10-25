<?php

namespace App\Events;

use App\Models\Timesheet;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TimesheetApprovedEvent
{
    use Dispatchable, SerializesModels;

    public Timesheet $timesheet;

    /**
     * Create a new event instance.
     */
    public function __construct(Timesheet $timesheet)
    {
        $this->timesheet = $timesheet;
    }
}
