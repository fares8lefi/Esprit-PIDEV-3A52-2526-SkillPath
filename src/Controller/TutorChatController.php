<?php

namespace App\Controller;

use App\Service\OllamaTutorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TutorChatController extends AbstractController
{
    #[Route('/api/tutor', name: 'api_tutor', methods: ['POST'])]
    public function tutor(Request $request, OllamaTutorService $tutor): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            $payload = [];
        }

        $question = trim((string)($payload['question'] ?? ''));
        $context  = trim((string)($payload['context'] ?? ''));
        $mode     = trim((string)($payload['mode'] ?? 'expliquer'));

        if ($question === '') {
            return $this->json(['answer' => 'Écris une question 🙂'], 400);
        }

        $answer = $tutor->tutor($question, $context, $mode);

        return $this->json(['answer' => $answer]);
    }
}
