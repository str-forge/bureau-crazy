<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\FundingProject;

class ICalGeneratorService implements ICalGeneratorInterface
{
    public function generateForProject(FundingProject $project): string
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//BureauCrazy//CalendarMux v1.0//DE',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH'
        ];
        foreach ($project->getDeadlines() as $deadline) {
            $dueDate = $deadline->getDueDate();
            $dtStamp = $dueDate->format('Ymd\THis');

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = sprintf('UID:%s@bureau-crazy.local', (string)$deadline->getId());
            $lines[] = sprintf('DTSTAMP:%s', $dtStamp);
            $lines[] = sprintf('DTSTART:%s', $dtStamp);
            $lines[] = sprintf('DTEND:%s', $dtStamp);
            $lines[] = sprintf('SUMMARY:%s - %s', $project->getTitle(), $deadline->getTitle());
            $lines[] = sprintf('DESCRIPTION:Fristen-Typ: %s', $deadline->getType()->value);
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines) . "\r\n";
    }
}
