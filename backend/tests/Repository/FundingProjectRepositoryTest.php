<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\FundingProject;
use App\Entity\Deadline;
use App\Enum\DeadlineType;
use App\Repository\FundingProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FundingProjectRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private FundingProjectRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->repository = $container->get(FundingProjectRepository::class);

        $this->entityManager->createQuery('DELETE FROM App\Entity\Deadline')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\FundingProject')->execute();
    }

    public function testPersistAndRetrieveProject(): void
    {
        // Falls dein Konstruktor Argumente schluckt, ist das okay. 
        // Zur Sicherheit setzen wir die Werte über die Setter fest:
        $project = new FundingProject();
        $project->setTitle('Muster-NGO Digitalisierung');
        $project->setBudget(500000);

        $this->entityManager->persist($project);
        $this->entityManager->flush();
        
        $this->entityManager->clear();

        $savedProject = $this->repository->find($project->getId());
        
        $this->assertNotNull($savedProject);
        $this->assertSame('Muster-NGO Digitalisierung', $savedProject->getTitle());
        $this->assertSame(500000, $savedProject->getBudget());
    }

    public function testCascadeDeleteRemovesDeadlines(): void
    {
        $project = new FundingProject();
        $project->setTitle('Fördertopf Klima');
        $project->setBudget(1200000);

        // Instanziierung ohne Argumente, Befüllung via Setter
        $deadline = new Deadline();
        $deadline->setTitle('Zwischenbericht');
        $deadline->setDueDate(new \DateTimeImmutable('+1 month'));
        $deadline->setType(DeadlineType::PROGRESS_REPORT); // Hier dein Enum nutzen!
        
        $project->addDeadline($deadline);

        $this->entityManager->persist($project);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $loadedProject = $this->repository->find($project->getId());
        $this->entityManager->remove($loadedProject);
        $this->entityManager->flush();

        $deadlineCount = $this->entityManager->getRepository(Deadline::class)->count([]);
        $this->assertSame(0, $deadlineCount);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
