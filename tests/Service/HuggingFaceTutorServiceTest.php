<?php

namespace App\Tests\Service;

use App\Service\HuggingFaceTutorService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HuggingFaceTutorServiceTest extends TestCase
{
    private $httpClient;
    private $tutorService;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->tutorService = new HuggingFaceTutorService('TOKEN', 'MODEL', $this->httpClient);
    }

    public function testTutorSuccessful(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('toArray')->willReturn([
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Voici l\'explication'
                    ]
                ]
            ]
        ]);
        
        $this->httpClient->method('request')->willReturn($response);
        
        $result = $this->tutorService->tutor('Comment apprendre?', 'Course context');
        $this->assertEquals('Voici l\'explication', $result);
    }

    public function testTutorEmptyQuestion(): void
    {
        $result = $this->tutorService->tutor('');
        $this->assertStringContainsString('une question', $result);
    }

    public function testTutorErrorResponse(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(500);
        $response->method('toArray')->willReturn(['error' => ['message' => 'API offline']]);
        
        $this->httpClient->method('request')->willReturn($response);
        
        $result = $this->tutorService->tutor('Why');
        $this->assertStringContainsString('Erreur IA: API offline', $result);
    }
}
