<?php

namespace App\Enums;

enum GovernanceActionStatus: string
{
    case PendingSignatures = 'pending_signatures';
    case ApprovedExecuted = 'approved_executed';
    case RejectedExpired = 'rejected_expired';
}
