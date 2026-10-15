<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\FundingProject;
use App\Entity\Deadline;
use App\Enum\DeadlineType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DeadlineTest extends KernelTestCase
{
    public function testCanCreateDeadlineAndAssociateWithProject(): void
    {
        // Arrange
        $project = new FundingProject();
        $deadline = new Deadline();
        $targetDate = new \DateTimeImmutable('+30 days');

        // Act
        $deadline->setTitle('Finaler Verwendungsnachweis 2026')
            ->setDueDate($targetDate)
            ->setType(DeadlineType::FINANCIAL_REPORT)
            ->setFundingProject($project);

        // Assert
        $this->assertInstanceOf(Deadline::class, $deadline);
        $this->assertSame($project, $deadline->getFundingProject());
        $this->assertSame(DeadlineType::FINANCIAL_REPORT, $deadline->getType());
        $this->assertEquals($targetDate, $deadline->getDueDate());
        $this->assertNotNull($deadline->getId(), 'UUIDv7 sollte im Konstruktor generiert werden.');
    }

    public function testDeadlineValidationFailsWhenDueDateIsInThePast(): void
    {
        // 1. Arrange: boot the Symfony kernel statically
        self::bootKernel();

        // Access the service container statically through self::
        $validator = self::getContainer()->get('validator');

        $project = new FundingProject();
        $deadline = new Deadline();

        // A date in the past (yesterday)
        $pastDate = new \DateTimeImmutable('-1 day');

        $deadline->setTitle('Abgabe Zwischenbericht')
            ->setDueDate($pastDate)
            ->setType(\App\Enum\DeadlineType::PROGRESS_REPORT)
            ->setFundingProject($project);

        // 2. Act: run validation
        $errors = $validator->validate($deadline);

        // 3. Assert: expect exactly one validation error
        $this->assertCount(1, $errors, 'Der Validator hätte das Datum in der Vergangenheit blockieren müssen.');

        // Verify that the error message matches the expected text
        $this->assertSame(
            'Das Frist-Datum darf nicht in der Vergangenheit liegen.',
            $errors->get(0)->getMessage()
        );
    }
}
