<?php

namespace App\Controller\FrontOffice;

use App\Entity\Resultat;
use App\Entity\Quiz;
use App\Repository\QuizRepository;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/quiz')]
class QuizController extends AbstractController
{
    #[Route('/', name: 'app_front_office_quiz_index', methods: ['GET'])]
    public function index(QuizRepository $quizRepository): Response
    {
        return $this->render('FrontOffice/quiz/index.html.twig', [
            'quizzes' => $quizRepository->findAll(),
        ]);
    }

    #[Route('/{id}/take', name: 'app_front_office_quiz_take', methods: ['GET', 'POST'])]
    public function take(Request $request, Quiz $quiz, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $score = 0;
            $questions = $quiz->getQuestions();
            $data = $request->request->all();

            foreach ($questions as $question) {
                $fieldName = 'question_' . $question->getId();
                if (isset($data[$fieldName])) {
                    $userAnswer = $data[$fieldName];
                    if ($userAnswer === $question->getBonneReponse()) {
                        $score += $question->getPoints();
                    }
                }
            }

            $user = $this->getUser();
            $resultat = null;

            // Only attempt to save if we have a valid user
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
    public function result(Quiz $quiz, Request $request): Response
    {
        $score = $request->query->get('score', 0);
        $totalPoints = $request->query->get('totalPoints', 0);

        return $this->render('FrontOffice/quiz/result.html.twig', [
            'quiz' => $quiz,
            'score' => $score,
            'totalPoints' => $totalPoints
        ]);
    }
}
