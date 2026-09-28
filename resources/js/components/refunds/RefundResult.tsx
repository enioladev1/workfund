import { RefundStatusBadge } from '@/components/refunds/RefundStatusBadge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { CheckmarkCircleIcon, XIcon, ClockIcon } from '@/components/ui/icons';
import type { RefundStatusPublic } from '@/types';

const ICONS = {
    approved: CheckmarkCircleIcon,
    denied: XIcon,
    escalated: ClockIcon,
    pending: ClockIcon,
};

const TITLES = {
    approved: 'Refund approved',
    denied: 'Refund request denied',
    escalated: 'Your request has been sent for review',
    pending: 'Your request is being processed',
};

export function RefundResult({ result, onStartOver }: { result: RefundStatusPublic; onStartOver: () => void }) {
    const Icon = ICONS[result.status];

    return (
        <Card>
            <CardHeader>
                <div className="flex items-center gap-3">
                    <Icon className="size-6" />
                    <CardTitle className="text-xl">{TITLES[result.status]}</CardTitle>
                </div>
            </CardHeader>
            <CardContent className="space-y-4">
                <RefundStatusBadge status={result.status} />

                <p className="text-sm leading-relaxed text-muted-foreground">{result.message}</p>

                <dl className="grid grid-cols-2 gap-2 rounded-lg border p-4 text-sm">
                    <dt className="text-muted-foreground">Requested amount</dt>
                    <dd className="text-right font-medium">
                        {result.currency} {result.requested_amount}
                    </dd>
                    <dt className="text-muted-foreground">Reference</dt>
                    <dd className="text-right font-mono text-xs">{result.id}</dd>
                </dl>

                <Button variant="outline" onClick={onStartOver} className="w-full">
                    Submit another request
                </Button>
            </CardContent>
        </Card>
    );
}
