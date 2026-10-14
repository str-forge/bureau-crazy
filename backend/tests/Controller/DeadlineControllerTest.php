<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\FundingProject;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DeadlineControllerTest extends WebTestCase
{
	private EntityManagerInterface $entityManager;

	private KernelBrowser $client;

	protected function setUp(): void
	{
		parent::setUp();

		$this->client = static::createClient();
		$this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
	}

	public function testCanCreateDeadlineViaApi(): void
	{
		$project = new FundingProject();
		$this->entityManager->persist($project);
		$this->entityManager->flush();

		$projectId = (string) $project->getId();
		$this->entityManager->clear();

		$payload = [
			'project_id' => $projectId,
			'title' => 'Abschlussbericht',
			'due_date' => '2027-12-31T23:59:59+00:00',
			'type' => 'progress_report',
		];

		$this->client->request(
			'POST',
			'/api/v1/deadlines',
			[],
			[],
			['CONTENT_TYPE' => 'application/json'],
			json_encode($payload)
		);

		self::assertResponseStatusCodeSame(201);
	}

	public function testCreateDeadlineFailsWithInvalidData(): void
	{
		$project = new FundingProject();
		$this->entityManager->persist($project);
		$this->entityManager->flush();

		$projectId = (string) $project->getId();
		$this->entityManager->clear();

		$payload = [
			'project_id' => $projectId,
			'title' => 'Ungueltige Frist',
			'due_date' => '2020-01-01T00:00:00+00:00',
			'type' => 'progress_report',
		];

		$this->client->request(
			'POST',
			'/api/v1/deadlines',
			[],
			[],
			['CONTENT_TYPE' => 'application/json'],
			json_encode($payload)
		);

		self::assertResponseStatusCodeSame(400);
	}
}
