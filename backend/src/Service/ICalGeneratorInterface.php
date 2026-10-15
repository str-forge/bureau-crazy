<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\FundingProject;

interface ICalGeneratorInterface
{
    /**
     * Generate a standardized RFC 5545 iCalendar string
     * from the deadlines of a funded project.
     */
    public function generateForProject(FundingProject $project): string;
}
