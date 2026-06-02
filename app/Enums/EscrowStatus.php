<?php

namespace App\Enums;

enum EscrowStatus: string
{
    case Blocked = 'blocked';
    case SeventyPercentReleased = 'seventy_percent_released';
    case ThirtyPercentReleased = 'thirty_percent_released';
    case Refunded = 'refunded';
}
