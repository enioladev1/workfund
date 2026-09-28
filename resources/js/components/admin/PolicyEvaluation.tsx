import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { CheckmarkCircleIcon, XIcon } from '@/components/ui/icons';
import type { PolicyResult } from '@/types';

export function PolicyEvaluation({ policyResult }: { policyResult: PolicyResult }) {
    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-base">Policy evaluation</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
                {policyResult.rules.map((rule) => (
                    <div key={rule.key} className="flex items-start gap-3 text-sm">
                        {rule.passed ? (
                            <CheckmarkCircleIcon className="mt-0.5 size-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                        ) : (
                            <XIcon className="mt-0.5 size-4 shrink-0 text-red-600 dark:text-red-400" />
                        )}
                        <div>
                            <div className="font-medium">{rule.label}</div>
                            <div className="text-muted-foreground">{rule.message}</div>
                        </div>
                    </div>
                ))}
                {policyResult.hard_decision && (
                    <p className="border-t pt-3 text-sm font-medium">
                        Deterministic result: <span className="capitalize">{policyResult.hard_decision}</span>
                    </p>
                )}
            </CardContent>
        </Card>
    );
}
