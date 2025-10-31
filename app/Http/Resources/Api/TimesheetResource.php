<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimesheetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'project_id' => $this->project_id,
            'date' => $this->date?->format('Y-m-d'),
            'check_in_time' => $this->check_in_time,
            'check_out_time' => $this->check_out_time,
            'check_in_method' => $this->check_in_method,
            'check_out_method' => $this->check_out_method,
            'check_in_location' => $this->check_in_location,
            'check_out_location' => $this->check_out_location,
            'total_hours' => $this->total_hours,
            'regular_hours' => $this->regular_hours,
            'overtime_hours' => $this->overtime_hours,
            'break_duration' => $this->break_duration,
            'status' => $this->status,
            'approval_status' => $this->approval_status,
            'notes' => $this->notes,
            'rejection_reason' => $this->rejection_reason,

            // Hesaplanan alanlar
            'is_overtime' => $this->is_overtime,
            'is_late' => $this->is_late,
            'is_early_leave' => $this->is_early_leave,

            // İlişkiler
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'project' => new ProjectResource($this->whenLoaded('project')),

            // Timestamps
            'approved_at' => $this->approved_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
