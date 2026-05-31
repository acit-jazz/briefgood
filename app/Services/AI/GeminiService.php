<?php

namespace App\Services\AI;

use App\Agents\BriefAnalysisAgent;
use App\Contracts\AIProviderInterface;
use App\DTOs\AI\BriefAnalysisInputDto;
use App\DTOs\AI\BriefAnalysisResultDto;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Enums\Lab;
use RuntimeException;

class GeminiService implements AIProviderInterface
{
    public function __construct(
        protected PromptBuilderService $promptBuilder,
    ) {}

    public function providerName(): string
    {
        return 'gemini';
    }

    public function analyzeBrief(BriefAnalysisInputDto $input, bool $useAdvancedModel = false): BriefAnalysisResultDto
    {
        $model = $useAdvancedModel
            ? config('briefgood.ai.advanced_model', 'gemini-2.5-pro')
            : config('briefgood.ai.default_model', 'gemini-2.0-flash');

        $prompt = $this->promptBuilder->buildBriefAnalysisPrompt($input);

        try {
            $response = (new BriefAnalysisAgent)->prompt(
                $prompt,
                provider: Lab::Gemini,
                model: $model,
                timeout: (int) config('briefgood.ai.timeout', 180),
            );

            $data = is_array($response) ? $response : $response->toArray();

            return BriefAnalysisResultDto::fromArray($data, $model);
        } catch (\Throwable $exception) {
            Log::error('Gemini brief analysis failed', [
                'brief_id' => $input->briefId,
                'message' => $exception->getMessage(),
            ]);

            throw new RuntimeException('AI analysis failed: '.$exception->getMessage(), 0, $exception);
        }
    }
}
