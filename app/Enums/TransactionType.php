<?php

namespace App\Enums;

enum TransactionType: string
{
    case Deposit = 'deposit';
    case Withdrawal = 'withdrawal';
    case MissionPayment = 'mission_payment';
    case Refund = 'refund';
    case PlatformCommission = 'platform_commission';
}
