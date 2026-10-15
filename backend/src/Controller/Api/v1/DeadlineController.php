<?php

declare(strict_types=1);

namespace App\Controller\Api\v1;

use App\Entity\Deadline;
use App\Entity\FundingProject;
use App\Enum\DeadlineType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/deadlines')]
class DeadlineController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: 'api_v1_deadlines_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // 1. Read the JSON payload and parse it with a safe fallback
        $content = json_decode($request->getContent(), true);

        if (!is_array($content)) {
            $content = $request->request->all();
        }

        // Apply API guard clauses before processing
        if (empty($content) || empty($content['project_id']) || empty($content['title'])) {
            return new JsonResponse(
                ['error' => 'Ungültige Daten, Projekt-ID oder Titel fehlt.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // 2. Convert the string ID back to a Uuid object for Doctrine
        try {
            $projectUuid = Uuid::fromString($content['project_id']);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => 'Ungültiges UUID-Format.'], Response::HTTP_BAD_REQUEST);
        }

        // Load the associated project from PostgreSQL
        $project = $this->entityManager->getRepository(FundingProject::class)->find($projectUuid);
        if (!$project) {
            return new JsonResponse(['error' => 'Das angegebene Projekt existiert nicht.'], Response::HTTP_NOT_FOUND);
        }

        // 3. Instantiate and populate a new deadline
        $deadline = new Deadline();
        $deadline->setTitle($content['title']);

        // Convert the date from an ISO 8601 string
        if (!empty($content['due_date'])) {
            $deadline->setDueDate(new \DateTimeImmutable($content['due_date']));
        }

        // Map the enum type case-insensitively
        if (!empty($content['type'])) {
            $typeString = strtolower($content['type']);
            $typeEnum = DeadlineType::tryFrom($typeString);

            if (!$typeEnum) {
                $typeEnum = DeadlineType::tryFrom(strtoupper($typeString));
            }

            if ($typeEnum) {
                $deadline->setType($typeEnum);
            }
        }

        // Set the relationship
        $deadline->setFundingProject($project);

        // 4. 🔥 API validation: enforce the domain constraints
        $violations = $this->validator->validate($deadline);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        // 5. Persist
        $this->entityManager->persist($deadline);
        $this->entityManager->flush();

        // 6. Generate the JSON response for the frontend
        $deadlineIdString = $deadline->getId();
        $projectIdString = $project->getId();

        $responseData = [
            'id' => $deadlineIdString,
            'title' => $deadline->getTitle(),
            'due_date' => $deadline->getDueDate()->format(\DateTimeInterface::ATOM),
            'type' => $deadline->getType()->value,
            'project_id' => $projectIdString,
            'risk_level' => $deadline->getRiskLevel()->value
        ];


        return new JsonResponse($responseData, Response::HTTP_CREATED);
    }
}
