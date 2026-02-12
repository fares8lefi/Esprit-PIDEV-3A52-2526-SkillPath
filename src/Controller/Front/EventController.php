<?php

namespace App\Controller\Front;

use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

#[Route('/event', name: 'app_event_')]
class EventController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        // Get upcoming events
        $events = $eventRepository->createQueryBuilder('e')
            ->where('e.eventDate >= :today')
            ->setParameter('today', new \DateTime('today'))
            ->orderBy('e.eventDate', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('Front/event/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/my-events', name: 'my_events', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myEvents(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('Front/event/my_events.html.twig', [
            'joinedEvents' => $user->getJoinedEvents(),
            'favoriteEvents' => $user->getFavoriteEvents(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('Front/event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/join', name: 'join', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function join(Event $event, EntityManagerInterface $entityManager, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->getJoinedEvents()->contains($event)) {
            $user->removeJoinedEvent($event);
            $this->addFlash('success', 'Vous ne participez plus à cet événement.');
        } else {
            // Check capacity if needed - for now just add
            if ($event->getLocation() && $event->getParticipants()->count() >= $event->getLocation()->getMaxCapacity()) {
                $this->addFlash('error', 'Désolé, cet événement est complet.');
                return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
            }
            
            $user->addJoinedEvent($event);
            $this->addFlash('success', 'Inscription réussie !');
        }

        $entityManager->flush();

        // Redirect back to where the user came from
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('app_event_show', ['id' => $event->getId()]));
    }

    #[Route('/{id}/favorite', name: 'favorite', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function favorite(Event $event, EntityManagerInterface $entityManager, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->getFavoriteEvents()->contains($event)) {
            $user->removeFavoriteEvent($event);
            //$this->addFlash('success', 'Retiré des favoris.');
        } else {
            $user->addFavoriteEvent($event);
            //$this->addFlash('success', 'Ajouté aux favoris !');
        }

        $entityManager->flush();

        return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('app_event_show', ['id' => $event->getId()]));
    }

    #[Route('/{id}/qrcode', name: 'qrcode', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function downloadQrCode(Event $event, BuilderInterface $customQrCodeBuilder): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Ensure user is joined
        if (!$user->getJoinedEvents()->contains($event)) {
            throw $this->createAccessDeniedException('Vous devez être inscrit à cet événement pour télécharger le billet.');
        }

        $data = sprintf(
            "Billet SkillPath\nÉvénement: %s\nDate: %s\nParticipant: %s\nEmail: %s",
            $event->getTitle(),
            $event->getEventDate()->format('d/m/Y'),
            $user->getUsername(),
            $user->getEmail()
        );

        $result = $customQrCodeBuilder->build(
            data: $data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
            writer: new PngWriter()
        );

        return new Response($result->getString(), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="billet-' . $event->getId() . '.png"'
        ]);
    }
}
