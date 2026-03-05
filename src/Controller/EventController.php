<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Service\AiTextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/admin/event', name: 'admin_event_')]
class EventController extends AbstractController
{
    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findBy([], ['eventDate' => 'DESC']);

        return $this->render('BackOffice/event/list.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/improve-description', name: 'improve_description', methods: ['POST'])]
    public function improveDescription(Request $request, AiTextService $aiTextService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $text = trim($data['description'] ?? '');

        if (empty($text)) {
            return $this->json(['error' => 'La description est vide.'], 400);
        }

        try {
            $improved = $aiTextService->improveText($text);
            return $this->json(['improved' => $improved]);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Erreur IA : ' . $e->getMessage()], 502);
        }
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // VichUploaderBundle will handle the file upload automatically

            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'L\'événement a été créé avec succès.');
            return $this->redirectToRoute('admin_event_list');
        }

        return $this->render('BackOffice/event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Event $event): Response
    {
        return $this->render('BackOffice/event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // VichUploaderBundle will handle the file upload automatically

            $entityManager->flush();
            $this->addFlash('success', 'L\'événement a été modifié avec succès.');
            return $this->redirectToRoute('admin_event_list');
        }

        return $this->render('BackOffice/event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($event);
                $entityManager->flush();
                $this->addFlash('success', 'L\'événement a été supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('admin_event_list');
    }
}
