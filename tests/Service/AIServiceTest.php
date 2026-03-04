<?php

namespace App\Tests\Service;

use App\Service\AIService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AIServiceTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject&HttpClientInterface $httpClient;
    private AIService $aiService;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->aiService = new AIService($this->httpClient, 'http://ai-api.local');
    }

    public function testGetPrediction(): void
    {
        $data = ['age' => 25, 'score' => 80];
        $model = 'Random_Forest';
        
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn(['prediction' => 'Success']);
        
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('POST', 'http://ai-api.local/api/predict', [
                'json' => [
                    'model' => $model,
                    'features' => $data
                ]
            ])
            ->willReturn($response);
            
        $result = $this->aiService->getPrediction($data);
        
        $this->assertEquals('Success', $result['prediction']);
    }
}
