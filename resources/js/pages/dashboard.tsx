import { Head, Link } from '@inertiajs/react';
import { RefundStats } from '@/components/admin/RefundStats';
import { RefundTable } from '@/components/admin/RefundTable';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import type { RefundRequestSummary } from '@/types';

type Props = {
    stats: {
        total: number;
        approved: number;
        denied: number;
        escalated: number;
        pending: number;
    };
    recent: RefundRequestSummary[];
};

export default function Dashboard({ stats, recent }: Props) {
    return (
        <>
            <Head title="Dashboard" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <RefundStats stats={stats} />

                <div className="rounded-xl border">
                    <div className="flex items-center justify-between border-b p-4">
                        <h2 className="font-medium">Recent refund requests</h2>
                        <Button asChild variant="outline" size="sm">
                            <Link href="/admin/refunds">View all</Link>
                        </Button>
                    </div>
                    <div className="p-4">
                        <RefundTable refunds={recent} />
                    </div>
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
};
