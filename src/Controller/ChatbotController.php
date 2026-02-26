<?php

namespace App\Controller;

use App\Service\OllamaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/chatbot')]
class ChatbotController extends AbstractController
{
    private OllamaService $ollamaService;

    public function __construct(OllamaService $ollamaService)
    {
        $this->ollamaService = $ollamaService;
    }

    #[Route('/api/message', name: 'api_chatbot_message', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function message(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $message = $data['message'] ?? '';

        if (empty($message)) {
            return new JsonResponse(['error' => 'Message is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            if ($this->isGranted('ROLE_ADMIN')) {
                // Admin specific prompt
                $prompt = "Tu es l'assistant IA exclusif de SkillPath pour l'Administrateur. 
                CONSIGNE STRICTE : Tu ne dois répondre qu'aux questions concernant la gestion de SkillPath, l'analyse des réclamations et le support technique du site. 
                Si l'administrateur te pose une question hors-sujet (culture générale, code non lié au site, vie privée, etc.), refuse poliment en expliquant que tu es dédié uniquement au support de SkillPath.
                Message : " . $message;
            } else {
                // User specific prompt
                $prompt = "Tu es l'assistant IA exclusif de SkillPath. 
                TA MISSION : Aider les utilisateurs uniquement pour leurs problèmes sur ce site ou leurs réclamations.
                CONSIGNE CRITIQUE : Si le message n'est pas lié à un problème technique, un cours sur SkillPath, ou une réclamation, tu DOIS répondre : 'Désolé, je suis uniquement programmé pour vous aider avec les problèmes liés à SkillPath et vos réclamations. Comment puis-je vous aider concernant votre expérience sur le site ?'
                Ne réponds JAMAIS à des questions de culture générale, de cuisine, d'autres sites, ou tout sujet non lié à SkillPath.
                Message : " . $message;
            }

            $response = $this->ollamaService->generateResponse($prompt);
            return new JsonResponse(['response' => $response]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Error generating response: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
