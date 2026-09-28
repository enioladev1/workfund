import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { AuditLogEntry } from '@/types';

const ACTION_LABELS: Record<string, string> = {
    'refund.requested': 'Refund request created',
    'refund.order_retrieved': 'Order information retrieved',
    'refund.policy_evaluated': 'Refund policy evaluated',
    'refund.ai_analyzed': 'AI analysis completed',
    'refund.approved': 'Decision: Approved',
    'refund.denied': 'Decision: Denied',
    'refund.escalated': 'Decision: Escalated',
    'refund.manually_resolved': 'Manually resolved by staff',
    'refund.idempotent_replay': 'Duplicate submission detected (idempotent replay)',
    'admin.viewed_refund': 'Viewed by support staff',
};

export function AuditTimeline({ entries }: { entries: AuditLogEntry[] }) {
    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-base">Audit history</CardTitle>
            </CardHeader>
            <CardContent>
                <ol className="space-y-4">
                    {entries.map((entry) => (
                        <li key={entry.id} className="flex gap-3 text-sm">
                            <span className="w-20 shrink-0 font-mono text-xs text-muted-foreground">
                                {new Date(entry.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })}
                            </span>
                            <span>{ACTION_LABELS[entry.action] ?? entry.action}</span>
                        </li>
                    ))}
                </ol>
            </CardContent>
        </Card>
    );
}
