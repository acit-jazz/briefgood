<?php

namespace App\Http\Resources;

use App\Models\BusinessUnit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BusinessUnit */
class PitchAssignmentsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'confidence' => $this->confidence,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'internal_notes' => $this->internal_notes,
            'business_unit' => $this->business_unit_id ? new BusinessUnitResource($this->businessUnit) : null,
        ];
    }
}
