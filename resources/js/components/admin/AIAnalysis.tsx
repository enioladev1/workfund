import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AlertDiamondIcon, SparklesIcon } from '@/components/ui/icons';
import type { AiInteraction, AiResult } from '@/types';

export function AIAnalysis({ aiResult, interactions }: { aiResult: AiResult; interactions: AiInteraction[] }) {
    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2 text-base">
                    <SparklesIcon className="size-4" />
                    AI analysis
                </CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
                {aiResult ? (
                    <>
                        <div className="flex flex-wrap items-center gap-2">
                            <Badge variant="secondary" className="capitalize">
                                {aiResult.classification.replace(/_/g, ' ')}
                            </Badge>
                            <Badge variant="outline">{Math.round(aiResult.confidence * 100)}% confidence</Badge>
                            {aiResult.suspicious && (
                                <Badge variant="destructive">
                                    <AlertDiamondIcon className="size-3" />
                                    Suspicious
                                </Badge>
                            )}
                            {aiResult.conflict_detected && <Badge variant="destructive">Conflicting information</Badge>}
                        </div>
                        <p className="text-sm text-muted-foreground">{aiResult.reasoning}</p>
                        <p className="text-sm">
                            Recommended action: <span className="font-medium capitalize">{aiResult.recommended_action}</span>
                        </p>
                    </>
                ) : (
                    <p className="text-sm text-muted-foreground">
                        Automated analysis was unavailable for this request; it was sent for manual review.
                    </p>
                )}

                {interactions.length > 0 && (
                    <div className="border-t pt-3 text-xs text-muted-foreground">
                        {interactions.map((interaction) => (
                            <div key={interaction.id} className="flex justify-between py-0.5">
                                <span>
                                    {interaction.provider} / {interaction.model} ({interaction.status})
                                </span>
                                <span>{interaction.latency_ms ? `${interaction.latency_ms}ms` : 'n/a'}</span>
                            </div>
                        ))}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
