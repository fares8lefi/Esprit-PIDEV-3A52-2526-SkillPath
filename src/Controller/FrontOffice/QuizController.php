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
use Knp\Component\Pager\PaginatorInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/quiz')]
class QuizController extends AbstractController
{
    #[Route('/', name: 'app_front_office_quiz_index', methods: ['GET'])]
    #[Route('/index', name: 'app_front_office_quiz_index_alias', methods: ['GET'])]
    public function index(Request $request, QuizRepository $quizRepository, HttpClientInterface $httpClient, PaginatorInterface $paginator): Response
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('tri');

        $queryBuilder = $quizRepository->searchAndSortQuery($search, $sort);

        $quizzes = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            6 // 6 quizzes per page
        );

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
    public function history(Request $request, ResultatRepository $resultatRepository, PaginatorInterface $paginator): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('warning', 'Vous devez être connecté pour voir votre historique.');
            return $this->redirectToRoute('app_user_login');
        }

        $queryBuilder = $resultatRepository->createQueryBuilder('r')
                ->where('r.etudiant = :user')
                ->setParameter('user', $user)
                ->orderBy('r.datePassage', 'DESC');

        $resultats = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            5 // 5 results per page
        );

        return $this->render('FrontOffice/quiz/history.html.twig', [
            'resultats' => $resultats,
        ]);
    }

    #[Route('/{id}/certificat', name: 'app_quiz_certificat')]
    public function generateCertificat(Resultat $resultat): Response
    {
        // Require the student to have passed
        $score = $resultat->getScore();
        $total = $resultat->getNoteMax();
        $percentage = $total > 0 ? ($score / $total) * 100 : 0;
        
        if ($percentage < 50) {
            $this->addFlash('error', 'Vous devez réussir le quiz pour obtenir un certificat.');
            return $this->redirectToRoute('app_front_office_quiz_history');
        }

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);
        
        $html = $this->renderView('FrontOffice/quiz/certificat.html.twig', [
            'resultat' => $resultat
        ]);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="certificat_quiz_' . $resultat->getQuiz()->getTitre() . '.pdf"'
            ]
        );
    }

    #[Route('/{id}/adaptive', name: 'app_front_office_quiz_adaptive', methods: ['GET', 'POST'])]
    public function adaptive(Request $request, Quiz $quiz, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Vous devez être connecté pour passer un quiz.');
            return $this->redirectToRoute('app_user_login');
        }

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
                    $this->addFlash('error', 'Erreur lors de la sauvegarde de votre résultat : ' . $e->getMessage());
                }
            }

            // Store answers in session for review
            $request->getSession()->set('quiz_answers_' . $quiz->getId(), $userAnswers);

            return $this->redirectToRoute('app_front_office_quiz_result', [
                'id' => $quiz->getId(),
                'score' => $score,
                'totalPoints' => $quiz->getNoteMax() ?? 0,
                'resultatId' => isset($resultat) && $resultat->getId() ? $resultat->getId() : null
            ]);
        }

        return $this->render('FrontOffice/quiz/take_adaptive.html.twig', [
            'quiz' => $quiz,
        ]);
    }

    #[Route('/{id}/take', name: 'app_front_office_quiz_take', methods: ['GET', 'POST'])]
    public function take(Request $request, Quiz $quiz, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Vous devez être connecté pour passer un quiz.');
            return $this->redirectToRoute('app_user_login');
        }

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
                    
                    // Synchronisation avec UserCourseView pour l'IA
                    $course = $quiz->getCourse();
                    if ($course) {
                        $viewRepository = $entityManager->getRepository(\App\Entity\UserCourseView::class);
                        $view = $viewRepository->findByUserAndCourse($user->getId(), $course->getId());
                        
                        if ($view) {
                            $view->setQuizScore($score);
                            $entityManager->persist($view);
                        }
                    }

                    $entityManager->flush();
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de la sauvegarde de votre résultat : ' . $e->getMessage());
                }
            }

            // Store answers in session for review
            $request->getSession()->set('quiz_answers_' . $quiz->getId(), $userAnswers);

            return $this->redirectToRoute('app_front_office_quiz_result', [
                'id' => $quiz->getId(),
                'score' => $score,
                'totalPoints' => $quiz->getNoteMax() ?? 0,
                'resultatId' => isset($resultat) && $resultat->getId() ? $resultat->getId() : null
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
        $resultatId = $request->query->get('resultatId', null);

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
            'resultatId' => $resultatId,
            'userAnswers' => $userAnswers,
            'funFact' => $funFact,
        ]);
    }
}
