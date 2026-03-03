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
use Endroid\QrCode\Writer\SvgWriter;
use Psr\Log\LoggerInterface;

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
    public function join(Event $event, EntityManagerInterface $entityManager, Request $request, MailerInterface $mailer, BuilderInterface $customQrCodeBuilder, LoggerInterface $logger): Response
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
            
            // Build the QR Code Billet (may fail if GD extension is missing)
            $data = sprintf(
                "Billet SkillPath\nÉvénement: %s\nDate: %s\nParticipant: %s\nEmail: %s",
                $event->getTitle(),
                $event->getEventDate()->format('d/m/Y'),
                $user->getUsername(),
                $user->getEmail()
            );

            $ticketContent = null;
            $ticketMime = null;
            $ticketFilename = null;
            try {
                $usePng = extension_loaded('gd');
                $writer = $usePng ? new PngWriter() : new SvgWriter();

                $qrResult = $customQrCodeBuilder->build(
                    data: $data,
                    encoding: new Encoding('UTF-8'),
                    errorCorrectionLevel: ErrorCorrectionLevel::High,
                    size: 300,
                    margin: 10,
                    writer: $writer
                );

                $ticketContent = $qrResult->getString();
                if ($usePng) {
                    $ticketMime = 'image/png';
                    $ticketFilename = 'billet-' . $event->getId() . '.png';
                } else {
                    $ticketMime = 'image/svg+xml';
                    $ticketFilename = 'billet-' . $event->getId() . '.svg';
                }
            } catch (\Throwable $e) {
                $logger->warning('QR generation failed', ['exception' => $e->getMessage(), 'event' => $event->getId(), 'user' => $user->getEmail()]);
                // Fail gracefully if image generation is not possible
                $this->addFlash('warning', 'Billet non généré (problème de génération d\'image).');
            }

            // Send Confirmation Email
            $fromAddress = getenv('MAILER_FROM') ?: ($_ENV['MAILER_FROM'] ?? null) ?: 'skillPathdonotreply@gmail.com';

            $email = (new TemplatedEmail())
                ->from(new Address($fromAddress, 'SkillPath Events'))
                ->to($user->getEmail())
                ->subject('Confirmation d\'inscription : ' . $event->getTitle())
                ->htmlTemplate('emails/event_registration.html.twig')
                ->context([
                    'event' => $event,
                    'user' => $user,
                ]);

            if ($ticketContent !== null) {
                $email->attach($ticketContent, $ticketFilename, $ticketMime);
            }

            try {
                $mailer->send($email);
                if ($ticketContent !== null) {
                    $this->addFlash('success', 'Inscription réussie ! Un email avec votre billet vous a été envoyé.');
                } else {
                    $this->addFlash('success', 'Inscription réussie ! L\'email a été envoyé sans billet.');
                }
            } catch (\Throwable $e) {
                $logger->error('Failed to send registration email', ['exception' => $e->getMessage(), 'user' => $user->getEmail(), 'event' => $event->getId()]);
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

        // Use PNG when GD is available, otherwise fall back to SVG
        $usePng = extension_loaded('gd');
        $writer = $usePng ? new PngWriter() : new SvgWriter();

        $result = $customQrCodeBuilder->build(
            data: $data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
            writer: $writer
        );

        $content = $result->getString();
        $mime = $usePng ? 'image/png' : 'image/svg+xml';
        $filename = 'billet-' . $event->getId() . ($usePng ? '.png' : '.svg');

        return new Response($content, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
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
