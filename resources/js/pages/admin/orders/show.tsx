import { Head, Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ChevronLeftIcon } from '@/components/ui/icons';
import type { AdminOrderDetail } from '@/types';

export default function AdminOrderShow({ order }: { order: AdminOrderDetail }) {
    return (
        <>
            <Head title={`Order ${order.order_number}`} />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <Button asChild variant="ghost" size="sm" className="w-fit">
                    <Link href="/admin/orders">
                        <ChevronLeftIcon className="size-4" />
                        Back to orders
                    </Link>
                </Button>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-base">
                                Order {order.order_number}
                                {order.is_final_sale && <Badge variant="outline">Final sale</Badge>}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-2 text-sm">
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Status</span>
                                <span className="capitalize">{order.status.replace(/_/g, ' ')}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Total</span>
                                <span>
                                    {order.currency} {order.total}
                                </span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Refunded</span>
                                <span>
                                    {order.currency} {order.refunded_amount}
                                </span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Remaining refundable</span>
                                <span>
                                    {order.currency} {order.remaining_refundable}
                                </span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Ordered</span>
                                <span>{new Date(order.ordered_at).toLocaleDateString()}</span>
                            </div>
                            {order.delivered_at && (
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Delivered</span>
                                    <span>{new Date(order.delivered_at).toLocaleDateString()}</span>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Customer</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-2 text-sm">
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Name</span>
                                <span>{order.customer.name}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Email</span>
                                <span>{order.customer.email}</span>
                            </div>
                            {order.customer.phone && (
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Phone</span>
                                    <span>{order.customer.phone}</span>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <div className="lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-base">Items</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Name</TableHead>
                                            <TableHead>SKU</TableHead>
                                            <TableHead>Qty</TableHead>
                                            <TableHead>Unit price</TableHead>
                                            <TableHead>Total</TableHead>
                                            <TableHead>Final sale</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {order.items.map((item) => (
                                            <TableRow key={item.id}>
                                                <TableCell>{item.name}</TableCell>
                                                <TableCell className="font-mono text-xs">{item.sku}</TableCell>
                                                <TableCell>{item.quantity}</TableCell>
                                                <TableCell>
                                                    {order.currency} {item.unit_price}
                                                </TableCell>
                                                <TableCell>
                                                    {order.currency} {item.total_price}
                                                </TableCell>
                                                <TableCell>{item.is_final_sale ? 'Yes' : 'No'}</TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}
