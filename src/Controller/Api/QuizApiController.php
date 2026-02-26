<?php

namespace App\Controller\Api;

use App\Entity\Resultat;
use App\Entity\Quiz;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/quizzes')]
class QuizApiController extends AbstractController
{
    #[Route('', name: 'api_quiz_list', methods: ['GET'])]
    public function index(QuizRepository $quizRepository): JsonResponse
    {
        $quizzes = $quizRepository->findAll();
        $data = [];

        foreach ($quizzes as $quiz) {
            $data[] = [
                'id' => $quiz->getId(),
                'title' => $quiz->getTitre(),
                'description' => $quiz->getDescription(),
                'duration' => $quiz->getDuree(),
                'questionCount' => count($quiz->getQuestions()),
                'course' => $quiz->getCourse() ? $quiz->getCourse()->getTitle() : null,
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', name: 'api_quiz_show', methods: ['GET'])]
    public function show(Quiz $quiz): JsonResponse
    {
        $questionsData = [];
        foreach ($quiz->getQuestions() as $question) {
            $questionsData[] = [
                'id' => $question->getId(),
                'text' => $question->getEnonce(),
                'points' => $question->getPoints(),
                'choices' => [
                    'A' => $question->getChoixA(),
                    'B' => $question->getChoixB(),
                    'C' => $question->getChoixC(),
                    'D' => $question->getChoixD(),
                ]
            ];
        }

        return $this->json([
            'id' => $quiz->getId(),
            'title' => $quiz->getTitre(),
            'description' => $quiz->getDescription(),
            'duration' => $quiz->getDuree(),
            'maxScore' => $quiz->getNoteMax(),
            'questions' => $questionsData
        ]);
    }

    #[Route('/{id}/submit', name: 'api_quiz_submit', methods: ['POST'])]
    public function submit(Request $request, Quiz $quiz, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['answers'])) {
            return $this->json(['error' => 'Invalid JSON or missing "answers" key'], 400);
        }

        $userAnswers = $data['answers']; // Format: { "question_id": "A", "question_id_2": "B" }
        $score = 0;
        $details = [];

        foreach ($quiz->getQuestions() as $question) {
            $qid = $question->getId();
            $userAnswer = $userAnswers[$qid] ?? null;
            $isCorrect = false;

            if ($userAnswer === $question->getBonneReponse()) {
                $score += $question->getPoints();
                $isCorrect = true;
            }

            $details[$qid] = [
                'submitted' => $userAnswer,
                'correct' => $isCorrect,
                'correctAnswer' => $question->getBonneReponse() // Optional: reveal answer
            ];
        }

        // Save result if user is logged in
        $user = $this->getUser();
        if ($user) {
            $resultat = new Resultat();
            $resultat->setQuiz($quiz);
            $resultat->setEtudiant($user);
            $resultat->setScore($score);
            $resultat->setNoteMax($quiz->getNoteMax() ?? 0);
            $resultat->setDatePassage(new \DateTime());
            
            $entityManager->persist($resultat);
            $entityManager->flush();
        }

        return $this->json([
            'quizId' => $quiz->getId(),
            'score' => $score,
            'maxScore' => $quiz->getNoteMax(),
            'details' => $details,
            'saved' => (bool)$user
        ]);
    }
}
