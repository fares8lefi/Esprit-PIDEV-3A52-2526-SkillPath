<?php

namespace App\Tests\Service;

use App\Service\GeminiService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GeminiServiceTest extends TestCase
{
    private $client;
    private $geminiService;

    protected function setUp(): void
    {
        $this->client = $this->createMock(HttpClientInterface::class);
        $this->geminiService = new GeminiService($this->client, 'FAKE_KEY');
    }

    public function testGenerateResponseSuccessful(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('toArray')->willReturn([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => 'Hello from AI']
                        ]
                    ]
                ]
            ]
        ]);
        
        $this->client->method('request')->willReturn($response);
        
        $result = $this->geminiService->generateResponse('Hello');
        $this->assertEquals('Hello from AI', $result);
    }

    public function testGenerateResponseQuotaExceeded(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(429);
        
        $this->client->method('request')->willReturn($response);
        
        $result = $this->geminiService->generateResponse('Hello');
        $this->assertStringContainsString('quota atteint', $result);
    }
}
