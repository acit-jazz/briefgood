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
        $model = $this->getModel($useAdvancedModel);

        $prompt = $this->promptBuilder->buildBriefAnalysisPrompt($input);

        try {
            $response = (new BriefAnalysisAgent)->prompt(
                $prompt,
                provider: Lab::Gemini,
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
                'provider' => 'gemini',
                'model' => $model,
                'message' => $exception->getMessage(),
            ]);

            throw new RuntimeException('AI analysis failed: '.$exception->getMessage(), 0, $exception);
        }
    }

    protected function getModel(bool $useAdvancedModel): string
    {
        return $useAdvancedModel
            ? config('briefgood.ai.advanced_model', 'gemini-2.5-pro')
            : config('briefgood.ai.default_model', 'gemini-3.6-flash');
    }
}
