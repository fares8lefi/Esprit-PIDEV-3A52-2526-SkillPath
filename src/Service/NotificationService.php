<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class NotificationService
{
    private EntityManagerInterface $entityManager;
    private MailerInterface $mailer;
    private UserRepository $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        UserRepository $userRepository
    ) {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
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
            // 1. Enregistrement en base de données
            $notification = new Notification();
            $notification->setMessage($message);
            $notification->setUser($user);
            $this->entityManager->persist($notification);

            // 2. Envoi d'email
            $this->sendEmail($user->getEmail(), $message);
        }

        $this->entityManager->flush();
    }

    private function sendEmail(string $recipientEmail, string $message): void
    {
        $email = (new Email())
            ->from('noreply@skillpath.com')
            ->to($recipientEmail)
            ->subject('Nouvelle mise à jour sur SkillPath')
            ->text($message)
            ->html("<p>$message</p>");

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log error or handle it (ignoring for now to not block process)
        }
    }
}
