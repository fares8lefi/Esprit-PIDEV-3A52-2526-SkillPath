<?php

namespace App\Controller\Front;

use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/notifications', name: 'app_notifications_')]
class NotificationController extends AbstractController
{
    #[Route('/unread-count', name: 'unread_count', methods: ['GET'])]
    public function getUnreadCount(NotificationRepository $repository): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['count' => 0]);
        }

        return new JsonResponse(['count' => $repository->countUnreadByUser($user)]);
    }

    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(NotificationRepository $repository): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['notifications' => []]);
        }

        $notifications = $repository->findBy(['user' => $user], ['createdAt' => 'DESC'], 5);
        $data = [];
        foreach ($notifications as $n) {
            $data[] = [
                'id' => $n->getId(),
                'title' => $n->getTitle(),
                'message' => $n->getMessage(),
                'createdAt' => $n->getCreatedAt()->format('d/m H:i'),
                'isRead' => $n->isRead(),
                'link' => $n->getLink()
            ];
        }

        return new JsonResponse(['notifications' => $data]);
    }

    #[Route('/mark-as-read/{id}', name: 'mark_as_read', methods: ['POST'])]
    public function markAsRead(int $id, NotificationRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $notification = $repository->find($id);
        if (!$notification || $notification->getUser() !== $this->getUser()) {
            return new JsonResponse(['success' => false], 404);
        }

        $notification->setIsRead(true);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }
}
