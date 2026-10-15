<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\SecurityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SecurityManagerContainerTest extends KernelTestCase
{
    public function testServiceIsCorrectlyRegisteredInContainer(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        // Verify that autowiring and parameter injection are configured
        $manager = $container->get(SecurityManager::class);

        $this->assertInstanceOf(SecurityManager::class, $manager);
    }
}
