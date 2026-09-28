import { Head, router } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { update } from '@/actions/App/Http/Controllers/Admin/SettingsController';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { AlertCircleIcon, SearchIcon, SparklesIcon, SpinnerIcon } from '@/components/ui/icons';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { dashboard } from '@/routes';

type AiModel = {
    id: string;
    name: string;
    context_length: number | null;
    prompt_price: string | null;
    completion_price: string | null;
};

type Props = {
    provider: string;
    currentModel: string;
    models: AiModel[];
    catalogAvailable: boolean;
};

function formatPricePerMillion(price: string | null): string | null {
    if (price === null) return null;
    const perToken = parseFloat(price);
    if (Number.isNaN(perToken)) return null;
    return `$${(perToken * 1_000_000).toFixed(2)} / 1M tokens`;
}

export default function AdminSettings({ provider, currentModel, models, catalogAvailable }: Props) {
    const [selected, setSelected] = useState(currentModel);
    const [processing, setProcessing] = useState(false);
    const [modelSearch, setModelSearch] = useState('');

    const currentModelDetails = useMemo(() => models.find((m) => m.id === currentModel) ?? null, [models, currentModel]);

    const filteredModels = useMemo(() => {
        const query = modelSearch.trim().toLowerCase();
        if (!query) return models;

        const matches = models.filter((m) => m.name.toLowerCase().includes(query) || m.id.toLowerCase().includes(query));

        // Keep the currently selected model in the list even while filtered out,
        // otherwise Radix Select can't render its label on the trigger anymore.
        const selectedModel = models.find((m) => m.id === selected);
        if (selectedModel && !matches.some((m) => m.id === selected)) {
            return [selectedModel, ...matches];
        }

        return matches;
    }, [models, modelSearch, selected]);

    function handleSave() {
        setProcessing(true);
        router.put(
            update.url(),
            { model: selected },
            { onFinish: () => setProcessing(false) },
        );
    }

    return (
        <>
            <Head title="AI settings" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <div>
                    <h1 className="text-lg font-semibold">AI settings</h1>
                    <p className="text-sm text-muted-foreground">
                        Choose which model handles refund analysis. The API key itself is configured via
                        the server environment, never here.
                    </p>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-base">
                            <SparklesIcon className="size-4" />
                            Current model
                        </CardTitle>
                        <CardDescription>
                            Provider: <span className="font-mono">{provider}</span>
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-lg border bg-muted/40 p-4">
                            <div className="font-medium">{currentModelDetails?.name ?? currentModel}</div>
                            <div className="font-mono text-xs text-muted-foreground">{currentModel}</div>
                            {currentModelDetails && (
                                <div className="mt-2 flex flex-wrap gap-2">
                                    {currentModelDetails.context_length && (
                                        <Badge variant="secondary">{currentModelDetails.context_length.toLocaleString()} context</Badge>
                                    )}
                                    {formatPricePerMillion(currentModelDetails.prompt_price) && (
                                        <Badge variant="outline">
                                            In: {formatPricePerMillion(currentModelDetails.prompt_price)}
                                        </Badge>
                                    )}
                                    {formatPricePerMillion(currentModelDetails.completion_price) && (
                                        <Badge variant="outline">
                                            Out: {formatPricePerMillion(currentModelDetails.completion_price)}
                                        </Badge>
                                    )}
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>

                {provider !== 'openrouter' && (
                    <Alert>
                        <AlertCircleIcon />
                        <AlertTitle>Model picker unavailable</AlertTitle>
                        <AlertDescription>
                            Model selection is only available when AI_PROVIDER is set to
                            &quot;openrouter&quot;. The current provider uses a fixed model from AI_MODEL
                            in the environment.
                        </AlertDescription>
                    </Alert>
                )}

                {provider === 'openrouter' && !catalogAvailable && (
                    <Alert variant="destructive">
                        <AlertCircleIcon />
                        <AlertTitle>Could not load the model list</AlertTitle>
                        <AlertDescription>
                            We couldn&apos;t reach OpenRouter&apos;s model catalog. Check that AI_API_KEY
                            is set, then reload this page.
                        </AlertDescription>
                    </Alert>
                )}

                {provider === 'openrouter' && catalogAvailable && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Change model</CardTitle>
                            <CardDescription>Type to search, then save to apply your choice.</CardDescription>
                        </CardHeader>
                        <CardContent className="flex flex-wrap items-end gap-3">
                            <Select
                                value={selected}
                                onValueChange={setSelected}
                                onOpenChange={(open) => {
                                    if (!open) setModelSearch('');
                                }}
                            >
                                <SelectTrigger className="w-full sm:w-96">
                                    <SelectValue placeholder="Select a model" />
                                </SelectTrigger>
                                <SelectContent>
                                    <div className="sticky top-0 z-10 -m-1 mb-1 bg-popover p-1.5 pb-2">
                                        <div className="relative">
                                            <SearchIcon className="absolute top-1/2 left-2.5 size-3.5 -translate-y-1/2 text-muted-foreground" />
                                            <Input
                                                value={modelSearch}
                                                onChange={(e) => setModelSearch(e.target.value)}
                                                onKeyDown={(e) => e.stopPropagation()}
                                                placeholder="Search models..."
                                                className="h-8 pl-8 text-sm"
                                            />
                                        </div>
                                    </div>
                                    {filteredModels.length === 0 && (
                                        <p className="px-2 py-3 text-center text-sm text-muted-foreground">No models match.</p>
                                    )}
                                    {filteredModels.map((model) => (
                                        <SelectItem key={model.id} value={model.id}>
                                            {model.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            <Button onClick={handleSave} disabled={processing || selected === currentModel}>
                                {processing && <SpinnerIcon className="size-4 animate-spin" />}
                                Save model
                            </Button>
                        </CardContent>
                    </Card>
                )}
            </div>
        </>
    );
}

AdminSettings.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'AI Settings', href: '/admin/settings' },
    ],
};
