<?php

namespace App\Enums;

enum LifecycleStatus: string
{
    case Published = 'published';
    case Assigned = 'assigned';
    case CheckInInProgress = 'check_in_in_progress';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case UnderWarranty = 'under_warranty';
    case Closed = 'closed';
    case InDispute = 'in_dispute';
}
