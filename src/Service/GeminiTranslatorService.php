<?php

namespace App\Service;

use App\Entity\Event;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class GeminiTranslatorService
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private string $apiKey;
    
    // The endpoint for gemini 1.5 flash text generation
    private const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger, string $geminiApiKey = '')
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->apiKey = trim($geminiApiKey);
    }

    /**
     * Translates an Event's title, description, and location into the target language.
     * @return array<string, string>
     */
    public function translateEvent(Event $event, string $targetLanguage): array
    {
        $originalTitle = $event->getTitle() ?? '';
        $originalDesc = $event->getDescription() ?? '';
        $originalLoc = $event->getLocation() ? $event->getLocation()->getName() : 'En ligne';

        // Fallback fake translation if API key is missing or not yet configured by the user
        if (empty($this->apiKey) || str_contains($this->apiKey, 'votre_cle_api_ici')) {
            $this->logger->warning('API Key for Gemini missing. Returning fake translated data.');
            sleep(1); // Simulate network latency

            $prefix = ($targetLanguage === 'ar') ? '[مترجم] ' : '[Translated] ';
            return [
                'title' => $prefix . $originalTitle,
                'description' => "($targetLanguage) " . $originalDesc,
                'location' => $prefix . $originalLoc,
            ];
        }

        // Prepare the prompt
        $languageName = ($targetLanguage === 'ar') ? 'Arabic' : 'English';
        $jsonSchema = '{ "title": "...", "description": "...", "location": "..." }';
        
        $prompt = <<<PROMPT
You are a professional translator. Translate the following event details from French into $languageName.
Return ONLY a valid JSON object matching this schema exactly: $jsonSchema
Do not use markdown formatting like ```json. Just raw valid JSON.

Event Title: $originalTitle
Event Description: $originalDesc
Event Location: $originalLoc
PROMPT;

        try {
            $response = $this->httpClient->request('POST', self::API_URL . '?key=' . $this->apiKey, [
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ],
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                $this->logger->error("Gemini API error ($statusCode): " . $response->getContent(false));
                return $this->getFallback($originalTitle, $originalDesc, $originalLoc);
            }

            $data = $response->toArray();
            $generatedText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            // Clean up potentially returned markdown blocks from Gemini
            $generatedText = str_replace(['```json', '```'], '', $generatedText);
            $generatedText = trim($generatedText);

            $decoded = json_decode($generatedText, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['title'])) {
                return [
                    'title' => $decoded['title'],
                    'description' => $decoded['description'] ?? $originalDesc,
                    'location' => $decoded['location'] ?? $originalLoc,
                ];
            }

            $this->logger->error('Failed to parse Gemini JSON output: ' . $generatedText);

        } catch (\Exception $e) {
            $this->logger->error('Exception caught during Gemini translation: ' . $e->getMessage());
        }

        return $this->getFallback($originalTitle, $originalDesc, $originalLoc);
    }

    /**
     * @return array<string, string>
     */
    private function getFallback(string $title, string $desc, string $loc): array
    {
        return [
            'title' => $title,
            'description' => $desc,
            'location' => $loc,
        ];
    }
}
