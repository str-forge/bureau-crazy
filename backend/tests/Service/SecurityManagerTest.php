<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\SecurityManager;
use PHPUnit\Framework\TestCase;

class SecurityManagerTest extends TestCase
{
    public function testCreateJwtTokenContainsCorrectPayload(): void
    {
        $user = new User('auth-test@ngo.de');
        $manager = new SecurityManager('test_secret_12345');

        $token = $manager->createJwtToken($user);
        
        $this->assertNotEmpty($token);
        
        $parts = explode('.', $token);
        $this->assertCount(3, $parts);

        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        $this->assertSame('auth-test@ngo.de', $payload['email']);
        $this->assertContains('ROLE_USER', $payload['roles']);
    }
    
    public function testVerifyTotpCodeValidatesSignature(): void
    {
        $manager = new SecurityManager('test_secret_12345');
        $secret = 'NY4A2GA4JVWGS4ZA';

        $isValid = $manager->verifyTotpCode($secret, '123456');
        $this->assertFalse($isValid);
    }
}
