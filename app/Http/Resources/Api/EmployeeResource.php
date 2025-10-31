<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'employee_code' => $this->employee_code,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'tc_no' => $this->when($request->user()?->can('view-sensitive-data'), $this->tc_no),
            'phone' => $this->phone,
            'email' => $this->email,
            'photo' => $this->photo,
            'photo_url' => $this->photo_url,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'hire_date' => $this->hire_date?->format('Y-m-d'),
            'gender' => $this->gender,
            'marital_status' => $this->marital_status,
            'position' => $this->position,
            'department' => $this->department,
            'employment_status' => $this->employment_status,
            'employment_type' => $this->employment_type,
            'daily_wage' => $this->when($request->user()?->can('view-salaries'), $this->daily_wage),
            'hourly_wage' => $this->when($request->user()?->can('view-salaries'), $this->hourly_wage),
            'monthly_salary' => $this->when($request->user()?->can('view-salaries'), $this->monthly_salary),
            'is_active' => $this->is_active,
            'qr_code' => $this->qr_code,
            'current_project_id' => $this->current_project_id,

            // İlişkiler
            'current_project' => new ProjectResource($this->whenLoaded('currentProject')),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
