<?php

declare(strict_types=1);

namespace App\Controller\Api\v1;

use App\Entity\FundingProject;
use App\Repository\FundingProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/projects')]
class FundingProjectController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }
    #[Route('', name: 'api_v1_project_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $content = json_decode($request->getContent(), true);

        if (!$content || empty($content['title'])) {
            return new JsonResponse(
                ['error' => 'Ungültige Daten oder fehlender Titel.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $project = new FundingProject();
        $project->setTitle($content['title']);

        // Typsicherer Direktaufruf ohne redundante Weichen
        $budget = isset($content['budget_in_cents']) ? (int)$content['budget_in_cents'] : 0;
        $project->setBudget($budget);

        $this->entityManager->persist($project);
        $this->entityManager->flush();

        // Universal, robust string casting of the UUID
        $idString = (string) $project->getId();

        $responseData = [
            'id' => $idString,
            'title' => $project->getTitle(),
            'budget_in_cents' => $budget,
            'deadlines' => []
        ];

        return new JsonResponse($responseData, Response::HTTP_CREATED);
    }
    #[Route('/{id}', name: 'api_v1_project_show', methods: ['GET'])]
    public function show(FundingProject $project): JsonResponse
    {
        $deadlinesData = [];
        foreach ($project->getDeadlines() as $deadline) {
            $deadlinesData[] = [
                'id' => (string) $deadline->getId(),
                'title' => $deadline->getTitle(),
                'due_date' => $deadline->getDueDate()->format(\DateTimeInterface::ATOM),
                'type' => $deadline->getType()->value,
                'risk_level' => $deadline->getRiskLevel()->value
            ];
        }

        $data = [
            'id' => (string) $project->getId(),
            'title' => $project->getTitle(),
            'budget_in_cents' => $project->getBudget(),
            'deadlines' => $deadlinesData
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }
    #[Route('', name: 'api_v1_project_list', methods: ['GET'])]
    public function list(FundingProjectRepository $repository): JsonResponse
    {
        $projects = $repository->findAll();
        $data = [];

        foreach ($projects as $project) {
            $data[] = [
                'id' => (string) $project->getId(),
                'title' => $project->getTitle(),
                'budgetInCents' => $project->getBudget(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }
}
