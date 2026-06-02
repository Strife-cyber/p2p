<?php

namespace App\Enums;

enum SystemRole: string
{
    case Client = 'client';
    case Provider = 'provider';
    case Expert = 'expert';
    case Admin = 'admin';
}
