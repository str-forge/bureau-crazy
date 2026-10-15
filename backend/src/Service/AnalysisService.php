<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\FundingProject;
use App\Entity\Deadline;
use App\Enum\DeadlineType;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

class AnalysisService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DeepSeekService $deepSeekService
    ) {
    }

    public function analyzeAndApply(FundingProject $project, string $text): void
    {
        $data = $this->deepSeekService->extractDeadlines($text);

        if (!isset($data['deadlines']) || !is_array($data['deadlines'])) {
            return;
        }

        foreach ($data['deadlines'] as $deadlineData) {
            $deadline = new Deadline();
            $deadline->setTitle($deadlineData['title']);
            $deadline->setDueDate(new DateTimeImmutable($deadlineData['date']));
            $deadline->setType(DeadlineType::from($deadlineData['type']));

            $project->addDeadline($deadline);
            $this->entityManager->persist($deadline);
        }

        $this->entityManager->flush();
    }
}
