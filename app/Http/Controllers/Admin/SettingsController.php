<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAiSettingsRequest;
use App\Services\AI\AiSettingsService;
use App\Services\AI\OpenRouterModelCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __construct(
        private readonly AiSettingsService $settings,
        private readonly OpenRouterModelCatalog $catalog,
    ) {}

    public function edit(): Response
    {
        $provider = config('ai.provider');

        return Inertia::render('admin/settings', [
            'provider' => $provider,
            'currentModel' => $this->settings->getSelectedModel() ?? config('ai.model'),
            'models' => $provider === 'openrouter' ? $this->catalog->models() : [],
            'catalogAvailable' => $provider === 'openrouter' && $this->catalog->models() !== [],
        ]);
    }

    public function update(UpdateAiSettingsRequest $request): RedirectResponse
    {
        $model = $request->string('model')->toString();

        $availableModelIds = array_column($this->catalog->models(), 'id');

        if ($availableModelIds !== [] && ! in_array($model, $availableModelIds, true)) {
            throw ValidationException::withMessages([
                'model' => 'That model is not in the current OpenRouter catalog. Please pick one from the list.',
            ]);
        }

        $this->settings->setSelectedModel($model);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('AI model updated.')]);

        return back();
    }
}
