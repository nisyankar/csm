<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgressPaymentResource extends JsonResource
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
            'project' => [
                'id' => $this->project->id ?? null,
                'name' => $this->project->name ?? null,
                'code' => $this->project->project_code ?? null,
            ],
            'subcontractor' => $this->when($this->subcontractor, [
                'id' => $this->subcontractor->id ?? null,
                'name' => $this->subcontractor->name ?? null,
                'company' => $this->subcontractor->company_name ?? null,
            ]),
            'work_item' => $this->when($this->workItem, [
                'id' => $this->workItem->id ?? null,
                'name' => $this->workItem->name ?? null,
                'code' => $this->workItem->code ?? null,
            ]),
            'structure' => $this->when($this->projectStructure, [
                'id' => $this->projectStructure->id ?? null,
                'name' => $this->projectStructure->name ?? null,
            ]),
            'floor' => $this->when($this->projectFloor, [
                'id' => $this->projectFloor->id ?? null,
                'name' => $this->projectFloor->name ?? null,
            ]),
            'unit' => $this->when($this->projectUnit, [
                'id' => $this->projectUnit->id ?? null,
                'name' => $this->projectUnit->name ?? null,
            ]),
            'planned_quantity' => (float) $this->planned_quantity,
            'completed_quantity' => (float) $this->completed_quantity,
            'unit_type' => $this->unit,
            'unit_price' => (float) $this->unit_price,
            'total_amount' => (float) ($this->unit_price * $this->completed_quantity),
            'completion_percentage' => (float) $this->completion_percentage,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'payment_date' => $this->payment_date?->format('Y-m-d'),
            'period' => [
                'year' => $this->period_year,
                'month' => $this->period_month,
                'label' => $this->period_year && $this->period_month
                    ? sprintf('%d/%02d', $this->period_year, $this->period_month)
                    : null,
            ],
            'is_quantity_overrun' => (bool) $this->is_quantity_overrun,
            'overrun_amount' => $this->when($this->is_quantity_overrun, (float) $this->overrun_amount),
            'overrun_notes' => $this->when($this->is_quantity_overrun, $this->overrun_notes),
            'notes' => $this->notes,
            'approved_by' => $this->when($this->approvedBy, [
                'id' => $this->approvedBy->id ?? null,
                'name' => $this->approvedBy->name ?? null,
            ]),
            'approved_at' => $this->approved_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Status label helper
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Beklemede',
            'in_progress' => 'Devam Ediyor',
            'completed' => 'Tamamlandı',
            'approved' => 'Onaylandı',
            'rejected' => 'Reddedildi',
            'paid' => 'Ödendi',
            default => 'Bilinmeyen',
        };
    }
}
