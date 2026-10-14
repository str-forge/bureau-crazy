<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $container = self::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);

        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
    }

    public function testLoginWithValidCredentialsReturnsPreAuthToken(): void
    {
        $user = new User('login-test@ngo.de');
        $user->setPassword(password_hash('password123', PASSWORD_BCRYPT));
        $user->setTotpSecret('NY4A2GA4JVWGS4ZA');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->request(
            'POST',
            '/api/v1/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
            'email' => 'login-test@ngo.de',
            'password' => 'password123'
            ])
        );

        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['requires_2fa']);
        $this->assertArrayHasKey('pre_auth_token', $data);
    }

    public function testVerifyValid2faCodeReturnsAccessToken(): void
    {
        $user = new User('2fa-test@ngo.de');
        $user->setTotpSecret('NY4A2GA4JVWGS4ZA');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $container = self::getContainer();
        $securityManager = $container->get(\App\Service\SecurityManager::class);
        $validCode = $securityManager->generateTotpCodeForTesting('NY4A2GA4JVWGS4ZA');

        $this->client->request(
            'POST',
            '/api/v1/auth/2fa',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => '2fa-test@ngo.de',
                'code' => $validCode
            ])
        );

        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('access_token', $data);
    }
}
