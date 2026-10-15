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
    // Hier deklarieren wir die Property sauber als Nullable-Interface
    private ?EntityManagerInterface $entityManager = null;

    protected function setUp(): void
    {
        // Erst den Symfony-Kernel booten, um Zugriff auf Dependency Injection zu haben
        self::bootKernel();

        $container = self::getContainer();

        // RFC-Fix: Zuweisung direkt auf die Klassen-Eigenschaft!
        $this->entityManager = $container->get(EntityManagerInterface::class);
    }

    public function testPersistAndRetrieveDeadlineWithEnumAndProject(): void
    {
        // 1. Arrange: Valides Projekt mit den Pflichtfeldern von Tag 1 erstellen
        $project = new FundingProject();

        // Die zu testende Deadline instanziieren und konfigurieren
        $deadline = new Deadline();
        $deadline->setTitle('Zweiter Zwischennachweis')
         ->setDueDate(new \DateTimeImmutable('+60 days'))
         ->setType(DeadlineType::PROGRESS_REPORT)
         ->setFundingProject($project);

        // 2. Act: Daten in die echte PostgreSQL-Datenbank schreiben
        $this->entityManager->persist($project);
        $this->entityManager->persist($deadline);
        $this->entityManager->flush();

        // Wir merken uns die ID für den anschließenden, frischen Abruf
        $deadlineId = $deadline->getId();

        // EntityManager komplett leeren. Das zwingt Doctrine dazu, die Entity
        // via SQL 'SELECT' frisch aus PostgreSQL zu laden, statt aus dem RAM-Cache.
        $this->entityManager->clear();

        // 3. Assert: Auslesen aus der DB und Datenkonsistenz verifizieren
        /** @var Deadline|null $retrievedDeadline */
        $retrievedDeadline = $this->entityManager->getRepository(Deadline::class)->find($deadlineId);

        // Überprüfungen auf Herz und Nieren
        $this->assertNotNull($retrievedDeadline, 'Die Deadline wurde nicht in der Datenbank gefunden.');
        $this->assertSame('Zweiter Zwischennachweis', $retrievedDeadline->getTitle());
        $this->assertSame(DeadlineType::PROGRESS_REPORT, $retrievedDeadline->getType());
        $this->assertEquals($deadlineId, $retrievedDeadline->getId());

        // Prüfen, ob die Verknüpfung zum Projekt auf DB-Ebene steht
        $this->assertNotNull($retrievedDeadline->getFundingProject());
        $this->assertEquals($project->getId(), $retrievedDeadline->getFundingProject()->getId());
    }
}
