<?php

namespace App\Services\AI;

use App\Models\AiSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Admin-editable AI settings (currently just which OpenRouter model to use).
 * The API key is never stored here; it only ever comes from AI_API_KEY in .env.
 */
class AiSettingsService
{
    private const CACHE_KEY = 'ai_settings:selected_model';

    private const CACHE_TTL_SECONDS = 3600;

    public function getSelectedModel(): ?string
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            $setting = AiSetting::query()->where('key', 'selected_model')->first();

            return $setting?->value['value'] ?? null;
        });
    }

    public function setSelectedModel(string $model): void
    {
        AiSetting::query()->updateOrCreate(
            ['key' => 'selected_model'],
            ['value' => ['value' => $model]],
        );

        Cache::forget(self::CACHE_KEY);
    }
}
