<?php

namespace App\Controller\Api\v1;

use App\Entity\FundingProject;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/projects')]
class FundingProjectController
{
	public function __construct(
		private readonly EntityManagerInterface $entityManager,
	){
        
    }

	#[Route('/{id}', name: 'api_v1_funding_project_show', methods: ['GET'])]
	public function show(FundingProject $project): JsonResponse
	{ 
		$deadlines = [];
		foreach ($project->getDeadlines() as $deadline) {
			$deadlines[] = [
				'id' => $deadline->getId()->toRfc4122(),
				'title' => $deadline->getTitle(),
				'due_date' => $deadline->getDueDate()->format(\DateTimeInterface::ATOM),
				'type' => $deadline->getType()->value,
			];
		}

		$data = [
			'id' => $project->getId(),
			'title' => method_exists($project, 'getTitle') ? $project->getTitle() : null,
			'budget_in_cents' => method_exists($project, 'getBudgetInCents') ? $project->getBudgetInCents() : $project->getBudget(),
			'deadlines' => $deadlines,
		];

		return new JsonResponse($data, JsonResponse::HTTP_OK);
	}
}
