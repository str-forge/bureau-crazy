<?php

declare(strict_types=1);

namespace App\Enum;

enum DeadlineType: string
{
    case FINANCIAL_REPORT = 'financial_report'; // Verwendungsnachweis / Finanzbericht
    case PROGRESS_REPORT = 'progress_report';   // Sachbericht / Zwischenbericht
    case DISBURSEMENT_REQUEST = 'disbursement_request'; // Mittelabruf
    case INQUIRY_RESPONSE = 'inquiry_response'; // Rückmeldung an den Geldgeber
}