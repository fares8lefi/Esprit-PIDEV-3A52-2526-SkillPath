<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    public function notifyNewContent(string $type, string $title): void
    {
        // En Symfony, ROLE_USER est souvent suffisant pour cibler les étudiants
        // Si les rôles sont stockés differemment, adapter ici.
        // Dans User.php, on voit: $roles[] = 'ROLE_USER'; (tous les users)
        // On va assumer que tous les utilisateurs actifs doivent être informés.
        
        $users = $this->userRepository->findAll();
        $message = sprintf("Un nouveau %s a été ajouté : %s", $type, $title);

        foreach ($users as $user) {
            $notification = new Notification();
            $notification->setMessage($message);
            $notification->setUser($user);
            $this->entityManager->persist($notification);
        }

        $this->entityManager->flush();
    }
}
