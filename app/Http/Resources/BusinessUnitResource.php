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
            'logo_path' => $this->logo_path,
            'logo_url' => $this->logo_url ? $this->logo_url : 'https://ui-avatars.com/api/?font-size=0.33&background=random&name=' . urlencode($this->name),
            'is_active' => $this->is_active,
            'category_id' => $this->category?->id,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ]),
            'services' => $this->whenLoaded('services', fn () =>
                $this->services->map(fn ($service) => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'slug' => $service->slug,
                    'description' => $service->description,
                    'keywords' => $service->keywords,
                    'specialization_score' => $service->pivot->specialization_score,
                    'notes' => $service->pivot->notes,
                ])
            ),
            'pic' => $this->whenLoaded('pic', fn () => [
                'id' => $this->pic?->id,
                'name' => $this->pic?->name,
                'email' => $this->pic?->email,
            ]),
        ];
    }
}
