<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'project_code' => $this->project_code,
            'name' => $this->name,
            'description' => $this->description,
            'location' => $this->location,
            'city' => $this->city,
            'district' => $this->district,
            'full_address' => $this->full_address,
            'coordinates' => $this->coordinates,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'planned_end_date' => $this->planned_end_date?->format('Y-m-d'),
            'actual_end_date' => $this->actual_end_date?->format('Y-m-d'),
            'budget' => $this->budget,
            'labor_budget' => $this->labor_budget,
            'spent_amount' => $this->spent_amount,
            'remaining_budget' => $this->remaining_budget,
            'budget_usage_percentage' => $this->budget_usage_percentage,
            'status' => $this->status,
            'type' => $this->type,
            'priority' => $this->priority,
            'client_name' => $this->client_name,
            'client_contact' => $this->client_contact,
            'contact_phone' => $this->contact_phone,
            'contact_email' => $this->contact_email,
            'estimated_employees' => $this->estimated_employees,
            'project_duration' => $this->project_duration,
            'elapsed_days' => $this->elapsed_days,
            'completion_percentage' => $this->completion_percentage,
            'is_active' => $this->is_active,
            'is_delayed' => $this->is_delayed,
            'weekend_days' => $this->weekend_days,
            'notes' => $this->notes,

            // İlişkiler (sadece yüklenmişse dahil et)
            'project_manager' => new EmployeeResource($this->whenLoaded('projectManager')),
            'site_manager' => new EmployeeResource($this->whenLoaded('siteManager')),
            'employees_count' => $this->when(isset($this->employees_count), $this->employees_count),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
