<?php

namespace App\Controller;

use App\Service\HuggingFaceTutorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HuggingFaceChatController extends AbstractController
{
    #[Route('/api/huggingface-chat', name: 'api_huggingface_chat', methods: ['POST'])]
    public function tutor(Request $request, HuggingFaceTutorService $tutor): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];
        $question = trim((string)($payload['question'] ?? ''));
        $context  = trim((string)($payload['context'] ?? ''));
        $mode     = trim((string)($payload['mode'] ?? 'expliquer'));

        if ($question === '') {
            return $this->json(['answer' => 'Écris une question 🙂'], 400);
        }

        return $this->json(['answer' => $tutor->tutor($question, $context, $mode)]);
    }
}   