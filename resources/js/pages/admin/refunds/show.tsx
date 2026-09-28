import { Head, Link } from '@inertiajs/react';
import { RefundDetail } from '@/components/admin/RefundDetail';
import { Button } from '@/components/ui/button';
import { ChevronLeftIcon } from '@/components/ui/icons';
import type { AuditLogEntry, RefundRequestDetail } from '@/types';

type Props = {
    refund: RefundRequestDetail;
    audit: AuditLogEntry[];
};

export default function AdminRefundShow({ refund, audit }: Props) {
    return (
        <>
            <Head title={`Refund ${refund.order.order_number}`} />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <Button asChild variant="ghost" size="sm" className="w-fit">
                    <Link href="/admin/refunds">
                        <ChevronLeftIcon className="size-4" />
                        Back to refund requests
                    </Link>
                </Button>

                <RefundDetail refund={refund} audit={audit} />
            </div>
        </>
    );
}
