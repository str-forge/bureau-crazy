<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api\v1;

use App\Entity\FundingProject;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProjectCalendarControllerTest extends WebTestCase
{
    public function testExportCalendarReturnsIcsStream(): void
    {
        $client = static::createClient();
        /** @var EntityManagerInterface $em */
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $project = new FundingProject();
        $project->setTitle('Export-Test');
        $project->setBudget(25000);
        $em->persist($project);
        $em->flush();

        $client->request('GET', sprintf('/api/v1/projects/%s/ical', (string)$project->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSame('text/calendar; charset=utf-8', $client->getResponse()->headers->get('Content-Type'));
        $this->assertStringContainsString('BEGIN:VCALENDAR', (string)$client->getResponse()->getContent());
    }
}
