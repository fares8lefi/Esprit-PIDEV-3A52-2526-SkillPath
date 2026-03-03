<?php

namespace App\Tests\Service;

use App\Entity\Event;
use App\Entity\Location;
use App\Service\GeminiTranslatorService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GeminiTranslatorServiceTest extends TestCase
{
    private $httpClient;
    private $logger;
    private $translator;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->translator = new GeminiTranslatorService($this->httpClient, $this->logger, 'FAKE_KEY');
    }

    public function testTranslateEventSuccessful(): void
    {
        $event = $this->createMock(Event::class);
        $event->method('getTitle')->willReturn('Titre');
        $event->method('getDescription')->willReturn('Desc');
        
        $location = $this->createMock(Location::class);
        $location->method('getName')->willReturn('Lieu');
        $event->method('getLocation')->willReturn($location);
        
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('toArray')->willReturn([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => '{"title": "Title", "description": "Description", "location": "Location"}']
                        ]
                    ]
                ]
            ]
        ]);
        
        $this->httpClient->method('request')->willReturn($response);
        
        $result = $this->translator->translateEvent($event, 'en');
        
        $this->assertEquals('Title', $result['title']);
        $this->assertEquals('Description', $result['description']);
    }

    public function testTranslateEventWithEmptyKeyFallback(): void
    {
        $translator = new GeminiTranslatorService($this->httpClient, $this->logger, '');
        $event = $this->createMock(Event::class);
        $event->method('getTitle')->willReturn('Titre');
        
        $result = $translator->translateEvent($event, 'en');
        
        $this->assertStringContainsString('[Translated] Titre', $result['title']);
    }
}
