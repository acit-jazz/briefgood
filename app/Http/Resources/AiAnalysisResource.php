<?php

namespace App\Http\Resources;

use App\Models\AiAnalysisResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AiAnalysisResult */
class AiAnalysisResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'executive_summary' => $this->executive_summary,
            'brand_overview' => $this->brand_overview,
            'campaign_objective' => $this->campaign_objective,
            'target_audience' => $this->target_audience,
            'scope_of_work' => $this->scope_of_work,
            'deliverables' => $this->deliverables,
            'timeline' => $this->timeline,
            'budget' => $this->budget,
            'mandatory_requirements' => $this->mandatory_requirements,
            'recommended_business_units' => $this->recommended_business_units,
            'recommended_services' => $this->recommended_services,
            'recommended_resources' => $this->recommended_resources,
            'pitch_complexity_score' => $this->pitch_complexity_score,
            'ai_confidence_score' => $this->ai_confidence_score,
            'ai_reasoning' => $this->ai_reasoning,
            'model_used' => $this->model_used,
            'recommendations' => AiRecommendationResource::collection($this->whenLoaded('recommendations')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
