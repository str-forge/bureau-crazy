<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class DeepSeekService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function extractDeadlines(string $text): array
    {
        $response = $this->httpClient->request('POST', 'https://deepseek.com', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'deepseek-chat',
                'messages' => [
                    ['role' => 'user', 'content' => $text]
                ],
                'response_format' => ['type' => 'json_object']
            ]
        ]);

        $responseData = $response->toArray();
        $content = $responseData['choices'][0]['message']['content'] ?? '{}';

        return json_decode($content, true) ?? [];
    }
}
