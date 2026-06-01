<?php

namespace App\Http\Resources;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Service */
class ServiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'keywords' => $this->keywords,
            'is_active' => $this->is_active,
            'business_units' => $this->whenLoaded('businessUnits', fn () =>
                $this->businessUnits->map(fn ($bu) => [
                    'id' => $bu->id,
                    'name' => $bu->name,
                    'specialization_score' => $bu->pivot->specialization_score,
                    'notes' => $bu->pivot->notes,
                ])
            ),
        ];
    }
}
