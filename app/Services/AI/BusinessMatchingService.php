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
            $unit = BusinessUnit::query()
                ->where('name', $recommendation['name'])
                ->orWhere('slug', Str::slug($recommendation['name']))
                ->first();

            AiRecommendation::query()->create([
                'ai_analysis_result_id' => $analysis->id,
                'business_unit_id' => $unit?->id,
                'business_unit_name' => $recommendation['name'],
                'confidence' => $recommendation['confidence'],
                'matched_services' => $recommendation['services'] ?? [],
                'reasoning' => $recommendation['reasoning'] ?? null,
            ]);
        }

        $this->enrichWithKeywordMatching($analysis);
    }

    protected function enrichWithKeywordMatching(AiAnalysisResult $analysis): void
    {
        $text = Str::lower(implode(' ', array_filter([
            $analysis->executive_summary,
            $analysis->scope_of_work,
            $analysis->deliverables,
        ])));

        $keywords = ['tiktok', 'reels', 'social media', 'influencer', 'kol', 'website', 'ux', 'photography', 'video', 'campaign'];

        foreach ($keywords as $keyword) {
            if (! str_contains($text, $keyword)) {
                continue;
            }

            $units = BusinessUnit::query()
                ->whereHas('services', fn ($q) => $q->where('keywords', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%"))
                ->limit(3)
                ->get();

            foreach ($units as $unit) {
                AiRecommendation::query()->firstOrCreate(
                    [
                        'ai_analysis_result_id' => $analysis->id,
                        'business_unit_name' => $unit->name,
                    ],
                    [
                        'business_unit_id' => $unit->id,
                        'confidence' => 65,
                        'reasoning' => "Keyword match: {$keyword}",
                        'matched_services' => $unit->services()->limit(3)->pluck('name')->all(),
                    ],
                );
            }
        }
    }
}
