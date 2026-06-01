<?php

namespace App\Services\AI;

use App\DTOs\AI\BriefAnalysisResultDto;
use App\Models\AiAnalysisResult;
use App\Models\AiRecommendation;
use App\Models\BusinessUnit;
use Illuminate\Support\Str;

class BusinessMatchingService
{
    public function syncRecommendations(AiAnalysisResult $analysis, BriefAnalysisResultDto $result): void
    {
        AiRecommendation::query()
            ->where('ai_analysis_result_id', $analysis->id)
            ->delete();

        foreach ($result->recommendedBusinessUnits as $recommendation) {
            // Strip category suffix like " (Brand Strategy)" from unit name
            $unitNameClean = preg_replace('/\s*\(.*\)$/', '', $recommendation['name']);
            $unit = BusinessUnit::query()
                ->where('name', $unitNameClean)
                ->orWhere('slug', Str::slug($unitNameClean))
                ->first();

            AiRecommendation::query()->create([
                'ai_analysis_result_id' => $analysis->id,
                'business_unit_id' => $unit?->id,
                'business_unit_name' => $unitNameClean,
                'confidence' => $recommendation['confidence'],
                'matched_services' => $recommendation['services'] ?? [],
                'reasoning' => $recommendation['reasoning'] ?? null,
            ]);
        }

        $this->enrichWithKeywordMatching($analysis);
    }

    protected function enrichWithKeywordMatching(AiAnalysisResult $analysis): void
    {
        // Skip enrichment - let the AI provide proper recommendations based on full context
        // The keyword matching was adding low-quality matches with hardcoded 65% confidence
        // which was reducing the overall recommendation quality
    }
}
