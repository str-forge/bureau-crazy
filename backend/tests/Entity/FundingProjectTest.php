<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\FundingProject;
use PHPUnit\Framework\TestCase;

class FundingProjectTest extends TestCase
{
    public function testCanInstantiateFundingProjectWithCoreFields(): void
    {
        // Since the class does not exist yet, this MUST fail in the TDD red phase!
        $project = new FundingProject();

        $project->setTitle('Digitalisierung NGO');
        $project->setBudget(250000); // 2500,00 € in Cent

        $this->assertSame('Digitalisierung NGO', $project->getTitle());
        $this->assertSame(250000, $project->getBudget());
    }
}
