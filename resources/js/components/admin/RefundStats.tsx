import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AlertDiamondIcon, CheckmarkCircleIcon, ClockIcon, XIcon } from '@/components/ui/icons';

type Stats = {
    total: number;
    approved: number;
    denied: number;
    escalated: number;
    pending: number;
};

const CARDS = [
    { key: 'total' as const, label: 'Total requests', icon: ClockIcon, tone: 'text-foreground' },
    { key: 'approved' as const, label: 'Approved', icon: CheckmarkCircleIcon, tone: 'text-emerald-600 dark:text-emerald-400' },
    { key: 'denied' as const, label: 'Denied', icon: XIcon, tone: 'text-red-600 dark:text-red-400' },
    { key: 'escalated' as const, label: 'Escalated', icon: AlertDiamondIcon, tone: 'text-amber-600 dark:text-amber-400' },
];

export function RefundStats({ stats }: { stats: Stats }) {
    return (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {CARDS.map(({ key, label, icon: Icon, tone }) => (
                <Card key={key}>
                    <CardHeader className="flex flex-row items-center justify-between pb-2">
                        <CardTitle className="text-sm font-medium text-muted-foreground">{label}</CardTitle>
                        <Icon className={`size-4 ${tone}`} />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-semibold">{stats[key]}</div>
                    </CardContent>
                </Card>
            ))}
        </div>
    );
}
