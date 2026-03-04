<?php

namespace App\Tests\Service;

use App\Entity\Course;
use App\Entity\User;
use App\Entity\UserCourseView;
use App\Repository\CourseRepository;
use App\Repository\UserCourseViewRepository;
use App\Service\AIService;
use App\Service\PredictionService;
use App\Service\RecommendationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\UuidV7;

class RecommendationServiceTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject&CourseRepository $courseRepository;
    private \PHPUnit\Framework\MockObject\MockObject&UserCourseViewRepository $userCourseViewRepository;
    private \PHPUnit\Framework\MockObject\MockObject&PredictionService $predictionService;
    private RecommendationService $recommendationService;

    protected function setUp(): void
    {
        $this->courseRepository = $this->createMock(CourseRepository::class);
        $this->userCourseViewRepository = $this->createMock(UserCourseViewRepository::class);
        $this->predictionService = $this->createMock(PredictionService::class);
        
        $this->recommendationService = new RecommendationService(
            $this->courseRepository,
            $this->userCourseViewRepository,
            $this->predictionService
        );
    }

    public function testRecommendCoursesByLevelAndDomain(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(new UuidV7());
        $user->method('getNiveau')->willReturn('Beginner');
        
        $course1 = $this->createMock(Course::class);
        $course1->method('getId')->willReturn(1);
        $course1->method('getLevel')->willReturn('Beginner');
        $course1->method('getCategory')->willReturn('IT');
        $course1->method('getPrice')->willReturn('50.00');
        
        $this->courseRepository->method('findAll')->willReturn([$course1]);
        $this->userCourseViewRepository->method('findByUser')->willReturn([]);
        $this->predictionService->method('getEffectiveDomain')->willReturn('IT');
        
        $results = $this->recommendationService->recommendCourses($user);
        
        $this->assertCount(1, $results);
        $this->assertSame($course1, $results[0]['course']);
    }

    public function testRecommendCoursesExcludesCompleted(): void
    {
        $user = $this->createMock(User::class);
        $uuid = new UuidV7();
        $user->method('getId')->willReturn($uuid);
        
        $course1 = $this->createMock(Course::class);
        $course1->method('getId')->willReturn(1);
        
        $view1 = $this->createMock(UserCourseView::class);
        $view1->method('isCompleted')->willReturn(true);
        $view1->method('getCourse')->willReturn($course1);
        
        $this->courseRepository->method('findAll')->willReturn([$course1]);
        $this->userCourseViewRepository->method('findByUser')->with($uuid)->willReturn([$view1]);
        
        $results = $this->recommendationService->recommendCourses($user);
        
        $this->assertCount(0, $results);
    }
}
