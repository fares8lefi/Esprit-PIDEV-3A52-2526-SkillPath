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
use Symfony\Component\Uid\UuidV7;

class PredictionServiceTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject&UserCourseViewRepository $userCourseViewRepository;
    private \PHPUnit\Framework\MockObject\MockObject&AIService $aiService;
    private \PHPUnit\Framework\MockObject\MockObject&EntityManagerInterface $entityManager;
    private PredictionService $predictionService;

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
        $uuid = new UuidV7();
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($uuid);
        
        $course1 = $this->createMock(Course::class);
        $course1->method('getCategory')->willReturn('Design');
        
        $view1 = $this->createMock(UserCourseView::class);
        $view1->method('getCourse')->willReturn($course1);

        $this->userCourseViewRepository->method('findByUser')->with($uuid)->willReturn([$view1]);

        $domain = $this->predictionService->getEffectiveDomain($user);

        $this->assertEquals('Design', $domain);
    }

    public function testGetGlobalStats(): void
    {
        $uuid = new UuidV7();
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($uuid);

        $certRepo = $this->createMock(EntityRepository::class);
        $certRepo->method('count')->willReturn(5);

        $this->entityManager->method('getRepository')->with(Certificate::class)->willReturn($certRepo);
        $this->userCourseViewRepository->method('findByUser')->willReturn([]);

        $stats = $this->predictionService->getGlobalStats($user);

        $this->assertEquals(5, $stats['certifs']);
        $this->assertEquals(0, $stats['progression']);
    }
}
