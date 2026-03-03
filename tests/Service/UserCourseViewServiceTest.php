<?php

namespace App\Tests\Service;

use App\Entity\Course;
use App\Entity\User;
use App\Entity\UserCourseView;
use App\Repository\UserCourseViewRepository;
use App\Service\AIService;
use App\Service\UserCourseViewService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\UuidV7;

class UserCourseViewServiceTest extends TestCase
{
    private $entityManager;
    private $repository;
    private $aiService;
    private $userCourseViewService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(UserCourseViewRepository::class);
        $this->aiService = $this->createMock(AIService::class);
        
        $this->userCourseViewService = new UserCourseViewService(
            $this->entityManager,
            $this->repository,
            $this->aiService
        );
    }

    public function testRecordViewCreatesNewView(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(new UuidV7());
        $course = $this->createMock(Course::class);
        $course->method('getId')->willReturn(1);
        
        $this->repository->method('findByUserAndCourse')->willReturn(null);
        
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(UserCourseView::class));
            
        $view = $this->userCourseViewService->recordView($user, $course);
        
        $this->assertSame($user, $view->getUser());
        $this->assertSame($course, $view->getCourse());
    }

    public function testCalculateEngagementLevel(): void
    {
        $view = new UserCourseView();
        
        $view->setTimeSpent(5);
        $this->userCourseViewService->calculateEngagementLevel($view);
        $this->assertEquals('low', $view->getEngagementLevel());
        
        $view->setTimeSpent(20);
        $this->userCourseViewService->calculateEngagementLevel($view);
        $this->assertEquals('medium', $view->getEngagementLevel());
        
        $view->setTimeSpent(50);
        $this->userCourseViewService->calculateEngagementLevel($view);
        $this->assertEquals('high', $view->getEngagementLevel());
    }

    public function testEnrollUser(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(new UuidV7());
        $user->method('getCourses')->willReturn(new \Doctrine\Common\Collections\ArrayCollection());
        
        $course = $this->createMock(Course::class);
        $course->method('getId')->willReturn(1);
        
        $view = new UserCourseView();
        $this->repository->method('findByUserAndCourse')->willReturn($view);
        
        $this->userCourseViewService->enrollUser($user, $course);
        
        $this->assertTrue($view->isEnrolled());
    }
}
