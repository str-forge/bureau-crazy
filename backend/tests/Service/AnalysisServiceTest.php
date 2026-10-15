<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\FundingProject;
use App\Enum\DeadlineType;
use App\Service\AnalysisService;
use App\Service\DeepSeekService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AnalysisServiceTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()
            ->get(EntityManagerInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    public function testAnalyzeAndMapSuccessfully(): void
    {
        $project = new FundingProject();
        $project->setTitle('Test Projekt');
        $project->setBudget(100000);
        $this->entityManager->persist($project);
        $this->entityManager->flush();

        $aiResponse = [
            'deadlines' => [[
                'title' => 'Zwischennachweis 2026',
                'date' => '2026-12-31',
                'type' => 'progress_report',
                'risk_level' => 'high' // New
            ]]
        ];

        $deepSeekStub = $this->createStub(DeepSeekService::class);
        $deepSeekStub->method('extractDeadlines')->willReturn($aiResponse);
        $service = new AnalysisService($this->entityManager, $deepSeekStub);
        $service->analyzeAndApply($project, 'Text...');
        $this->entityManager->clear();

        $refreshed = $this->entityManager
            ->getRepository(FundingProject::class)
            ->find($project->getId());

        $this->assertCount(1, $refreshed->getDeadlines());
        $deadline = $refreshed->getDeadlines()->first();
        $this->assertSame('Zwischennachweis 2026', $deadline->getTitle());
        $this->assertSame(DeadlineType::PROGRESS_REPORT, $deadline->getType());
        $this->assertSame(\App\Enum\RiskLevel::HIGH, $deadline->getRiskLevel());
    }
}
