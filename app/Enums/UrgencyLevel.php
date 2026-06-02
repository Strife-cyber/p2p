<?php

namespace App\Enums;

enum UrgencyLevel: string
{
    case Normal = 'normal';
    case High = 'high';
    case Critical = 'critical';
}
