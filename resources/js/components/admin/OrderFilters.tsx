import { router } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { FilterIcon } from '@/components/ui/icons';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

type Filters = {
    customer_email?: string;
    order_number?: string;
    status?: string;
};

const STATUSES = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded', 'partially_refunded'];

export function OrderFilters({ filters }: { filters: Filters }) {
    const [local, setLocal] = useState<Filters>(filters);

    function apply() {
        router.get('/admin/orders', local as Record<string, string>, { preserveState: true });
    }

    function clear() {
        setLocal({});
        router.get('/admin/orders', {}, { preserveState: true });
    }

    return (
        <div className="flex flex-wrap items-end gap-3 rounded-lg border p-4">
            <div className="grid gap-1.5">
                <label className="text-xs text-muted-foreground">Status</label>
                <Select
                    value={local.status ?? 'all'}
                    onValueChange={(v) => setLocal((f) => ({ ...f, status: v === 'all' ? undefined : v }))}
                >
                    <SelectTrigger className="w-40">
                        <SelectValue placeholder="All" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All</SelectItem>
                        {STATUSES.map((s) => (
                            <SelectItem key={s} value={s} className="capitalize">
                                {s.replace(/_/g, ' ')}
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
