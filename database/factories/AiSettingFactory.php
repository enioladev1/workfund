<?php

namespace Database\Factories;

use App\Models\AiSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiSetting>
 */
class AiSettingFactory extends Factory
{
    protected $model = AiSetting::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => 'selected_model',
            'value' => ['value' => 'openai/gpt-4o-mini'],
        ];
    }
}
