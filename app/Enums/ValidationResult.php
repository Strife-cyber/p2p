<?php

namespace App\Enums;

enum ValidationResult: string
{
    case AutoValidated = 'auto_validated';
    case ExpertValidated = 'expert_validated';
    case Rejected = 'rejected';
}
