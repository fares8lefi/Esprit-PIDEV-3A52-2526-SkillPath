<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param User[]|iterable $users
     */
    public function notifyUsers(iterable $users, string $title, string $message, ?string $link = null): void
    {
        foreach ($users as $user) {
            $notification = new Notification();
            $notification->setUser($user);
            $notification->setTitle($title);
            $notification->setMessage($message);
            $notification->setLink($link);
            
            $this->entityManager->persist($notification);
        }

        $this->entityManager->flush();
    }
}
