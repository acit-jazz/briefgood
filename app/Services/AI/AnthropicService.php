<?php

namespace App\Services\AI;

use App\Agents\BriefAnalysisAgent;
use App\Contracts\AIProviderInterface;
use App\DTOs\AI\BriefAnalysisInputDto;
use App\DTOs\AI\BriefAnalysisResultDto;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Enums\Lab;
use RuntimeException;

class AnthropicService implements AIProviderInterface
{
    public function __construct(
        protected PromptBuilderService $promptBuilder,
    ) {}

    public function providerName(): string
    {
        return 'anthropic';
    }

    public function analyzeBrief(BriefAnalysisInputDto $input, bool $useAdvancedModel = false): BriefAnalysisResultDto
    {
        $model = $this->getModel($useAdvancedModel);

        $prompt = $this->promptBuilder->buildBriefAnalysisPrompt($input);

        try {
            $response = (new BriefAnalysisAgent)->prompt(
                $prompt,
                provider: Lab::Anthropic,
                model: $model,
                timeout: (int) config('briefgood.ai.timeout', 180),
            );

            $data = is_array($response) ? $response : $response->toArray();

            $usage = is_array($response) ? null : $response->usage;
            $promptTokens = $usage?->promptTokens ?? 0;
            $completionTokens = $usage?->completionTokens ?? 0;
            $costUsd = TokenCostService::calculate($model, $promptTokens, $completionTokens);

            return BriefAnalysisResultDto::fromArrayWithUsage($data, $model, $promptTokens, $completionTokens, $costUsd);
        } catch (\Throwable $exception) {
            Log::error('AI brief analysis failed', [
                'brief_id' => $input->briefId,
                'provider' => 'anthropic',
                'model' => $model,
                'message' => $exception->getMessage(),
            ]);

            throw new RuntimeException('AI analysis failed: '.$exception->getMessage(), 0, $exception);
        }
    }

    protected function getModel(bool $useAdvancedModel): string
    {
        return $useAdvancedModel
            ? config('briefgood.ai.anthropic.advanced_model', 'claude-opus-4-7')
            : config('briefgood.ai.anthropic.default_model', 'claude-haiku-4-5-20251001');
    }
}
