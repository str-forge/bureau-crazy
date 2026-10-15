<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Deadline;
use App\Entity\FundingProject;
use App\Enum\DeadlineType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DeadlineRepositoryTest extends KernelTestCase
{
    // Declare the property as a nullable interface
    private ?EntityManagerInterface $entityManager = null;

    protected function setUp(): void
    {
        // Boot the Symfony kernel first to access dependency injection
        self::bootKernel();

        $container = self::getContainer();

        // RFC fix: assign directly to the class property
        $this->entityManager = $container->get(EntityManagerInterface::class);
    }

    public function testPersistAndRetrieveDeadlineWithEnumAndProject(): void
    {
        // 1. Arrange: create a valid project with the required fields
        $project = new FundingProject();

        // Instantiate and configure the deadline under test
        $deadline = new Deadline();
        $deadline->setTitle('Zweiter Zwischennachweis')
         ->setDueDate(new \DateTimeImmutable('+60 days'))
         ->setType(DeadlineType::PROGRESS_REPORT)
         ->setFundingProject($project);

        // 2. Act: write data to the actual PostgreSQL database
        $this->entityManager->persist($project);
        $this->entityManager->persist($deadline);
        $this->entityManager->flush();

        // Save the ID for the subsequent fresh retrieval
        $deadlineId = $deadline->getId();

        // Clear the EntityManager completely. This forces Doctrine to load the
        // entity from PostgreSQL with a fresh SQL 'SELECT' instead of the RAM cache.
        $this->entityManager->clear();

        // 3. Assert: read from the database and verify data consistency
        /** @var Deadline|null $retrievedDeadline */
        $retrievedDeadline = $this->entityManager->getRepository(Deadline::class)->find($deadlineId);

        // Thoroughly validate the result
        $this->assertNotNull($retrievedDeadline, 'Die Deadline wurde nicht in der Datenbank gefunden.');
        $this->assertSame('Zweiter Zwischennachweis', $retrievedDeadline->getTitle());
        $this->assertSame(DeadlineType::PROGRESS_REPORT, $retrievedDeadline->getType());
        $this->assertEquals($deadlineId, $retrievedDeadline->getId());

        // Verify that the project relationship exists at the database level
        $this->assertNotNull($retrievedDeadline->getFundingProject());
        $this->assertEquals($project->getId(), $retrievedDeadline->getFundingProject()->getId());
    }
}
