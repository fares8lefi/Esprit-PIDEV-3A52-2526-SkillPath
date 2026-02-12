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

#[Route('/admin/question')]
class QuestionController extends AbstractController
{
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
