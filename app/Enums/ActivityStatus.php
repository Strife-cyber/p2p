<?php

namespace App\Enums;

enum ActivityStatus: string
{
    case Available = 'available';
    case OnMission = 'on_mission';
    case Blocked = 'blocked';
}
