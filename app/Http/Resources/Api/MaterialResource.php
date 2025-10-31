<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'unit' => $this->unit,
            'estimated_unit_price' => (float) $this->estimated_unit_price,
            'current_stock' => (float) ($this->current_stock ?? 0),
            'min_stock_level' => (float) ($this->min_stock_level ?? 0),
            'is_low_stock' => $this->current_stock < $this->min_stock_level,
            'material_code' => $this->material_code,
            'specification' => $this->specification,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
