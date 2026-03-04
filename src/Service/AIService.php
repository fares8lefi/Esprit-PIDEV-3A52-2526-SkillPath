<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class AIService
{
    private HttpClientInterface $httpClient;
    private string $aiUrl;

    public function __construct(HttpClientInterface $httpClient, string $aiUrl)
    {
        $this->httpClient = $httpClient;
        $this->aiUrl = $aiUrl;
    }

    /**
     * @param array<mixed> $data Données des features
     * @param string $model Nom du modèle (Random_Forest, Gradient_Boosting, Logistic_Regression)
     * @return array<string, mixed>
     */
    public function getPrediction(array $data, string $model = 'Random_Forest'): array
    {
        $response = $this->httpClient->request('POST', $this->aiUrl . '/api/predict', [
            'json' => [
                'model' => $model,
                'features' => $data
            ]
        ]);

        return $response->toArray();
    }
}
