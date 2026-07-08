<?php

namespace App\Services\AI;

use App\Contracts\AIProviderInterface;
use App\DTOs\AI\BriefAnalysisInputDto;
use App\DTOs\AI\BriefAnalysisResultDto;

class AIOrchestratorService
{
    public function __construct(
        protected AIServiceFactory $factory,
    ) {}

    public function analyze(BriefAnalysisInputDto $input, bool $useAdvancedModel = false, ?string $provider = null): BriefAnalysisResultDto
    {
        $provider = $provider ?? config('ai.provider', 'gemini');
        $aiProvider = $this->factory->make($provider);

        $complexityThreshold = (int) config('briefgood.ai.complexity_threshold', 7);

        $result = $aiProvider->analyzeBrief($input, $useAdvancedModel);

        if (! $useAdvancedModel && $result->pitchComplexityScore >= $complexityThreshold) {
            return $aiProvider->analyzeBrief($input, useAdvancedModel: true);
        }

        return $result;
    }
}
