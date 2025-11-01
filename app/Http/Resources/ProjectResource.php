<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Only returns fields that mobile app needs
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
            'status' => $this->status,
            'type' => $this->type,
            'priority' => $this->priority,
            'client_name' => $this->client_name,
            'client_contact' => $this->client_contact,
            'contact_phone' => $this->contact_phone,
            'contact_email' => $this->contact_email,
            'estimated_employees' => $this->estimated_employees,
            'notes' => $this->notes,
            'project_manager_id' => $this->project_manager_id,
            'site_manager_id' => $this->site_manager_id,

            // Manager names (always return, null if not loaded)
            'project_manager_name' => $this->projectManager
                ? $this->projectManager->first_name . ' ' . $this->projectManager->last_name
                : null,
            'site_manager_name' => $this->siteManager
                ? $this->siteManager->first_name . ' ' . $this->siteManager->last_name
                : null,

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
