<?php

namespace App\Services\AI;

use App\Contracts\AIProviderInterface;
use InvalidArgumentException;

class AIServiceFactory
{
    protected array $serviceClasses = [
        'gemini' => GeminiService::class,
        'openai' => OpenAIService::class,
        'anthropic' => AnthropicService::class,
    ];

    /** @var array<string, AIProviderInterface|null> */
    protected array $resolvedServices = [];

    public function make(string $provider): AIProviderInterface
    {
        $provider = strtolower($provider);

        if (! isset($this->serviceClasses[$provider])) {
            throw new InvalidArgumentException("AI provider [{$provider}] is not supported.");
        }

        if (! isset($this->resolvedServices[$provider])) {
            $this->resolvedServices[$provider] = app($this->serviceClasses[$provider]);
        }

        return $this->resolvedServices[$provider];
    }

    public function supportedProviders(): array
    {
        return array_keys($this->serviceClasses);
    }
}
