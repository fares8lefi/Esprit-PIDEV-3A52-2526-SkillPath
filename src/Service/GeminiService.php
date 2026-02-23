<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class GeminiService
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(
        HttpClientInterface $client,
        #[Autowire(env: 'GEMINI_API_KEY')] string $apiKey
    ) {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    public function generateResponse(string $prompt): string
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $this->apiKey;

        try {
            $response = $this->client->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                return 'Error: Gemini API returned status ' . $statusCode;
            }

            $content = $response->toArray();

            // Extract the text from the response structure
            return $content['candidates'][0]['content']['parts'][0]['text'] ?? 'No response received from Gemini.';

        } catch (TransportExceptionInterface $e) {
            return 'Error communicating with Gemini: ' . $e->getMessage();
        } catch (\Exception $e) {
            return 'An error occurred: ' . $e->getMessage();
        }
    }
}
