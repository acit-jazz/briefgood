<?php

namespace App\DTOs\AI;

readonly class BriefAnalysisResultDto
{
    /**
     * @param  list<array{name: string, confidence: float, services: list<string>, reasoning: string}>  $recommendedBusinessUnits
     * @param  list<array{name: string, estimated_hours: float, workload_percent: int, duration_days: int}>  $recommendedResources
     */
    public function __construct(
        public string $executiveSummary,
        public string $brandOverview,
        public string $campaignObjective,
        public string $targetAudience,
        public string $scopeOfWork,
        public string $deliverables,
        public string $timeline,
        public string $budget,
        public string $mandatoryRequirements,
        public array $recommendedBusinessUnits,
        public array $recommendedServices,
        public array $recommendedResources,
        public int $pitchComplexityScore,
        public float $aiConfidenceScore,
        public string $aiReasoning,
        public string $modelUsed,
        public array $rawResponse = [],
        public int $promptTokens = 0,
        public int $completionTokens = 0,
        public int $totalTokens = 0,
        public float $costUsd = 0.0,
    ) {}

    /**
     * Normalize a value to array - handles JSON strings and other edge cases
     */
    protected static function normalizeArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, string $modelUsed): self
    {
        return new self(
            executiveSummary: (string) ($data['executive_summary'] ?? ''),
            brandOverview: (string) ($data['brand_overview'] ?? ''),
            campaignObjective: (string) ($data['campaign_objective'] ?? ''),
            targetAudience: (string) ($data['target_audience'] ?? ''),
            scopeOfWork: (string) ($data['scope_of_work'] ?? ''),
            deliverables: (string) ($data['deliverables'] ?? ''),
            timeline: (string) ($data['timeline'] ?? ''),
            budget: (string) ($data['budget'] ?? ''),
            mandatoryRequirements: (string) ($data['mandatory_requirements'] ?? ''),
            recommendedBusinessUnits: self::normalizeArray($data['recommended_business_units'] ?? null),
            recommendedServices: self::normalizeArray($data['recommended_services'] ?? null),
            recommendedResources: self::normalizeArray($data['recommended_resources'] ?? null),
            pitchComplexityScore: (int) ($data['pitch_complexity_score'] ?? 5),
            aiConfidenceScore: (float) ($data['ai_confidence_score'] ?? 0),
            aiReasoning: (string) ($data['ai_reasoning'] ?? ''),
            modelUsed: $modelUsed,
            rawResponse: $data,
        );
    }

    public static function fromArrayWithUsage(
        array $data,
        string $modelUsed,
        int $promptTokens,
        int $completionTokens,
        float $costUsd
    ): self {
        $totalTokens = $promptTokens + $completionTokens;

        return new self(
            executiveSummary: (string) ($data['executive_summary'] ?? ''),
            brandOverview: (string) ($data['brand_overview'] ?? ''),
            campaignObjective: (string) ($data['campaign_objective'] ?? ''),
            targetAudience: (string) ($data['target_audience'] ?? ''),
            scopeOfWork: (string) ($data['scope_of_work'] ?? ''),
            deliverables: (string) ($data['deliverables'] ?? ''),
            timeline: (string) ($data['timeline'] ?? ''),
            budget: (string) ($data['budget'] ?? ''),
            mandatoryRequirements: (string) ($data['mandatory_requirements'] ?? ''),
            recommendedBusinessUnits: self::normalizeArray($data['recommended_business_units'] ?? null),
            recommendedServices: self::normalizeArray($data['recommended_services'] ?? null),
            recommendedResources: self::normalizeArray($data['recommended_resources'] ?? null),
            pitchComplexityScore: (int) ($data['pitch_complexity_score'] ?? 5),
            aiConfidenceScore: (float) ($data['ai_confidence_score'] ?? 0),
            aiReasoning: (string) ($data['ai_reasoning'] ?? ''),
            modelUsed: $modelUsed,
            rawResponse: $data,
            promptTokens: $promptTokens,
            completionTokens: $completionTokens,
            totalTokens: $totalTokens,
            costUsd: $costUsd,
        );
    }
}
