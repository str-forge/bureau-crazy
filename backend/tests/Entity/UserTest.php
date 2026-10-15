<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserCreationAndDefaultRoles(): void
    {
        $user = new User('dev@ngo.de');

        $this->assertSame('dev@ngo.de', $user->getEmail());
        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertInstanceOf(\Symfony\Component\Uid\Uuid::class, $user->getId());
    }

    public function testTotpSecretActivation(): void
    {
        $user = new User('dev@ngo.de');
        $this->assertFalse($user->is2faEnabled());

        $user->setTotpSecret('SECRET123');
        $this->assertTrue($user->is2faEnabled());
        $this->assertSame('SECRET123', $user->getTotpSecret());
    }

    public function testAccountLockoutAfterTooManyFailedAttempts(): void
    {
        $user = new User('dev@ngo.de');
        $this->assertFalse($user->isLocked());

        $user->incrementFailed2faAttempts();
        $user->incrementFailed2faAttempts();
        $user->incrementFailed2faAttempts();

        $this->assertTrue($user->isLocked());
    }
}
