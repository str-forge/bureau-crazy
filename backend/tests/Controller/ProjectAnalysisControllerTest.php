<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api\v1;

use App\Entity\FundingProject;
use App\Service\DeepSeekService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProjectAnalysisControllerTest extends WebTestCase
{
    public function testAnalysisEndpointReturnsSuccess(): void
    {
        $client = static::createClient();

        $deepSeekStub = $this->createStub(DeepSeekService::class);
        $deepSeekStub->method('extractDeadlines')->willReturn([
            'deadlines' => [[
                'title' => 'Finanzbericht 2026',
                'date' => '2026-11-30',
                'type' => 'financial_report'
            ]]
        ]);

        $client->getContainer()->set(DeepSeekService::class, $deepSeekStub);
        /** @var EntityManagerInterface $em */
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $project = new FundingProject();
        $project->setTitle('KI-Test');
        $project->setBudget(50000);
        $em->persist($project);
        $em->flush();

        $client->request(
            'POST',
            sprintf('/api/v1/projects/%s/analyze', (string)$project->getId()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['text' => 'Finanzbericht bis 2026-11-30.'], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseIsSuccessful();
        $this->assertJson((string)$client->getResponse()->getContent());
    }
}
