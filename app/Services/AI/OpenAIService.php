<?php

namespace App\Services\AI;

use App\Agents\BriefAnalysisAgent;
use App\Contracts\AIProviderInterface;
use App\DTOs\AI\BriefAnalysisInputDto;
use App\DTOs\AI\BriefAnalysisResultDto;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Enums\Lab;
use RuntimeException;

class OpenAIService implements AIProviderInterface
{
    public function __construct(
        protected PromptBuilderService $promptBuilder,
    ) {}

    public function providerName(): string
    {
        return 'openai';
    }

    public function analyzeBrief(BriefAnalysisInputDto $input, bool $useAdvancedModel = false): BriefAnalysisResultDto
    {
        $model = $this->getModel($useAdvancedModel);

        $prompt = $this->promptBuilder->buildBriefAnalysisPrompt($input);

        try {
            $response = (new BriefAnalysisAgent)->prompt(
                $prompt,
                provider: Lab::OpenAI,
                model: $model,
                timeout: (int) config('briefgood.ai.timeout', 180),
            );

            $data = is_array($response) ? $response : $response->toArray();

            return BriefAnalysisResultDto::fromArray($data, $model);
        } catch (\Throwable $exception) {
            Log::error('AI brief analysis failed', [
                'brief_id' => $input->briefId,
                'provider' => 'openai',
                'model' => $model,
                'message' => $exception->getMessage(),
            ]);

            throw new RuntimeException('AI analysis failed: '.$exception->getMessage(), 0, $exception);
        }
    }

    protected function getModel(bool $useAdvancedModel): string
    {
        return $useAdvancedModel
            ? config('briefgood.ai.openai.advanced_model', 'gpt-4o')
            : config('briefgood.ai.openai.default_model', 'gpt-4o-mini');
    }
}
