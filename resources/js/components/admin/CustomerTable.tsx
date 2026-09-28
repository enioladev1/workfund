import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import type { AdminCustomer } from '@/types';

export function CustomerTable({ customers }: { customers: AdminCustomer[] }) {
    if (customers.length === 0) {
        return <p className="py-8 text-center text-sm text-muted-foreground">No customers match these filters.</p>;
    }

    return (
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>Phone</TableHead>
                    <TableHead>Orders</TableHead>
                    <TableHead>Refund requests</TableHead>
                    <TableHead>Joined</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {customers.map((customer) => (
                    <TableRow key={customer.id}>
                        <TableCell className="font-medium">{customer.name}</TableCell>
                        <TableCell>{customer.email}</TableCell>
                        <TableCell>{customer.phone ?? 'n/a'}</TableCell>
                        <TableCell>{customer.orders_count ?? 0}</TableCell>
                        <TableCell>{customer.refund_requests_count ?? 0}</TableCell>
                        <TableCell className="text-xs text-muted-foreground">
                            {new Date(customer.created_at).toLocaleDateString()}
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
