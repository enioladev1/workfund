<?php

namespace App\Enums;

/**
 * Not a DB enum: audit_logs.action is a plain string column because this set
 * of actions is expected to grow. This enum is just for type safety in code.
 */
enum AuditAction: string
{
    case RefundRequested = 'refund.requested';
    case OrderRetrieved = 'refund.order_retrieved';
    case PolicyEvaluated = 'refund.policy_evaluated';
    case AiAnalyzed = 'refund.ai_analyzed';
    case AiFailed = 'refund.ai_failed';
    case Approved = 'refund.approved';
    case Denied = 'refund.denied';
    case Escalated = 'refund.escalated';
    case ManuallyResolved = 'refund.manually_resolved';
    case IdempotentReplay = 'refund.idempotent_replay';
    case AdminViewedRefund = 'admin.viewed_refund';
}
