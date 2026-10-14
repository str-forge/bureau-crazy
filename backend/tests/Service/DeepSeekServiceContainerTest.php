<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\DeepSeekService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DeepSeekServiceContainerTest extends KernelTestCase
{
    public function testServiceIsCorrectlyRegisteredInContainer(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $service = $container->get(DeepSeekService::class);

        $this->assertInstanceOf(DeepSeekService::class, $service);
    }
}
