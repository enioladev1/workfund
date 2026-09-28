import { router } from '@inertiajs/react';
import { useState } from 'react';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AlertCircleIcon, SpinnerIcon } from '@/components/ui/icons';
import { Textarea } from '@/components/ui/textarea';
import { api, ApiError } from '@/lib/api';

export function RefundResolveAction({ refundId }: { refundId: string }) {
    const [reasoning, setReasoning] = useState('');
    const [processing, setProcessing] = useState<'approved' | 'denied' | null>(null);
    const [error, setError] = useState<string | null>(null);

    async function resolve(decision: 'approved' | 'denied') {
        setProcessing(decision);
        setError(null);

        try {
            await api.patch(`/api/admin/refunds/${refundId}/resolve`, { decision, reasoning });
            router.reload();
        } catch (err) {
            setError(err instanceof ApiError ? err.message : 'Something went wrong. Please try again.');
            setProcessing(null);
        }
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-base">Manual review required</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
                <Textarea
                    value={reasoning}
                    onChange={(e) => setReasoning(e.target.value)}
                    placeholder="Explain the reason for your decision (kept internally, not shown to the customer verbatim)..."
                    rows={3}
                    minLength={5}
                    maxLength={2000}
                />

                {error && (
                    <Alert variant="destructive">
                        <AlertCircleIcon />
                        <AlertDescription>{error}</AlertDescription>
                    </Alert>
                )}

                <div className="flex gap-3">
                    <Button
                        type="button"
                        disabled={processing !== null || reasoning.trim().length < 5}
                        onClick={() => resolve('approved')}
                    >
                        {processing === 'approved' && <SpinnerIcon className="size-4 animate-spin" />}
                        Approve refund
                    </Button>
                    <Button
                        type="button"
                        variant="destructive"
                        disabled={processing !== null || reasoning.trim().length < 5}
                        onClick={() => resolve('denied')}
                    >
                        {processing === 'denied' && <SpinnerIcon className="size-4 animate-spin" />}
                        Deny refund
                    </Button>
                </div>
            </CardContent>
        </Card>
    );
}
