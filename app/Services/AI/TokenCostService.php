<?php

namespace App\Services\AI;

class TokenCostService
{
    private const PRICING = [
        // Gemini
        'gemini-2.0-flash' => ['input' => 0.075, 'output' => 0.30],
        'gemini-2.5-pro' => ['input' => 1.25, 'output' => 10.00],

        // OpenAI
        'gpt-4o-mini' => ['input' => 0.15, 'output' => 0.60],
        'gpt-4o' => ['input' => 2.50, 'output' => 10.00],

        // Anthropic
        'claude-haiku-4-5-20251001' => ['input' => 0.80, 'output' => 4.00],
        'claude-opus-4-7' => ['input' => 15.00, 'output' => 75.00],
    ];

    public static function calculate(string $model, int $promptTokens, int $completionTokens): float
    {
        $pricing = self::PRICING[$model] ?? self::getDefaultPricing($model);

        if (!$pricing) {
            return 0.0;
        }

        $inputCost = ($promptTokens / 1_000_000) * $pricing['input'];
        $outputCost = ($completionTokens / 1_000_000) * $pricing['output'];

        return round($inputCost + $outputCost, 6);
    }

    public static function getModelPricing(string $model): ?array
    {
        return self::PRICING[$model] ?? null;
    }

    public static function getAllModels(): array
    {
        return array_keys(self::PRICING);
    }

    private static function getDefaultPricing(string $model): ?array
    {
        foreach (self::PRICING as $key => $pricing) {
            if (str_contains($key, 'gemini') && str_contains($model, 'gemini')) {
                return $pricing;
            }
            if (str_contains($key, 'gpt') && str_contains($model, 'gpt')) {
                return $pricing;
            }
            if (str_contains($key, 'claude') && str_contains($model, 'claude')) {
                return $pricing;
            }
        }

        return null;
    }
}
