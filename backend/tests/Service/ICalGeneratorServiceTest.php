<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\FundingProject;
use App\Entity\Deadline;
use App\Enum\DeadlineType;
use App\Service\ICalGeneratorService;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class ICalGeneratorServiceTest extends TestCase
{
    public function testGenerateForProjectReturnsValidRfc5545Structure(): void
    {
        $project = new FundingProject();
        $project->setTitle('iCal Test-Projekt');

        $deadline = new Deadline();
        $deadline->setTitle('Kritischer Zwischenbericht');
        $deadline->setDueDate(new DateTimeImmutable('2026-12-31 23:59:59'));
        $deadline->setType(DeadlineType::PROGRESS_REPORT);

        $project->addDeadline($deadline);

        $generator = new ICalGeneratorService();
        $icsContent = $generator->generateForProject($project);

        $this->assertStringContainsString('BEGIN:VCALENDAR', $icsContent);
        $this->assertStringContainsString('VERSION:2.0', $icsContent);
        $this->assertStringContainsString('SUMMARY:iCal Test-Projekt - Kritischer Zwischenbericht', $icsContent);
        $this->assertStringContainsString('DTSTART:20261231T235959', $icsContent);
        $this->assertStringContainsString('END:VCALENDAR', $icsContent);
    }
}
