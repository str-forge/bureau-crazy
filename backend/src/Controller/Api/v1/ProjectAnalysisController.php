<?php

declare(strict_types=1);

namespace App\Controller\Api\v1;

use App\Entity\FundingProject;
use App\Service\AnalysisService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/projects')]
class ProjectAnalysisController extends AbstractController
{
    public function __construct(
        private readonly AnalysisService $analysisService
    ) {
    }

    #[Route('/{id}/analyze', name: 'api_project_analyze', methods: ['POST'])]
    public function analyze(FundingProject $project, Request $request): JsonResponse
    {
        /** @var array{text?: string} $payload */
        $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $text = $payload['text'] ?? '';

        if (empty($text)) {
            return new JsonResponse(['error' => 'Text payload is required'], Response::HTTP_BAD_REQUEST);
        }

        $this->analysisService->analyzeAndApply($project, $text);

        return new JsonResponse(['status' => 'Analysis completed'], Response::HTTP_OK);
    }
}
