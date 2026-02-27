<?php

namespace App\Controller\Front;

use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\GeminiTranslatorService;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

#[Route('/event', name: 'app_event_')]
class EventController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request, EventRepository $eventRepository, \App\Repository\LocationRepository $locationRepository): Response
    {
        $search = $request->query->get('q');
        $rating = $request->query->get('rating') ? (int)$request->query->get('rating') : null;
        $locationId = $request->query->get('location') ? (int)$request->query->get('location') : null;

        $events = $eventRepository->findByFilters($search, $rating, $locationId);

        return $this->render('FrontOffice/event/index.html.twig', [
            'events' => $events,
            'locations' => $locationRepository->findAll(),
            'currentSearch' => $search,
            'currentRating' => $rating,
            'currentLocation' => $locationId,
        ]);
    }

    #[Route('/my-events', name: 'my_events', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myEvents(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('FrontOffice/event/my_events.html.twig', [
            'joinedEvents' => $user->getJoinedEvents(),
            'favoriteEvents' => $user->getFavoriteEvents(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('FrontOffice/event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/join', name: 'join', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function join(Event $event, EntityManagerInterface $entityManager, Request $request, MailerInterface $mailer, BuilderInterface $customQrCodeBuilder): Response
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
            
            // Build the QR Code Billet
            $data = sprintf(
                "Billet SkillPath\nÉvénement: %s\nDate: %s\nParticipant: %s\nEmail: %s",
                $event->getTitle(),
                $event->getEventDate()->format('d/m/Y'),
                $user->getUsername(),
                $user->getEmail()
            );

            $qrResult = $customQrCodeBuilder->build(
                data: $data,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 300,
                margin: 10,
                writer: new PngWriter()
            );
            $ticketPngContent = $qrResult->getString();

            // Send Confirmation Email
            $email = (new TemplatedEmail())
                ->from(new Address('bizbiz1478@gmail.com', 'SkillPath Events'))
                ->to($user->getEmail())
                ->subject('Confirmation d\'inscription : ' . $event->getTitle())
                ->htmlTemplate('emails/event_registration.html.twig')
                ->context([
                    'event' => $event,
                    'user' => $user,
                ])
                ->attach($ticketPngContent, 'billet-' . $event->getId() . '.png', 'image/png');

            try {
                $mailer->send($email);
                $this->addFlash('success', 'Inscription réussie ! Un email avec votre billet vous a été envoyé.');
            } catch (\Exception $e) {
                // Ignore silent mailer drops for local usage if needed, but alert user
                $this->addFlash('error', 'Inscription réussie mais l\'envoi de l\'email a échoué.');
            }
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

    #[Route('/{id}/rate', name: 'rate', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function rate(Event $event, Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $score = $request->request->get('rating');

        if ($score && is_numeric($score) && $score >= 1 && $score <= 5) {
            $existingRating = $entityManager->getRepository(\App\Entity\EventRating::class)
                ->findOneBy(['event' => $event, 'user' => $user]);

            if ($existingRating) {
                $existingRating->setScore((int)$score);
            } else {
                $rating = new \App\Entity\EventRating();
                $rating->setUser($user);
                $rating->setScore((int)$score);
                $event->addRating($rating);
                $entityManager->persist($rating);
            }

            $event->updateAverageRating();
            $entityManager->flush();
            $this->addFlash('success', 'Merci pour votre évaluation !');
        } else {
            $this->addFlash('error', 'Note invalide.');
        }

        return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('app_event_show', ['id' => $event->getId()]));
    }

    #[Route('/{id}/translate/{lang}', name: 'translate', methods: ['GET'], requirements: ['lang' => 'en|ar'])]
    public function translate(Event $event, string $lang, GeminiTranslatorService $translatorService): JsonResponse
    {
        $translation = $translatorService->translateEvent($event, $lang);

        return new JsonResponse($translation);
    }
}
