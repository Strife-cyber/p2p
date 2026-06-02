<?php

namespace App\Enums;

enum AnomalyType: string
{
    case LatePresence = 'late_presence';
    case Shunting = 'shunting';
    case QualityDispute = 'quality_dispute';
}
