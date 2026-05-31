<?php

namespace App\Services\AI;

use App\Contracts\AIProviderInterface;
use App\DTOs\AI\BriefAnalysisInputDto;
use App\DTOs\AI\BriefAnalysisResultDto;

class AIOrchestratorService
{
    public function __construct(
        protected AIProviderInterface $provider,
    ) {}

    public function analyze(BriefAnalysisInputDto $input, bool $useAdvancedModel = false): BriefAnalysisResultDto
    {
        $complexityThreshold = (int) config('briefgood.ai.complexity_threshold', 7);

        $result = $this->provider->analyzeBrief($input, $useAdvancedModel);

        if (! $useAdvancedModel && $result->pitchComplexityScore >= $complexityThreshold) {
            return $this->provider->analyzeBrief($input, useAdvancedModel: true);
        }

        return $result;
    }
}
