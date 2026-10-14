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
    private static AbstractBrowser $client;

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

        // 1. Ein Testprojekt direkt über die API anlegen (POST)
        $client->request('POST', '/api/v1/projects', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json'
        ], json_encode([
            'title' => 'SPA Test-Förderung',
            'budget' => 12500000,          // Variante 1 (Tag 3 Default)
            'budget_in_cents' => 12500000, // Variante 2 (Tag 1 Fallback)
            'budgetInCents' => 12500000    // Variante 3 (Vite SPA)
        ]));

        $this->assertResponseStatusCodeSame(201);

        // Zwinge Doctrine, den First-Level-Cache im Test zu leeren,
        // damit der nachfolgende GET-Request die echten DB-Daten hydriert!
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->clear();

         // 2. Die Liste abrufen (GET)
        $client->request('GET', '/api/v1/projects', [], [], [
            'HTTP_ACCEPT' => 'application/json'
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        // 3. Inhalt der JSON-Antwort validieren
        $projects = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($projects);
        $this->assertNotEmpty($projects);

        // Wir suchen gezielt nach unserem eben erstellten Projekt im Array
        $foundProject = null;
        foreach ($projects as $project) {
            if ($project['title'] === 'SPA Test-Förderung') {
                $foundProject = $project;
                break;
            }
        }

        // Sicherstellen, dass das Projekt in der API-Liste aufgetaucht ist
        $this->assertNotNull($foundProject, 'Das neu angelegte Projekt wurde in der GET-Liste nicht gefunden!');

        // Die exakte Struktur für das Frontend validieren
        $this->assertArrayHasKey('id', $foundProject);
        $this->assertArrayHasKey('title', $foundProject);
        $this->assertArrayHasKey('budgetInCents', $foundProject);

        $this->assertSame('SPA Test-Förderung', $foundProject['title']);
    }
}
