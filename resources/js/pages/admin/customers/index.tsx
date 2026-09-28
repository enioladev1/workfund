import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { CustomerTable } from '@/components/admin/CustomerTable';
import { Button } from '@/components/ui/button';
import { ChevronLeftIcon, ChevronRightIcon, FilterIcon } from '@/components/ui/icons';
import { Input } from '@/components/ui/input';
import type { AdminCustomer } from '@/types';

type Props = {
    customers: AdminCustomer[];
    meta: { current_page: number; last_page: number; total: number };
    filters: Record<string, string>;
};

export default function AdminCustomersIndex({ customers, meta, filters }: Props) {
    const [local, setLocal] = useState(filters);

    function apply() {
        router.get('/admin/customers', local, { preserveState: true });
    }

    function clear() {
        setLocal({});
        router.get('/admin/customers', {}, { preserveState: true });
    }

    function goToPage(page: number) {
        router.get('/admin/customers', { ...filters, page: String(page) }, { preserveState: true });
    }

    return (
        <>
            <Head title="Customers" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div>
                    <h1 className="text-lg font-semibold">Customers</h1>
                    <p className="text-sm text-muted-foreground">{meta.total} total</p>
                </div>

                <div className="flex flex-wrap items-end gap-3 rounded-lg border p-4">
                    <div className="grid gap-1.5">
                        <label className="text-xs text-muted-foreground">Name</label>
                        <Input
                            className="w-48"
                            value={local.name ?? ''}
                            onChange={(e) => setLocal((f) => ({ ...f, name: e.target.value }))}
                            placeholder="Search by name"
                        />
                    </div>
                    <div className="grid gap-1.5">
                        <label className="text-xs text-muted-foreground">Email</label>
                        <Input
                            className="w-48"
                            value={local.email ?? ''}
                            onChange={(e) => setLocal((f) => ({ ...f, email: e.target.value }))}
                            placeholder="Search by email"
                        />
                    </div>
                    <Button type="button" onClick={apply}>
                        <FilterIcon className="size-4" />
                        Apply filters
                    </Button>
                    <Button type="button" variant="outline" onClick={clear}>
                        Clear
                    </Button>
                </div>

                <div className="rounded-xl border">
                    <CustomerTable customers={customers} />
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
