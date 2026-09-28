import { Head, Link, router } from '@inertiajs/react';
import { RefundFilters } from '@/components/admin/RefundFilters';
import { RefundTable } from '@/components/admin/RefundTable';
import { Button } from '@/components/ui/button';
import { ChevronLeftIcon, ChevronRightIcon } from '@/components/ui/icons';
import type { RefundRequestSummary } from '@/types';

type Props = {
    refunds: RefundRequestSummary[];
    meta: { current_page: number; last_page: number; total: number };
    filters: Record<string, string>;
};

export default function AdminRefundsIndex({ refunds, meta, filters }: Props) {
    function goToPage(page: number) {
        router.get('/admin/refunds', { ...filters, page }, { preserveState: true });
    }

    return (
        <>
            <Head title="Refund requests" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-lg font-semibold">Refund requests</h1>
                    <p className="text-sm text-muted-foreground">{meta.total} total</p>
                </div>

                <RefundFilters filters={filters} />

                <div className="rounded-xl border">
                    <RefundTable refunds={refunds} />
                </div>

                {meta.last_page > 1 && (
                    <div className="flex items-center justify-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            disabled={meta.current_page <= 1}
                            onClick={() => goToPage(meta.current_page - 1)}
                        >
                            <ChevronLeftIcon className="size-4" />
                            Previous
                        </Button>
                        <span className="text-sm text-muted-foreground">
                            Page {meta.current_page} of {meta.last_page}
                        </span>
                        <Button
                            variant="outline"
                            size="sm"
                            disabled={meta.current_page >= meta.last_page}
                            onClick={() => goToPage(meta.current_page + 1)}
                        >
                            Next
                            <ChevronRightIcon className="size-4" />
                        </Button>
                    </div>
                )}
            </div>
        </>
    );
}
