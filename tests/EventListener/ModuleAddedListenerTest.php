<?php

namespace App\Tests\EventListener;

use App\Entity\Course;
use App\Entity\Module;
use App\Entity\User;
use App\EventListener\ModuleAddedListener;
use App\Service\NotificationService;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ModuleAddedListenerTest extends TestCase
{
    private $notificationService;
    private $logger;
    private $listener;

    protected function setUp(): void
    {
        $this->notificationService = $this->createMock(NotificationService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->listener = new ModuleAddedListener($this->notificationService, $this->logger);
    }

    public function testPostPersistSendsNotifications(): void
    {
        $user = $this->createMock(User::class);
        $course = $this->createMock(Course::class);
        $course->method('getUsers')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$user]));
        $course->method('getTitle')->willReturn('Symfony Course');
        
        $module = $this->createMock(Module::class);
        $module->method('getCourse')->willReturn($course);
        $module->method('getTitle')->willReturn('New Lesson');
        $module->method('getCreatedBy')->willReturn($this->createMock(User::class));
        
        $args = $this->createMock(LifecycleEventArgs::class);
        $args->method('getObject')->willReturn($module);
        
        $this->notificationService->expects($this->once())
            ->method('notifyUsers');
            
        $this->listener->postPersist($args);
    }

    public function testPostPersistSkipsNonModule(): void
    {
        $args = $this->createMock(LifecycleEventArgs::class);
        $args->method('getObject')->willReturn(new \stdClass());
        
        $this->notificationService->expects($this->never())
            ->method('notifyUsers');
            
        $this->listener->postPersist($args);
    }
}
