<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * AiTextService
 *
 * Corrects grammar and improves event descriptions using the local Ollama API.
 * Uses the same Ollama instance already configured in the project (OLLAMA_URL / OLLAMA_MODEL).
 */
class AiTextService
{
    private string $ollamaUrl;
    private string $ollamaModel;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        string $ollamaUrl,
        string $ollamaModel,
    ) {
        $this->ollamaUrl   = rtrim(trim($ollamaUrl), '/');
        $this->ollamaModel = trim($ollamaModel);
    }

    /**
     * Corrects grammar and improves the given text while keeping the same meaning.
     *
     * @throws \RuntimeException when Ollama is unreachable or returns an error
     */
    public function improveText(string $text): string
    {
        if (empty($this->ollamaUrl) || empty($this->ollamaModel)) {
            throw new \RuntimeException('Config manquante : vérifie OLLAMA_URL et OLLAMA_MODEL dans .env.local');
        }

        $system = "You are an expert French and English copywriter. "
            . "Correct the grammar, fix spelling mistakes, and improve the style "
            . "of the event description the user provides. "
            . "Keep exactly the same meaning and the same language. "
            . "Return ONLY the improved text — no explanations, no quotes, no preamble.";

        $response = $this->httpClient->request('POST', $this->ollamaUrl . '/api/chat', [
            'headers' => ['Content-Type' => 'application/json'],
            'json'    => [
                'model'    => $this->ollamaModel,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user',   'content' => $text],
                ],
                'stream'  => false,
                'options' => [
                    'temperature' => 0.3,
                    'num_predict' => 512,
                ],
            ],
            'timeout' => 120,
        ]);

        $status = $response->getStatusCode();
        $data   = $response->toArray(false);

        if ($status >= 400) {
            $msg = $data['error'] ?? $data['message'] ?? json_encode($data);
            throw new \RuntimeException('Erreur Ollama : ' . $msg);
        }

        $content = $data['message']['content'] ?? null;

        if (!$content) {
            throw new \RuntimeException(
                "Réponse vide d'Ollama. Vérifie que le modèle `{$this->ollamaModel}` est bien téléchargé."
            );
        }

        return trim($content);
    }
}
