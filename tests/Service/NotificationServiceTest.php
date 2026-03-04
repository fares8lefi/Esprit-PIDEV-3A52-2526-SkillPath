<?php

namespace App\Tests\Service;

use App\Entity\Notification;
use App\Entity\User;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class NotificationServiceTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject&EntityManagerInterface $entityManager;
    private \PHPUnit\Framework\MockObject\MockObject&Security $security;
    private NotificationService $notificationService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->notificationService = new NotificationService($this->entityManager, $this->security);
    }

    public function testNotifyUsers(): void
    {
        $user1 = $this->createMock(User::class);
        $creator = $this->createMock(User::class);
        
        $this->security->method('getUser')->willReturn($creator);
        
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Notification::class));
            
        $this->entityManager->expects($this->once())
            ->method('flush');
            
        $this->notificationService->notifyUsers([$user1], 'Title', 'Message');
    }

    public function testNotifyUsersWithCreatorFallback(): void
    {
        $user1 = $this->createMock(User::class);
        $this->security->method('getUser')->willReturn(null);
        
        $admin = $this->createMock(User::class);
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn($admin);
        
        $this->entityManager->method('getRepository')->with(User::class)->willReturn($repo);
        
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Notification::class));
            
        $this->notificationService->notifyUsers([$user1], 'Title', 'Message');
    }
}
