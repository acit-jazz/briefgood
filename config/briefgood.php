<?php

return [

    'ai' => [
        'default_model' => env('BRIEFGOOD_AI_MODEL', 'gemini-2.0-flash'),
        'advanced_model' => env('BRIEFGOOD_AI_ADVANCED_MODEL', 'gemini-2.5-pro'),
        'complexity_threshold' => (int) env('BRIEFGOOD_AI_COMPLEXITY_THRESHOLD', 7),
        'confidence_threshold' => (float) env('BRIEFGOOD_AI_CONFIDENCE_THRESHOLD', 60),
        'timeout' => (int) env('BRIEFGOOD_AI_TIMEOUT', 180),
        'max_retries' => (int) env('BRIEFGOOD_AI_MAX_RETRIES', 2),
        'openrouter' => [
            'default_model' => env('BRIEFGOOD_OPENROUTER_MODEL', 'minimax/minimax-grammarly-sonnet-4-20250514'),
            'advanced_model' => env('BRIEFGOOD_OPENROUTER_ADVANCED_MODEL', 'minimax/minimax-grammarly-sonnet-4-20250514'),
        ],
        'groq' => [
            'default_model' => env('BRIEFGOOD_GROQ_MODEL', 'llama-3.3-70b-versatile'),
            'advanced_model' => env('BRIEFGOOD_GROQ_ADVANCED_MODEL', 'llama-3.3-70b-versatile'),
        ],
        'openai' => [
            'default_model' => env('BRIEFGOOD_OPENAI_MODEL', 'gpt-4o-mini'),
            'advanced_model' => env('BRIEFGOOD_OPENAI_ADVANCED_MODEL', 'gpt-4o'),
        ],
        'anthropic' => [
            'default_model' => env('BRIEFGOOD_ANTHROPIC_MODEL', 'claude-haiku-4-5-20251001'),
            'advanced_model' => env('BRIEFGOOD_ANTHROPIC_ADVANCED_MODEL', 'claude-opus-4-7'),
        ],
    ],

    'uploads' => [
        'max_size_kb' => (int) env('BRIEFGOOD_UPLOAD_MAX_KB', 20480),
        'allowed_mimes' => ['application/pdf'],
        'disk' => env('BRIEFGOOD_UPLOAD_DISK', 'local'),
    ],

];
