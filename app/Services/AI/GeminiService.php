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
        return config('ai.provider', 'gemini');
    }

    public function analyzeBrief(BriefAnalysisInputDto $input, bool $useAdvancedModel = false): BriefAnalysisResultDto
    {
        $provider = config('ai.provider', 'gemini');
        $model = $this->getModel($useAdvancedModel);
        $lab = $this->getLab($provider);

        $prompt = $this->promptBuilder->buildBriefAnalysisPrompt($input);

        try {
            $response = (new BriefAnalysisAgent)->prompt(
                $prompt,
                provider: $lab,
                model: $model,
                timeout: (int) config('briefgood.ai.timeout', 180),
            );

            $data = is_array($response) ? $response : $response->toArray();

            return BriefAnalysisResultDto::fromArray($data, $model);
        } catch (\Throwable $exception) {
            Log::error('AI brief analysis failed', [
                'brief_id' => $input->briefId,
                'provider' => $provider,
                'model' => $model,
                'message' => $exception->getMessage(),
            ]);

            throw new RuntimeException('AI analysis failed: '.$exception->getMessage(), 0, $exception);
        }
    }

    protected function getModel(bool $useAdvancedModel): string
    {
        $provider = config('ai.provider', 'gemini');

        if ($provider === 'openrouter') {
            return $useAdvancedModel
                ? config('briefgood.ai.openrouter.advanced_model', 'minimax/minimax-grammarly-sonnet-4-20250514')
                : config('briefgood.ai.openrouter.default_model', 'minimax/minimax-grammarly-sonnet-4-20250514');
        }

        return $useAdvancedModel
            ? config('briefgood.ai.advanced_model', 'gemini-2.5-pro')
            : config('briefgood.ai.default_model', 'gemini-2.0-flash');
    }

    protected function getLab(string $provider): Lab
    {
        return match ($provider) {
            'openrouter' => Lab::OpenRouter,
            default => Lab::Gemini,
        };
    }
}
