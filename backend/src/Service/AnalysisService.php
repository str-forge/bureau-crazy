<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\FundingProject;
use App\Entity\Deadline;
use App\Enum\DeadlineType;
use App\Enum\RiskLevel;
use Doctrine\ORM\EntityManagerInterface;

class AnalysisService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DeepSeekService $deepSeekService
    ) {
    }

    public function analyzeAndApply(FundingProject $project, string $text): void
    {
        $aiData = $this->deepSeekService->extractDeadlines($text);

        foreach ($aiData['deadlines'] ?? [] as $extracted) {
            $deadline = new Deadline();
            $deadline->setTitle($extracted['title'] ?? 'Unbenannte Frist');
            $deadline->setDueDate(new \DateTimeImmutable($extracted['date'] ?? 'now'));

            // Fall back to PROGRESS_REPORT if the AI returns an unrecognized string
            $type = DeadlineType::tryFrom($extracted['type'] ?? '') ?? DeadlineType::PROGRESS_REPORT;
            $deadline->setType($type);

            // Assign the risk level
            $riskStr = $extracted['risk_level'] ?? 'low';
            $risk = RiskLevel::tryFrom($riskStr) ?? RiskLevel::LOW;
            $deadline->setRiskLevel($risk);

            $project->addDeadline($deadline);
            $this->entityManager->persist($deadline);
        }

        $this->entityManager->flush();
    }
}
