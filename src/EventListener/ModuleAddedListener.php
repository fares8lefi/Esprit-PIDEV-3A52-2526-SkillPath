<?php

namespace App\EventListener;

use App\Entity\Module;
use App\Service\NotificationService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;

#[AsDoctrineListener(event: Events::postPersist)]
class ModuleAddedListener
{
    private $notificationService;
    private $logger;

    public function __construct(NotificationService $notificationService, LoggerInterface $logger)
    {
        $this->notificationService = $notificationService;
        $this->logger = $logger;
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $module = $args->getObject();
        if (!$module instanceof Module) {
            return;
        }

        $this->logger->info("ModuleAddedListener: Triggered for module " . $module->getId());

        $course = $module->getCourse();
        if (!$course) {
            $this->logger->warning("ModuleAddedListener: No course found for module " . $module->getId());
            return;
        }

        $users = $course->getUsers();
        $this->logger->info("ModuleAddedListener: Found " . count($users) . " users for course " . $course->getId());

        if (count($users) === 0) {
            return;
        }

        $title = "Nouveau module ajouté !";
        $message = sprintf(
            'Un nouveau module intitulé "%s" a été ajouté au cours "%s".',
            $module->getTitle(),
            $course->getTitle()
        );
        
        $link = "/course/" . $course->getId(); 

        try {
            $this->notificationService->notifyUsers($users, $title, $message, $link, $module->getCreatedBy());
            $this->logger->info("ModuleAddedListener: Notifications sent successfully");
        } catch (\Exception $e) {
            $this->logger->error("ModuleAddedListener: Error sending notifications: " . $e->getMessage());
        }
    }
}
