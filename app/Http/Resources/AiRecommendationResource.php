<?php

namespace App\Http\Resources;

use App\Models\AiRecommendation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AiRecommendation */
class AiRecommendationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_unit_id' => $this->business_unit_id,
            'business_unit_name' => $this->business_unit_name,
            'confidence' => $this->confidence,
            'matched_services' => $this->matched_services,
            'reasoning' => $this->reasoning,
        ];
    }
}
