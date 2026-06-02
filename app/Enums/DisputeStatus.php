<?php

namespace App\Enums;

enum DisputeStatus: string
{
    case Open = 'open';
    case InArbitration = 'in_arbitration';
    case Resolved = 'resolved';
    case Cancelled = 'cancelled';
}
