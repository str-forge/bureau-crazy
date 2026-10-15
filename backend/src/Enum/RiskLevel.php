<?php

declare(strict_types=1);

namespace App\Enum;

enum RiskLevel: string
{
    case HIGH = 'high';
    case MEDIUM = 'medium';
    case LOW = 'low';
}
