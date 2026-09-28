<?php

return [
    'provider' => env('AI_PROVIDER', 'openrouter'),

    'api_key' => env('AI_API_KEY'),

    // Fallback model, used until an admin picks one on the settings page (openrouter),
    // or as the fixed model for the openai/anthropic providers.
    'model' => env('AI_MODEL', 'openai/gpt-4o-mini'),

    'timeout_seconds' => (int) env('AI_TIMEOUT_SECONDS', 15),

    'max_retries' => (int) env('AI_MAX_RETRIES', 2),
];
