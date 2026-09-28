<?php

namespace App\Enums;

enum DecisionType: string
{
    case Approved = 'approved';
    case Denied = 'denied';
    case Escalated = 'escalated';

    public function toRefundStatus(): RefundStatus
    {
        return RefundStatus::from($this->value);
    }
}
