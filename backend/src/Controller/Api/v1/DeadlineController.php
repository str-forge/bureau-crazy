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
		// 1. JSON-Payload auslesen und fallbacksicher parsen
        $content = json_decode($request->getContent(), true);

        if (!is_array($content)) {
            $content = $request->request->all();
        }

        // API-Schutzschalter vor dem Verarbeiten
        if (empty($content) || empty($content['project_id']) || empty($content['title'])) {
            return new JsonResponse(
                ['error' => 'Ungültige Daten, Projekt-ID oder Titel fehlt.'], 
                Response::HTTP_BAD_REQUEST
            );
        }

        // 2. String-ID in ein echtes Uuid-Objekt für Doctrine zurückverwandeln
        try {
            $projectUuid = Uuid::fromString($content['project_id']);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => 'Ungültiges UUID-Format.'], Response::HTTP_BAD_REQUEST);
        }

        // Zugehöriges Projekt aus PostgreSQL laden
        $project = $this->entityManager->getRepository(FundingProject::class)->find($projectUuid);
        if (!$project) {
            return new JsonResponse(['error' => 'Das angegebene Projekt existiert nicht.'], Response::HTTP_NOT_FOUND);
        }

        // 3. Neue Deadline instanziieren und befüllen
        $deadline = new Deadline();
        $deadline->setTitle($content['title']);
        
        // Datum aus ISO-8601-String konvertieren
        if (!empty($content['due_date'])) {
            $deadline->setDueDate(new \DateTimeImmutable($content['due_date']));
        }

        // Enum-Typ fehlertolerant (Groß-/Kleinschreibung) mappen
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

        // Relation setzen
        $deadline->setFundingProject($project);

        // 4. 🔥 API-Validierung: Die Domänen-Schutzregeln triggern
        $violations = $this->validator->validate($deadline);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        // 5. Speichern
        $this->entityManager->persist($deadline);
        $this->entityManager->flush();

        // 6. JSON-Response für das Frontend generieren
        $deadlineId = $deadline->getId();
        $deadlineId = is_object($deadlineId) && method_exists($deadlineId, 'toRfc4122')
            ? $deadlineId->toRfc4122()
            : (string) $deadlineId;

        $projectId = $project->getId();
        $projectId = is_object($projectId) && method_exists($projectId, 'toRfc4122')
            ? $projectId->toRfc4122()
            : (string) $projectId;

        $responseData = [
            'id' => $deadlineId,
            'title' => $deadline->getTitle(),
            'due_date' => $deadline->getDueDate()->format(\DateTimeInterface::ATOM),
            'type' => $deadline->getType()->value,
            'project_id' => $projectId
        ];

        return new JsonResponse($responseData, Response::HTTP_CREATED);
    }
}
