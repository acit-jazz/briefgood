<?php

namespace App\Contracts;

use App\DTOs\AI\BriefAnalysisInputDto;
use App\DTOs\AI\BriefAnalysisResultDto;

interface AIProviderInterface
{
    public function analyzeBrief(BriefAnalysisInputDto $input, bool $useAdvancedModel = false): BriefAnalysisResultDto;

    public function providerName(): string;
}
