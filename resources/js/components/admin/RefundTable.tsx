import { Link } from '@inertiajs/react';
import { RefundStatusBadge } from '@/components/refunds/RefundStatusBadge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import type { RefundRequestSummary } from '@/types';

export function RefundTable({ refunds }: { refunds: RefundRequestSummary[] }) {
    if (refunds.length === 0) {
        return <p className="py-8 text-center text-sm text-muted-foreground">No refund requests match these filters.</p>;
    }

    return (
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Customer</TableHead>
                    <TableHead>Order</TableHead>
                    <TableHead>Reason</TableHead>
                    <TableHead>Amount</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Submitted</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {refunds.map((refund) => (
                    <TableRow key={refund.id}>
                        <TableCell>
                            <Link href={`/admin/refunds/${refund.id}`} className="hover:underline">
                                <div className="font-medium">{refund.customer.name}</div>
                                <div className="text-xs text-muted-foreground">{refund.customer.email}</div>
                            </Link>
                        </TableCell>
                        <TableCell className="font-mono text-xs">{refund.order.order_number}</TableCell>
                        <TableCell className="capitalize">{refund.reason.replace(/_/g, ' ')}</TableCell>
                        <TableCell>
                            {refund.currency} {refund.requested_amount}
                        </TableCell>
                        <TableCell>
                            <RefundStatusBadge status={refund.status} />
                        </TableCell>
                        <TableCell className="text-xs text-muted-foreground">
                            {new Date(refund.created_at).toLocaleString()}
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
