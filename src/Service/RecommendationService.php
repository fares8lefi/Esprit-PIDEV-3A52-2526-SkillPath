<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Course;
use App\Entity\Certificate;
use App\Repository\CourseRepository;
use App\Repository\UserCourseViewRepository;
use Doctrine\ORM\EntityManagerInterface;

class RecommendationService
{
    private CourseRepository $courseRepository;
    private UserCourseViewRepository $userCourseViewRepository;
    private PredictionService $predictionService;

    public function __construct(
        CourseRepository $courseRepository,
        UserCourseViewRepository $userCourseViewRepository,
        PredictionService $predictionService
    ) {
        $this->courseRepository = $courseRepository;
        $this->userCourseViewRepository = $userCourseViewRepository;
        $this->predictionService = $predictionService;
    }

    /**
     * Recommends courses based on user profile, detected interests and budget.
     * 
     * @return array<array{course: Course, reasons: string[]}>
     */
    public function recommendCourses(User $user, ?float $budget = null): array
    {
        $allCourses = $this->courseRepository->findAll();
        $userViews = $this->userCourseViewRepository->findByUser($user->getId());
        
        $completedCourseIds = [];
        foreach ($userViews as $view) {
            if ($view->isCompleted() && $view->getCourse()) {
                $completedCourseIds[] = $view->getCourse()->getId();
            }
        }
        
        $userLevel = $user->getNiveau();
        // Détection dynamique du domaine si non présent dans le profil
        $effectiveDomain = $this->predictionService->getEffectiveDomain($user, $userViews);

        $recommendations = [];

        foreach ($allCourses as $course) {
            // 1. Exclure les cours déjà terminés
            if (in_array($course->getId(), $completedCourseIds)) {
                continue;
            }

            // 2. Filtre par Niveau (Strict)
            $courseLevel = $course->getLevel();
            if ($userLevel && $courseLevel) {
                if (mb_strtolower($userLevel, 'UTF-8') !== mb_strtolower($courseLevel, 'UTF-8')) {
                    continue;
                }
            }

            // 3. Filtre par Domaine / Catégorie (Strict sur le domaine EFFECTIF)
            $courseCat = $course->getCategory() ? trim($course->getCategory()) : '';
            if ($effectiveDomain !== '' && $courseCat !== '') {
                if (mb_strtolower($effectiveDomain, 'UTF-8') !== mb_strtolower($courseCat, 'UTF-8')) {
                    continue;
                }
            }

            // 4. Filtre par Budget
            if ($budget !== null && $course->getPrice() > $budget) {
                continue;
            }

            $recommendations[] = [
                'course' => $course,
                'reasons' => $effectiveDomain !== '' ? ['Correspond à vos centres d\'intérêt'] : ['Suggéré pour votre niveau']
            ];
        }

        return $recommendations;
    }
}
