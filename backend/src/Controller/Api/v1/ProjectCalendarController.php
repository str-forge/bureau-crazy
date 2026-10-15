<?php

declare(strict_types=1);

namespace App\Controller\Api\v1;

use App\Entity\FundingProject;
use App\Service\ICalGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProjectCalendarController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ICalGeneratorInterface $calendarGenerator
    ) {
    }

    #[Route('/api/v1/projects/{id}/ical', name: 'api_v1_project_ical', methods: ['GET'])]
    public function exportProjectCalendar(string $id): Response
    {
        $project = $this->entityManager->getRepository(FundingProject::class)->find($id);

        if (!$project) {
            return new Response('Project not found', Response::HTTP_NOT_FOUND);
        }

        $icsContent = $this->calendarGenerator->generateForProject($project);

        $response = new Response($icsContent);
        $response->headers->set('Content-Type', 'text/calendar; charset=utf-8');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="project-%s.ics"', $id));

        return $response;
    }
}
