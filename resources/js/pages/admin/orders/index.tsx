import { Head, Link, router } from '@inertiajs/react';
import { OrderFilters } from '@/components/admin/OrderFilters';
import { OrderTable } from '@/components/admin/OrderTable';
import { Button } from '@/components/ui/button';
import { ChevronLeftIcon, ChevronRightIcon, PlusIcon } from '@/components/ui/icons';
import type { Order } from '@/types';

type Props = {
    orders: Order[];
    meta: { current_page: number; last_page: number; total: number };
    filters: Record<string, string>;
};

export default function AdminOrdersIndex({ orders, meta, filters }: Props) {
    function goToPage(page: number) {
        router.get('/admin/orders', { ...filters, page }, { preserveState: true });
    }

    return (
        <>
            <Head title="Orders" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-lg font-semibold">Orders</h1>
                        <p className="text-sm text-muted-foreground">{meta.total} total</p>
                    </div>
                    <Button asChild>
                        <Link href="/admin/orders/create">
                            <PlusIcon className="size-4" />
                            New order
                        </Link>
                    </Button>
                </div>

                <OrderFilters filters={filters} />

                <div className="rounded-xl border">
                    <OrderTable orders={orders} />
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
