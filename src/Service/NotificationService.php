<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    private EntityManagerInterface $entityManager;
    private \Symfony\Bundle\SecurityBundle\Security $security;

    public function __construct(EntityManagerInterface $entityManager, \Symfony\Bundle\SecurityBundle\Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * @param User[]|iterable $users
     */
    public function notifyUsers(iterable $users, string $title, string $message, ?string $link = null, ?User $creator = null): void
    {
        $creator = $creator ?? $this->security->getUser();
        
        // If still null, we might have a problem because Notification now requires a creator.
        // We'll handle this by assuming there's at least ONE admin in the DB to act as default if needed,
        // but for now let's hope a creator is always available.
        if (!$creator instanceof User) {
            // Fallback for system notifications: find the first admin
            $creator = $this->entityManager->getRepository(User::class)->findOneBy(['role' => 'admin']);
        }

        foreach ($users as $user) {
            $notification = new Notification($creator);
            $notification->setUser($user);
            $notification->setTitle($title);
            $notification->setMessage($message);
            $notification->setLink($link);
            
            $this->entityManager->persist($notification);
        }

        $this->entityManager->flush();
    }
}
