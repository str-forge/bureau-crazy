<?php

declare(strict_types=1);

namespace App\Controller\Api\v1;

use App\Entity\FundingProject;
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
        // 1. Rohen JSON-Body aus dem Request fangen und dekodieren
        $content = json_decode($request->getContent(), true);

        // API-Schutzschalter: Einfache Validierung (Subtask 3.4 Vorbereitung)
        if (!$content || empty($content['title'])) {
            return new JsonResponse(
                ['error' => 'Ungültige Daten oder fehlender Titel.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // 2. Neue Entität instanziieren und befüllen
        $project = new FundingProject();
        $project->setTitle($content['title']);

        // Flexibler Budget-Setter (Kompatibilität zu Tag 1)
        $budget = isset($content['budget_in_cents']) ? (int)$content['budget_in_cents'] : 0;
        if (method_exists($project, 'setBudgetInCents')) {
            $project->setBudgetInCents($budget);
        } elseif (method_exists($project, 'setBudget')) {
            $project->setBudget($budget);
        }

        // 3. Persistent in die PostgreSQL-Datenbank schreiben
        $this->entityManager->persist($project);
        $this->entityManager->flush();

        // FIX: Robuste ID-Auflösung (Abfangjäger für Strings und Objekte)
        $idRaw = $project->getId();
        $idString = is_object($idRaw) && method_exists($idRaw, 'toRfc4122')
            ? $idRaw->toRfc4122()
            : (string)$idRaw;

        $responseData = [
            'id' => $idString, // Nutzen den sicheren String
            'title' => $project->getTitle(),
            'budget_in_cents' => $budget,
            'deadlines' => []
        ];


        // Mit HTTP 201 Created antworten
        return new JsonResponse($responseData, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_v1_project_show', methods: ['GET'])]
    public function show(FundingProject $project): JsonResponse
    {
        $budget = 0;
        if (method_exists($project, 'getBudgetInCents')) {
            $budget = $project->getBudgetInCents();
        } elseif (method_exists($project, 'getBudget')) {
            $budget = $project->getBudget();
        }

        $deadlinesData = [];
        foreach ($project->getDeadlines() as $deadline) {
            $deadlinesData[] = [
                'id' => $deadline->getId(),
                'title' => $deadline->getTitle(),
                'due_date' => $deadline->getDueDate()->format(\DateTimeInterface::ATOM),
                'type' => $deadline->getType()->value
            ];
        }

        $data = [
            'id' => $project->getId(),
            'title' => method_exists($project, 'getTitle') ? $project->getTitle() : null,
            'budget_in_cents' => $budget,
            'deadlines' => $deadlinesData
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('', name: 'api_v1_project_list', methods: ['GET'])]
    public function list(\App\Repository\FundingProjectRepository $repository): JsonResponse
    {
        $projects = $repository->findAll();
        $data = [];

        foreach ($projects as $project) {
            $data[] = [
                'id' => $project->getId(),
                'title' => $project->getTitle(),
                'budgetInCents' => $project->getBudget(),
            ];
        }

        return new JsonResponse($data, \Symfony\Component\HttpFoundation\Response::HTTP_OK);
    }
}
