<?php

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
		self::$client->request('GET', '/api/v1/projects/'.$projectId);

		self::assertResponseIsSuccessful();
		self::assertResponseHeaderSame('Content-Type', 'application/json');

		$response = self::$client->getResponse();
		$data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

		self::assertArrayHasKey('deadlines', $data);
		self::assertIsArray($data['deadlines']);
		self::assertSame('Progress report deadline', $data['deadlines'][0]['title']);
		self::assertSame('progress_report', $data['deadlines'][0]['type']);
	}
}
