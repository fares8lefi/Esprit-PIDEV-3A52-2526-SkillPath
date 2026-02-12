<?php

namespace App\Controller\FrontOffice;

use App\Entity\Resultat;
use App\Entity\Quiz;
use App\Repository\QuizRepository;
use App\Repository\ResultatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/quiz')]
class QuizController extends AbstractController
{
    #[Route('/', name: 'app_front_office_quiz_index', methods: ['GET'])]
    public function index(Request $request, QuizRepository $quizRepository, HttpClientInterface $httpClient): Response
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort');

        $quizzes = $quizRepository->searchAndSort($search, $sort);

        // API Integration: Fetch a random educational fact
        $fallbacks = [
            "La persévérance est la clé de la réussite en programmation !",
            "Chaque erreur est une opportunité d'apprendre quelque chose de nouveau.",
            "Le code propre est un code qui semble avoir été écrit par quelqu'un qui s'en soucie.",
            "L'apprentissage continu est la seule voie vers l'excellence."
        ];
        $funFact = $fallbacks[array_rand($fallbacks)]; 

        try {
            $response = $httpClient->request('GET', 'https://numbersapi.com/random/trivia?json&t=' . time(), [
                'timeout' => 2,
                'verify_peer' => false,
            ]);
            
            if ($response->getStatusCode() === 200) {
                $content = $response->toArray();
                $funFact = $content['text'] ?? $funFact;
            }
        } catch (\Exception $e) {
            // Fallback remains active
        }

        return $this->render('FrontOffice/quiz/index.html.twig', [
            'quizzes' => $quizzes,
            'current_search' => $search,
            'current_sort' => $sort,
            'fun_fact' => $funFact
        ]);
    }

    #[Route('/history', name: 'app_front_office_quiz_history', methods: ['GET'])]
    public function history(ResultatRepository $resultatRepository): Response
    {
        $user = $this->getUser();
        $resultats = [];

        if ($user) {
            $resultats = $resultatRepository->findBy(
                ['etudiant' => $user],
                ['datePassage' => 'DESC']
            );
        }

        return $this->render('FrontOffice/quiz/history.html.twig', [
            'resultats' => $resultats,
        ]);
    }

    #[Route('/{id}/take', name: 'app_front_office_quiz_take', methods: ['GET', 'POST'])]
    public function take(Request $request, Quiz $quiz, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $score = 0;
            $questions = $quiz->getQuestions();
            $data = $request->request->all();
            $userAnswers = [];

            foreach ($questions as $question) {
                $fieldName = 'question_' . $question->getId();
                $userAnswer = $data[$fieldName] ?? null;
                $userAnswers[$question->getId()] = $userAnswer;

                if ($userAnswer && $userAnswer === $question->getBonneReponse()) {
                    $score += $question->getPoints();
                }
            }

            $user = $this->getUser();

            if ($user && method_exists($user, 'getId')) {
                $resultat = new Resultat();
                $resultat->setQuiz($quiz);
                $resultat->setEtudiant($user);
                $resultat->setScore($score);
                $resultat->setNoteMax($quiz->getNoteMax() ?? 0);
                $resultat->setDatePassage(new \DateTime());

                try {
                    $entityManager->persist($resultat);
                    $entityManager->flush();
                } catch (\Exception $e) {
                    // Log error but continue
                }
            }

            // Store answers in session for review
            $request->getSession()->set('quiz_answers_' . $quiz->getId(), $userAnswers);

            return $this->redirectToRoute('app_front_office_quiz_result', [
                'id' => $quiz->getId(),
                'score' => $score,
                'totalPoints' => $quiz->getNoteMax() ?? 0
            ]);
        }

        return $this->render('FrontOffice/quiz/take.html.twig', [
            'quiz' => $quiz,
        ]);
    }

    #[Route('/{id}/result', name: 'app_front_office_quiz_result', methods: ['GET'])]
    public function result(Quiz $quiz, Request $request, HttpClientInterface $httpClient): Response
    {
        $score = $request->query->get('score', 0);
        $totalPoints = $request->query->get('totalPoints', 0);

        // Retrieve user answers from session for review
        $userAnswers = $request->getSession()->get('quiz_answers_' . $quiz->getId(), []);

        // Fun Fact Logic
        $fallbacks = [
            "La persévérance est la clé de la réussite en programmation !",
            "Chaque erreur est une opportunité d'apprendre quelque chose de nouveau.",
            "Le code propre est un code qui semble avoir été écrit par quelqu'un qui s'en soucie.",
            "L'apprentissage continu est la seule voie vers l'excellence."
        ];
        $funFact = $fallbacks[array_rand($fallbacks)]; 

        try {
            $response = $httpClient->request('GET', 'https://numbersapi.com/random/trivia?json&t=' . time(), [
                'timeout' => 2,
                'verify_peer' => false,
            ]);
            
            if ($response->getStatusCode() === 200) {
                $content = $response->toArray();
                $funFact = $content['text'] ?? $funFact;
            }
        } catch (\Exception $e) {
            // Fallback remains active
        }

        return $this->render('FrontOffice/quiz/result.html.twig', [
            'quiz' => $quiz,
            'score' => $score,
            'totalPoints' => $totalPoints,
            'userAnswers' => $userAnswers,
            'funFact' => $funFact,
        ]);
    }
}
