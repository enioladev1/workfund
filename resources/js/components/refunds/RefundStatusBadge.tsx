import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import type { RefundStatusValue } from '@/types';

const STYLES: Record<RefundStatusValue, string> = {
    approved: 'border-emerald-600/20 bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400',
    denied: 'border-red-600/20 bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-400',
    escalated: 'border-amber-600/20 bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-400',
    pending: 'border-neutral-400/20 bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300',
};

const LABELS: Record<RefundStatusValue, string> = {
    approved: 'Approved',
    denied: 'Denied',
    escalated: 'Escalated',
    pending: 'Pending',
};

export function RefundStatusBadge({ status, className }: { status: RefundStatusValue; className?: string }) {
    return (
        <Badge variant="outline" className={cn(STYLES[status], className)}>
            {LABELS[status]}
        </Badge>
    );
}
