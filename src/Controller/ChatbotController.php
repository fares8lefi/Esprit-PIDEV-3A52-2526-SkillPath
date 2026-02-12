<?php

namespace App\Controller;

use App\Service\GeminiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/chatbot')]
class ChatbotController extends AbstractController
{
    private GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    #[Route('/api/message', name: 'api_chatbot_message', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function message(Request $request): JsonResponse
    {
        // Check if the user is an admin and deny access
        if ($this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $message = $data['message'] ?? '';

        if (empty($message)) {
            return new JsonResponse(['error' => 'Message is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $response = $this->geminiService->generateResponse($message);
            return new JsonResponse(['response' => $response]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Error generating response'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
