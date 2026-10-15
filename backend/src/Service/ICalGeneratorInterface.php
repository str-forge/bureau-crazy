<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\FundingProject;

interface ICalGeneratorInterface
{
    /**
     * Generiert einen standardisierten RFC 5545 iCalendar-String
     * aus den Fristen eines Förderprojekts.
     */
    public function generateForProject(FundingProject $project): string;
}
