<?php

declare(strict_types=1);

namespace App\Enum;

enum DeadlineType: string
{
    case FINANCIAL_REPORT = 'financial_report'; // Financial report / expenditure statement
    case PROGRESS_REPORT = 'progress_report';   // Progress report / interim report
    case DISBURSEMENT_REQUEST = 'disbursement_request'; // Disbursement request
    case INQUIRY_RESPONSE = 'inquiry_response'; // Response to the funding provider
}
