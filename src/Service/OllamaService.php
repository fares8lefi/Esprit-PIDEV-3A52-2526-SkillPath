<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class OllamaService
{
    private HttpClientInterface $client;
    private string $ollamaUrl;
    private string $ollamaModel;

    public function __construct(
        HttpClientInterface $client,
        #[Autowire(env: 'OLLAMA_URL')] string $ollamaUrl,
        #[Autowire(env: 'OLLAMA_MODEL')] string $ollamaModel
    ) {
        $this->client = $client;
        $this->ollamaUrl = rtrim($ollamaUrl, '/');
        $this->ollamaModel = $ollamaModel;
    }

    public function generateResponse(string $prompt): string
    {
        $url = $this->ollamaUrl . '/api/generate';

        try {
            $response = $this->client->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->ollamaModel,
                    'prompt' => $prompt,
                    'stream' => false,
                ],
                'timeout' => 60, // Local models can be slow
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                return 'Error: Ollama API returned status ' . $statusCode;
            }

            $content = $response->toArray();

            return $content['response'] ?? 'No response received from Ollama.';

        } catch (TransportExceptionInterface $e) {
            return 'Error communicating with Ollama: ' . $e->getMessage();
        } catch (\Exception $e) {
            return 'An error occurred: ' . $e->getMessage();
        }
    }
    /**
     * @return array{is_aggressive: bool, has_bad_words: bool, clean_description: string}
     */
    public function analyzeReclamation(string $description): array
    {
        // 1. Manual safety check (Safety net)
        $badWords = ['merde', 'connard', 'salope', 'putain', 'fuck', 'shit', 'idiot', 'btf', 'zbi'];
        $foundBad = false;
        foreach ($badWords as $word) {
            if (stripos($description, $word) !== false) {
                $foundBad = true;
                break;
            }
        }

        // 2. AI Analysis
        $prompt = "You are a support analysis expert. Analyze the following user complaint.
        
        GOAL:
        1. Set 'is_aggressive' to true if the user is very angry, extremely frustrated, or uses an imperative/authoritarian tone (e.g., 'I demand', 'Fix this now').
        2. Set 'has_bad_words' to true if there are explicit insults.
        
        TEXT: \"$description\"
        
        RESPONSE FORMAT:
        Respond ONLY with a JSON object.
        {
          \"is_aggressive\": boolean,
          \"has_bad_words\": boolean,
          \"clean_description\": \"text with asterisks for insults if any\"
        }";

        $response = $this->generateResponse($prompt);
        
        $isAggressive = $foundBad;
        $hasBadWords = $foundBad;
        $cleanDescription = $description;

        // Tentative d'extraction du JSON
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $json = $matches[0];
            $aiData = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // Use OR (||) to keep the 'true' if manual check already found something
                $isAggressive = ($aiData['is_aggressive'] ?? false) || $foundBad;
                $hasBadWords = ($aiData['has_bad_words'] ?? false) || $foundBad;
                $cleanDescription = $aiData['clean_description'] ?? $description;
            }
        }

        return [
            'is_aggressive' => $isAggressive,
            'has_bad_words' => $hasBadWords,
            'clean_description' => $cleanDescription
        ];
    }
}
