import { AIAnalysis } from '@/components/admin/AIAnalysis';
import { AuditTimeline } from '@/components/admin/AuditTimeline';
import { PolicyEvaluation } from '@/components/admin/PolicyEvaluation';
import { RefundResolveAction } from '@/components/admin/RefundResolveAction';
import { RefundStatusBadge } from '@/components/refunds/RefundStatusBadge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { AuditLogEntry, RefundRequestDetail } from '@/types';

export function RefundDetail({ refund, audit }: { refund: RefundRequestDetail; audit: AuditLogEntry[] }) {
    const latestDecision = refund.decisions[0] ?? null;

    return (
        <div className="grid gap-6 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle className="text-base">Customer request</CardTitle>
                </CardHeader>
                <CardContent className="space-y-2 text-sm">
                    <div className="flex justify-between">
                        <span className="text-muted-foreground">Customer</span>
                        <span>{refund.customer.name} ({refund.customer.email})</span>
                    </div>
                    <div className="flex justify-between">
                        <span className="text-muted-foreground">Reason</span>
                        <span className="capitalize">{refund.reason.replace(/_/g, ' ')}</span>
                    </div>
                    <div className="flex justify-between">
                        <span className="text-muted-foreground">Requested amount</span>
                        <span>
                            {refund.currency} {refund.requested_amount}
                        </span>
                    </div>
                    <div className="border-t pt-2">
                        <span className="text-muted-foreground">Message</span>
                        <p className="mt-1 rounded-md bg-muted p-3 text-sm">{refund.customer_message}</p>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle className="text-base">Order information</CardTitle>
                </CardHeader>
                <CardContent className="space-y-2 text-sm">
                    <div className="flex justify-between">
                        <span className="text-muted-foreground">Order number</span>
                        <span className="font-mono">{refund.order.order_number}</span>
                    </div>
                    <div className="flex justify-between">
                        <span className="text-muted-foreground">Order total</span>
                        <span>
                            {refund.order.currency} {refund.order.total}
                        </span>
                    </div>
                    <div className="flex justify-between">
                        <span className="text-muted-foreground">Final sale</span>
                        <span>{refund.order.is_final_sale ? 'Yes' : 'No'}</span>
                    </div>
                    <div className="flex justify-between">
                        <span className="text-muted-foreground">Ordered</span>
                        <span>{new Date(refund.order.ordered_at).toLocaleDateString()}</span>
                    </div>
                    {refund.order_item && (
                        <div className="border-t pt-2">
                            <span className="text-muted-foreground">Item</span>
                            <p className="mt-1">
                                {refund.order_item.name}
                                {refund.order_item.is_final_sale && ' (final sale)'}
                            </p>
                        </div>
                    )}
                </CardContent>
            </Card>

            {latestDecision && <PolicyEvaluation policyResult={latestDecision.policy_result} />}
            {latestDecision && <AIAnalysis aiResult={latestDecision.ai_result} interactions={refund.ai_interactions} />}

            <Card>
                <CardHeader>
                    <CardTitle className="text-base">Final decision</CardTitle>
                </CardHeader>
                <CardContent className="space-y-2 text-sm">
                    <RefundStatusBadge status={refund.status} />
                    {latestDecision && (
                        <>
                            <p className="text-muted-foreground">
                                Decided by {latestDecision.decided_by === 'staff' ? (latestDecision.decided_by_user?.name ?? 'staff') : 'automated pipeline'}
                            </p>
                            <p className="rounded-md bg-muted p-3">{latestDecision.reasoning}</p>
                        </>
                    )}
                </CardContent>
            </Card>

            {refund.status === 'escalated' && <RefundResolveAction refundId={refund.id} />}

            <div className="lg:col-span-2">
                <AuditTimeline entries={audit} />
            </div>
        </div>
    );
}
