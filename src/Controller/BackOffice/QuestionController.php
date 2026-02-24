<?php

namespace App\Controller\BackOffice;

use App\Entity\Question;
use App\Entity\Quiz;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/admin/question')]
class QuestionController extends AbstractController
{
    #[Route('/import/{id_quiz}', name: 'app_back_office_question_import', methods: ['POST'])]
    public function importApi(Request $request, int $id_quiz, QuizRepository $quizRepository, EntityManagerInterface $entityManager, HttpClientInterface $httpClient): Response
    {
        $quiz = $quizRepository->find($id_quiz);
        if (!$quiz) {
            throw $this->createNotFoundException('Quiz not found');
        }

        if (!$this->isCsrfTokenValid('import_api'.$quiz->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('app_back_office_quiz_show', ['id' => $quiz->getId()]);
        }

        try {
            // Import 5 multiple choice questions
            $response = $httpClient->request('GET', 'https://opentdb.com/api.php?amount=5&type=multiple');
            $data = $response->toArray();

            if (isset($data['results']) && is_array($data['results'])) {
                $count = 0;
                foreach ($data['results'] as $item) {
                    $questionEntity = new Question();
                    $questionEntity->setQuiz($quiz);
                    // OpenTDB returns HTML entities, decode them.
                    $questionEntity->setEnonce(html_entity_decode($item['question'], ENT_QUOTES | ENT_HTML5));
                    $questionEntity->setPoints(5); // Default points

                    $correctAnswer = html_entity_decode($item['correct_answer'], ENT_QUOTES | ENT_HTML5);
                    $incorrectAnswers = array_map(function($ans) {
                        return html_entity_decode($ans, ENT_QUOTES | ENT_HTML5);
                    }, $item['incorrect_answers']);

                    // Combine and shuffle
                    $allAnswers = array_merge([$correctAnswer], $incorrectAnswers);
                    shuffle($allAnswers);

                    // Assign to A, B, C, D
                    $letters = ['A', 'B', 'C', 'D'];
                    $bonneReponse = 'A';

                    foreach ($allAnswers as $index => $ans) {
                        $letter = $letters[$index];
                        if ($ans === $correctAnswer) {
                            $bonneReponse = $letter;
                        }

                        if ($letter === 'A') $questionEntity->setChoixA($ans);
                        elseif ($letter === 'B') $questionEntity->setChoixB($ans);
                        elseif ($letter === 'C') $questionEntity->setChoixC($ans);
                        elseif ($letter === 'D') $questionEntity->setChoixD($ans);
                    }

                    $questionEntity->setBonneReponse($bonneReponse);
                    $entityManager->persist($questionEntity);
                    $count++;
                }

                $entityManager->flush();
                $this->addFlash('success', "$count vecteurs importés depuis l'API publique avec succès.");
            } else {
                $this->addFlash('error', "Format de réponse de l'API invalide.");
            }
        } catch (\Exception $e) {
            $this->addFlash('error', "Erreur lors de l'importation depuis l'API : " . $e->getMessage());
        }

        return $this->redirectToRoute('app_back_office_quiz_show', ['id' => $quiz->getId()]);
    }
    #[Route('/new/{id_quiz}', name: 'app_back_office_question_new', methods: ['GET', 'POST'])]
    public function new(Request $request, int $id_quiz, QuizRepository $quizRepository, EntityManagerInterface $entityManager): Response
    {
        $quiz = $quizRepository->find($id_quiz);
        if (!$quiz) {
            throw $this->createNotFoundException('Quiz not found');
        }

        $question = new Question();
        $question->setQuiz($quiz);
        $form = $this->createForm(QuestionType::class, $question);
        
        // Remove quiz field if we are adding to a specific quiz context to avoid confusion
        $form->remove('quiz');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($question);
            $entityManager->flush();

            return $this->redirectToRoute('app_back_office_quiz_show', ['id' => $quiz->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('BackOffice/question/new.html.twig', [
            'question' => $question,
            'form' => $form,
            'quiz' => $quiz
        ]);
    }

    #[Route('/{id}/edit', name: 'app_back_office_question_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Question $question, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(QuestionType::class, $question);
        $form->remove('quiz');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_back_office_quiz_show', ['id' => $question->getQuiz()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('BackOffice/question/edit.html.twig', [
            'question' => $question,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_back_office_question_delete', methods: ['POST'])]
    public function delete(Request $request, Question $question, EntityManagerInterface $entityManager): Response
    {
        $quizId = $question->getQuiz()->getId();
        if ($this->isCsrfTokenValid('delete'.$question->getId(), $request->request->get('_token'))) {
            $entityManager->remove($question);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_back_office_quiz_show', ['id' => $quizId], Response::HTTP_SEE_OTHER);
    }
}
