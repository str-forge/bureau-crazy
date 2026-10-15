<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Deadline;
use App\Entity\FundingProject;
use App\Enum\DeadlineType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;

class FundingProjectControllerTest extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private static $client;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::$client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testCanRetrieveProjectViaCollectionApi(): void
    {
        $project = new FundingProject();
        $this->entityManager->persist($project);

        $deadline = new Deadline();
        $deadline
            ->setTitle('Progress report deadline')
            ->setDueDate(new \DateTimeImmutable('+30 days'))
            ->setType(DeadlineType::PROGRESS_REPORT)
            ->setFundingProject($project);

        $this->entityManager->persist($deadline);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $projectId = (string) $project->getId();
        self::$client->request('GET', '/api/v1/projects/' . $projectId);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/json');

        $response = self::$client->getResponse();
        $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('deadlines', $data);
        self::assertIsArray($data['deadlines']);
        self::assertSame('Progress report deadline', $data['deadlines'][0]['title']);
        self::assertSame('progress_report', $data['deadlines'][0]['type']);
    }

    public function testCanRetrieveProjectListViaApi(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();

        // 1. Create a test project directly through the API (POST)
        $client->request('POST', '/api/v1/projects', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json'
        ], json_encode([
            'title' => 'SPA Test-Förderung',
            'budget' => 12500000,          // Variant 1 (day 3 default)
            'budget_in_cents' => 12500000, // Variant 2 (day 1 fallback)
            'budgetInCents' => 12500000    // Variant 3 (Vite SPA)
        ]));

        $this->assertResponseStatusCodeSame(201);

        // Force Doctrine to clear the first-level cache in the test,
        // so the following GET request hydrates the actual database data!
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->clear();

        // 2. Retrieve the list (GET)
        $client->request('GET', '/api/v1/projects', [], [], [
            'HTTP_ACCEPT' => 'application/json'
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        // 3. Validate the JSON response content
        $projects = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($projects);
        $this->assertNotEmpty($projects);

        // Find the newly created project in the array
        $foundProject = null;
        foreach ($projects as $project) {
            if ($project['title'] === 'SPA Test-Förderung') {
                $foundProject = $project;
                break;
            }
        }

        // Ensure that the project appears in the API list
        $this->assertNotNull($foundProject, 'Das neu angelegte Projekt wurde in der GET-Liste nicht gefunden!');

        // Validate the exact structure expected by the frontend
        $this->assertArrayHasKey('id', $foundProject);
        $this->assertArrayHasKey('title', $foundProject);
        $this->assertArrayHasKey('budgetInCents', $foundProject);

        $this->assertSame('SPA Test-Förderung', $foundProject['title']);
    }
}
