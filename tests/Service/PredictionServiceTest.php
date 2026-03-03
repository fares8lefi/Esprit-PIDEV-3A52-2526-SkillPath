<?php

namespace App\Tests\Service;

use App\Entity\Course;
use App\Entity\User;
use App\Entity\UserCourseView;
use App\Entity\Certificate;
use App\Repository\UserCourseViewRepository;
use App\Service\AIService;
use App\Service\PredictionService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class PredictionServiceTest extends TestCase
{
    private $userCourseViewRepository;
    private $aiService;
    private $entityManager;
    private $predictionService;

    protected function setUp(): void
    {
        $this->userCourseViewRepository = $this->createMock(UserCourseViewRepository::class);
        $this->aiService = $this->createMock(AIService::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->predictionService = new PredictionService(
            $this->userCourseViewRepository,
            $this->aiService,
            $this->entityManager
        );
    }

    public function testGetEffectiveDomainFromProfile(): void
    {
        $user = new User();
        $user->setDomaine('Développement');

        $domain = $this->predictionService->getEffectiveDomain($user);

        $this->assertEquals('Développement', $domain);
    }

    public function testGetEffectiveDomainFromInteractions(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        
        $course1 = $this->createMock(Course::class);
        $course1->method('getCategory')->willReturn('Design');
        
        $view1 = $this->createMock(UserCourseView::class);
        $view1->method('getCourse')->willReturn($course1);

        $this->userCourseViewRepository->method('findByUser')->with(1)->willReturn([$view1]);

        $domain = $this->predictionService->getEffectiveDomain($user);

        $this->assertEquals('Design', $domain);
    }

    public function testGetGlobalStats(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $certRepo = $this->createMock(EntityRepository::class);
        $certRepo->method('count')->willReturn(5);

        $this->entityManager->method('getRepository')->with(Certificate::class)->willReturn($certRepo);
        $this->userCourseViewRepository->method('findByUser')->willReturn([]);

        $stats = $this->predictionService->getGlobalStats($user);

        $this->assertEquals(5, $stats['certifs']);
        $this->assertEquals(0, $stats['progression']);
    }
}
