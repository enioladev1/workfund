import { router } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { FilterIcon } from '@/components/ui/icons';
import { REFUND_REASONS } from '@/types';

type Filters = {
    status?: string;
    reason?: string;
    customer_email?: string;
    order_number?: string;
};

const STATUSES = ['pending', 'approved', 'denied', 'escalated'];

export function RefundFilters({ filters }: { filters: Filters }) {
    const [local, setLocal] = useState<Filters>(filters);

    function apply() {
        router.get('/admin/refunds', local as Record<string, string>, { preserveState: true });
    }

    function clear() {
        setLocal({});
        router.get('/admin/refunds', {}, { preserveState: true });
    }

    return (
        <div className="flex flex-wrap items-end gap-3 rounded-lg border p-4">
            <div className="grid gap-1.5">
                <label className="text-xs text-muted-foreground">Status</label>
                <Select
                    value={local.status ?? 'all'}
                    onValueChange={(v) => setLocal((f) => ({ ...f, status: v === 'all' ? undefined : v }))}
                >
                    <SelectTrigger className="w-36">
                        <SelectValue placeholder="All" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All</SelectItem>
                        {STATUSES.map((s) => (
                            <SelectItem key={s} value={s} className="capitalize">
                                {s}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>

            <div className="grid gap-1.5">
                <label className="text-xs text-muted-foreground">Reason</label>
                <Select
                    value={local.reason ?? 'all'}
                    onValueChange={(v) => setLocal((f) => ({ ...f, reason: v === 'all' ? undefined : v }))}
                >
                    <SelectTrigger className="w-44">
                        <SelectValue placeholder="All" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All</SelectItem>
                        {REFUND_REASONS.map((r) => (
                            <SelectItem key={r.value} value={r.value}>
                                {r.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>

            <div className="grid gap-1.5">
                <label className="text-xs text-muted-foreground">Customer email</label>
                <Input
                    className="w-48"
                    value={local.customer_email ?? ''}
                    onChange={(e) => setLocal((f) => ({ ...f, customer_email: e.target.value || undefined }))}
                    placeholder="customer@example.com"
                />
            </div>

            <div className="grid gap-1.5">
                <label className="text-xs text-muted-foreground">Order number</label>
                <Input
                    className="w-36"
                    value={local.order_number ?? ''}
                    onChange={(e) => setLocal((f) => ({ ...f, order_number: e.target.value || undefined }))}
                    placeholder="WF-100001"
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
    );
}
