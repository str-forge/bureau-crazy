<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\DeepSeekService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class DeepSeekServiceTest extends TestCase
{
    public function testExtractDeadlinesFromTextReturnsStructuredData(): void
    {
        $mockResponseBody = json_encode([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode([
                            'title' => 'Zwischenbericht einreichen',
                            'days_until_due' => 30,
                            'type' => 'PROGRESS_REPORT'
                        ])
                    ]
                ]
            ]
        ]);

        $mockResponse = new MockResponse($mockResponseBody, [
            'http_code' => 200,
            'headers' => ['content-type' => 'application/json']
        ]);

        $httpClient = new MockHttpClient($mockResponse);
        $service = new DeepSeekService($httpClient, 'fake-api-key-123');

        $result = $service->extractDeadlines('Richtlinie für digitale Förderung...');

        $this->assertSame('Zwischenbericht einreichen', $result['title']);
        $this->assertSame(30, $result['days_until_due']);
        $this->assertSame('PROGRESS_REPORT', $result['type']);
    }
}
