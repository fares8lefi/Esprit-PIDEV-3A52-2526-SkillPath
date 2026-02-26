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
    private AIService $aiService;

    public function __construct(
        EntityManagerInterface $entityManager, 
        UserCourseViewRepository $repository,
        AIService $aiService
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->aiService = $aiService;
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

        // Synchroniser avec la relation ManyToMany si nécessaire
        if (!$user->getCourses()->contains($course)) {
            $user->addCourse($course);
        }

        $this->entityManager->flush();
    }

    public function isUserEnrolled(User $user, Course $course): bool
    {
        $view = $this->repository->findByUserAndCourse($user->getId(), $course->getId());
        return $view ? $view->isEnrolled() : false;
    }

    public function getRecommendations(User $user, int $topN = 1): array
    {
        $unseenCourses = $this->repository->findUnseenCoursesByUser($user->getId());
        $scoredCourses = [];

        foreach ($unseenCourses as $course) {
            $features = $this->repository->getMLFeaturesForUser($user->getId(), $course->getId());
            
            try {
                // Utilisation du consensus de tous les modèles AI
                $result = $this->aiService->getPrediction($features, 'all');
                // On récupère la probabilité moyenne pour la classe "inscrit"
                $score = $result['prediction']['probabilities'][1] ?? $course->getRating();
            } catch (\Exception $e) {
                // Fallback sur le rating en cas d'erreur serveur AI
                $score = $course->getRating();
            }

            $scoredCourses[] = [
                'course' => $course,
                'score' => $score
            ];
        }

        // Tri par score décroissant
        usort($scoredCourses, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // Retourne uniquement l'objet Course le plus recommandé
        return array_map(fn($item) => $item['course'], array_slice($scoredCourses, 0, $topN));
    }
}
