<?php

namespace App\Service;

use App\Entity\Course;
use App\Entity\User;
use App\Entity\UserCourseView;
use App\Repository\UserCourseViewRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserCourseViewService
{
    private EntityManagerInterface $entityManager;
    private UserCourseViewRepository $repository;

    public function __construct(EntityManagerInterface $entityManager, UserCourseViewRepository $repository)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function recordView(User $user, Course $course): UserCourseView
    {
        $view = $this->repository->findByUserAndCourse($user->getId(), $course->getId());

        if (!$view) {
            $view = new UserCourseView();
            $view->setUser($user);
            $view->setCourse($course);
            $view->setTimeSpent(0);
            $view->setIsEnrolled(false);
            $this->entityManager->persist($view);
            $this->entityManager->flush();
        }

        return $view;
    }

    public function updateTimeSpent(UserCourseView $view, int $minutes): void
    {
        $view->setTimeSpent($view->getTimeSpent() + $minutes);
        $this->calculateEngagementLevel($view);
        $this->entityManager->flush();
    }

    public function updateQuizScore(UserCourseView $view, float $score): void
    {
        $view->setQuizScore($score);
        $this->entityManager->flush();
    }

    public function calculateEngagementLevel(UserCourseView $view): void
    {
        $time = $view->getTimeSpent();

        if ($time < 10) {
            $view->setEngagementLevel('low');
        } elseif ($time <= 30) {
            $view->setEngagementLevel('medium');
        } else {
            $view->setEngagementLevel('high');
        }
    }

    public function enrollUser(User $user, Course $course): void
    {
        $view = $this->recordView($user, $course);
        $view->setIsEnrolled(true);
        $this->entityManager->flush();
    }

    public function getRecommendations(User $user, int $topN = 5): array
    {
        $unseenCourses = $this->repository->findUnseenCoursesByUser($user->getId());
        
        // This is where the ML integration would happen.
        // For now, let's sort by rating as a fallback mock recommendation.
        usort($unseenCourses, function($a, $b) {
            return $b->getRating() <=> $a->getRating();
        });

        return array_slice($unseenCourses, 0, $topN);
    }
}
