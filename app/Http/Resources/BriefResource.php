<?php

namespace App\Http\Resources;

use App\Models\Brief;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Brief */
class BriefResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_name' => $this->client_name,
            'industry' => $this->industry,
            'title' => $this->title,
            'brief_date' => $this->brief_date?->toDateString(),
            'budget' => $this->budget,
            'deadline' => $this->deadline?->toDateString(),
            'notes' => $this->notes,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'pitch_assignments' => $this->pitchAssignments ?  PitchAssignmentsResource::collection($this->whenLoaded('pitchAssignments'))->resolve() : [],
            'ai_status' => $this->ai_status?->value,
            'ai_error' => $this->ai_error,
            'primary_file_name' => $this->primary_file_name,
            'created_at' => $this->created_at?->toIso8601String(),
            'creator' => $this->whenLoaded('creator', fn () => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
            ]),
            'latest_analysis' => $this->whenLoaded('latestAnalysis', fn () => new AiAnalysisResource($this->latestAnalysis)),
        ];
    }
}
