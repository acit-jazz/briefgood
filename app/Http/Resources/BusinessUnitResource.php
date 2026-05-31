<?php

namespace App\Http\Resources;

use App\Models\BusinessUnit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BusinessUnit */
class BusinessUnitResource extends JsonResource
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
            'is_active' => $this->is_active,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ]),
            'services' => ServiceResource::collection($this->whenLoaded('services')),
            'pic' => $this->whenLoaded('pic', fn () => [
                'id' => $this->pic?->id,
                'name' => $this->pic?->name,
            ]),
        ];
    }
}
