<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class HuggingFaceTutorService
{
    private string $hfToken;
    private string $hfModel;
    private HttpClientInterface $http;

    public function __construct(string $hfToken, string $hfModel, HttpClientInterface $http)
    {
        $this->hfToken = trim($hfToken);
        $this->hfModel = trim($hfModel);
        $this->http = $http;
    }

    public function tutor(string $question, string $context = '', string $mode = 'expliquer'): string
    {
        if ($question === '') {
            return "Écris une question 🙂";
        }

        if ($this->hfToken === '' || $this->hfModel === '') {
            return "Config manquante : vérifie HF_TOKEN et HF_MODEL dans .env.local";
        }

        // Instruction système (rôle tuteur)
        $system = "Tu es un tuteur personnel. Tu expliques simplement, tu résumes, tu reformules, "
                . "tu proposes des exercices, et tu réponds aux questions des étudiants.\n"
                . "Mode demandé: {$mode}.\n"
                . "Réponds en français, clair, structuré, avec des exemples quand c'est utile.";

        $user = "Question: {$question}\n\n"
              . "Contexte (cours/texte):\n{$context}";

        // ✅ Endpoint Router correct (sans /hf-inference/)
        $url = "https://router.huggingface.co/v1/chat/completions";

        try {
            $response = $this->http->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->hfToken,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => $this->hfModel,
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $user],
                    ],
                    'temperature' => 0.4,
                    'max_tokens' => 400,
                ],
            ]);

            $status = $response->getStatusCode();
            $data = $response->toArray(false); // false = ne plante pas si JSON erreur

            if ($status >= 400) {
                // message d'erreur HF lisible
                $msg = $data['error']['message'] ?? $data['message'] ?? json_encode($data);
                return "Erreur IA: " . $msg;
            }

            $content = $data['choices'][0]['message']['content'] ?? null;
            if (!$content) {
                return "Erreur IA: réponse vide (vérifie HF_MODEL et les providers).";
            }

            return $content;

        } catch (\Throwable $e) {
            return "Erreur IA: " . $e->getMessage();
        }
    }
}
