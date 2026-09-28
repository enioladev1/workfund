<?php

namespace App\Enums;

enum RefundStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Denied = 'denied';
    case Escalated = 'escalated';
}
