import { Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import type { Order } from '@/types';

export function OrderTable({ orders }: { orders: Order[] }) {
    if (orders.length === 0) {
        return <p className="py-8 text-center text-sm text-muted-foreground">No orders match these filters.</p>;
    }

    return (
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Order</TableHead>
                    <TableHead>Customer</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Items</TableHead>
                    <TableHead>Total</TableHead>
                    <TableHead>Ordered</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {orders.map((order) => (
                    <TableRow key={order.id}>
                        <TableCell>
                            <Link href={`/admin/orders/${order.id}`} className="font-mono text-sm hover:underline">
                                {order.order_number}
                            </Link>
                            {order.is_final_sale && (
                                <Badge variant="outline" className="ml-2">
                                    Final sale
                                </Badge>
                            )}
                        </TableCell>
                        <TableCell>
                            {order.customer ? (
                                <>
                                    <div className="font-medium">{order.customer.name}</div>
                                    <div className="text-xs text-muted-foreground">{order.customer.email}</div>
                                </>
                            ) : (
                                <span className="text-muted-foreground">n/a</span>
                            )}
                        </TableCell>
                        <TableCell className="capitalize">{order.status.replace(/_/g, ' ')}</TableCell>
                        <TableCell>{order.items_count ?? order.items.length}</TableCell>
                        <TableCell>
                            {order.currency} {order.total}
                        </TableCell>
                        <TableCell className="text-xs text-muted-foreground">
                            {new Date(order.ordered_at).toLocaleDateString()}
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
