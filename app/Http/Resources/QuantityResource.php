<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuantityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Only returns fields that mobile app needs
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'work_item_id' => $this->work_item_id,
            'project_structure_id' => $this->project_structure_id,
            'project_floor_id' => $this->project_floor_id,
            'project_unit_id' => $this->project_unit_id,
            'planned_quantity' => (float) $this->planned_quantity,
            'completed_quantity' => (float) $this->completed_quantity,
            'unit' => $this->unit,
            'measurement_date' => $this->measurement_date?->format('Y-m-d'),
            'measurement_method' => $this->measurement_method,
            'verified_at' => $this->verified_at?->toISOString(),
            'approved_at' => $this->approved_at?->toISOString(),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Nested relations (always return, null if not loaded)
            'project_name' => $this->project?->name,
            'project_code' => $this->project?->project_code,
            'work_item_name' => $this->workItem?->name,
            'work_item_code' => $this->workItem?->code,
            'work_item_unit' => $this->workItem?->unit,
            'work_item_unit_price' => $this->workItem?->default_unit_price
                ? (float) $this->workItem->default_unit_price
                : null,
            'project_structure_name' => $this->projectStructure?->name,
            'project_floor_name' => $this->projectFloor?->name,
            'project_unit_code' => $this->projectUnit?->unit_code,
            'verified_by_name' => $this->verifiedBy?->name,
            'approved_by_name' => $this->approvedBy?->name,
        ];
    }
}
