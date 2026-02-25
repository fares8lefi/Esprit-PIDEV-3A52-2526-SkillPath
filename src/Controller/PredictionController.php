<?php

namespace App\Controller;

use App\Service\AIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PredictionController extends AbstractController
{
    #[Route('/test-ai', name: 'app_test_ai')]
    public function index(AIService $aiService): JsonResponse
    {
        // Exemple de données à envoyer au modèle
        $userData = [
            'age' => 25,
            'experience_years' => 2,
            'hours_spent' => 12.5,
            // ... ajoutez toutes les features attendues par votre modèle .pkl
        ];

        try {
            // Appel au microservice Flask
            $result = $aiService->getPrediction($userData, 'Gradient_Boosting');
            return new JsonResponse([
                'status' => 'success',
                'prediction' => $result['prediction'] ?? $result,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
