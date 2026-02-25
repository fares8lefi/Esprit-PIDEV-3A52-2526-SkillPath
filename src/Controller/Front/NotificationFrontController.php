<?php

namespace App\Controller\Front;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/front/notifications', name: 'front_notifications_')]
class NotificationFrontController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(NotificationRepository $notificationRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_user_login');
        }

        $notifications = $notificationRepository->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('front/notifications/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    #[Route('/{id}/read', name: 'mark_read', methods: ['POST'])]
    public function markAsRead(
        Notification $notification,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        if (!$user || $notification->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('mark_notification' . $notification->getId(), (string) $request->request->get('_token'))) {
            $notification->setRead(true);
            $entityManager->flush();
        }

        return $this->redirectToRoute('front_notifications_index');
    }

    #[Route('/read-all', name: 'mark_all_read', methods: ['POST'])]
    public function markAllRead(
        NotificationRepository $notificationRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_user_login');
        }

        if ($this->isCsrfTokenValid('mark_all_notifications', (string) $request->request->get('_token'))) {
            $notifications = $notificationRepository->findBy(
                ['user' => $user, 'isRead' => false],
                ['createdAt' => 'DESC']
            );

            foreach ($notifications as $notification) {
                $notification->setRead(true);
            }

            $entityManager->flush();
        }

        return $this->redirectToRoute('front_notifications_index');
    }
}

