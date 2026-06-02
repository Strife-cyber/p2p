<?php

namespace App\Enums;

enum GovernanceActionType: string
{
    case ManualBadgeUpdate = 'manual_badge_update';
    case ExceptionalReaudit = 'exceptional_reaudit';
    case SystemParameterMutation = 'system_parameter_mutation';
    case PartyExclusion = 'party_exclusion';
}
