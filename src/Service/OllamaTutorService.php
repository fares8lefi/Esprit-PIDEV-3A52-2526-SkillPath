<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OllamaTutorService
{
    private string $ollamaUrl;
    private string $ollamaModel;
    private HttpClientInterface $http;

    public function __construct(string $ollamaUrl, string $ollamaModel, HttpClientInterface $http)
    {
        $this->ollamaUrl = rtrim(trim($ollamaUrl), '/');
        $this->ollamaModel = trim($ollamaModel);
        $this->http = $http;
    }

    public function tutor(string $question, string $context = '', string $mode = 'expliquer'): string
    {
        if ($question === '') {
            return "Écris une question 🙂";
        }

        if ($this->ollamaUrl === '' || $this->ollamaModel === '') {
            return "Config manquante : vérifie OLLAMA_URL et OLLAMA_MODEL dans .env.local";
        }

        // Instruction système (rôle tuteur)
        $system = "Tu es un tuteur personnel. Tu expliques simplement, tu résumes, tu reformules, "
                . "tu proposes des exercices, et tu réponds aux questions des étudiants.\n"
                . "Mode demandé: {$mode}.\n"
                . "Réponds en français, clair, structuré, avec des exemples quand c'est utile.";

        $user = "Question: {$question}\n\n"
              . "Contexte (cours/texte):\n{$context}";

        // Endpoint Ollama
        $url = "{$this->ollamaUrl}/api/chat";

        try {
            $response = $this->http->request('POST', $url, [
                'headers' => [
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => $this->ollamaModel,
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $user],
                    ],
                    'stream' => false,
                    'options' => [
                        'temperature' => 0.4,
                        'num_predict' => 80 // Reduced for much faster local generation
                    ]
                ],
                'timeout' => 120 // 2 minutes just in case the PC is slow
            ]);

            $status = $response->getStatusCode();
            $data = $response->toArray(false); // false = don't throw exception on bad status

            if ($status >= 400) {
                // message d'erreur Lisible de l'API Ollama
                $msg = $data['error'] ?? $data['message'] ?? json_encode($data);
                return "Erreur IA: " . $msg;
            }

            $content = $data['message']['content'] ?? null;
            if (!$content) {
                return "Erreur IA: réponse vide (vérifie si le modèle `{$this->ollamaModel}` est bien téléchargé).";
            }

            return $content;

        } catch (\Throwable $e) {
            return "Erreur IA: Impossible de se connecter à Ollama (" . $e->getMessage() . "). Assure-toi que l'application Ollama est lancée.";
        }
    }
}
