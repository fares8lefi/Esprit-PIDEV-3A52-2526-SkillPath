<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Course;
use App\Repository\CourseRepository;
use App\Repository\UserCourseViewRepository;

class RecommendationService
{
    private CourseRepository $courseRepository;
    private UserCourseViewRepository $userCourseViewRepository;

    public function __construct(
        CourseRepository $courseRepository,
        UserCourseViewRepository $userCourseViewRepository
    ) {
        $this->courseRepository = $courseRepository;
        $this->userCourseViewRepository = $userCourseViewRepository;
    }

    /**
     * Recommends courses based on user profile and a potential budget.
     *
     * @param User $user
     * @param float|null $budget
     * @return array Array of associative arrays with 'course', 'score', 'probability', 'reasons'
     */
    public function recommendCourses(User $user, ?float $budget = null): array
    {
        $allCourses = $this->courseRepository->findAll();
        $userViews = $this->userCourseViewRepository->findByUser($user->getId());
        
        $completedCourseIds = [];
        $completedCategories = [];
        
        foreach ($userViews as $view) {
            if ($view->isCompleted() && $view->getCourse()) {
                $completedCourseIds[] = $view->getCourse()->getId();
                if ($view->getCourse()->getCategory()) {
                    $completedCategories[] = $view->getCourse()->getCategory();
                }
            }
        }
        
        if ($user->getDomaine()) {
            $completedCategories[] = $user->getDomaine();
        }

        $recommendations = [];

        foreach ($allCourses as $course) {
            if (in_array($course->getId(), $completedCourseIds)) {
                // Exclude already completed courses
                continue;
            }

            $userLevel = $user->getNiveau();
            $courseLevel = $course->getLevel();

            if ($userLevel && $courseLevel) {
                if (strtolower($userLevel) !== strtolower($courseLevel)) {
                    continue; // Skip course entirely if level doesn't match
                }
            }

            $score = 0;
            $reasons = [];

            // A. Correspondance de niveau (+30)
            if ($userLevel && $courseLevel) {
                if (strtolower($userLevel) === strtolower($courseLevel)) {
                    $score += 30;
                    $reasons[] = 'Niveau adapté';
                }
            }

            // B. Similarité / Catégorie (+20)
            if ($course->getCategory() && in_array($course->getCategory(), $completedCategories)) {
                $score += 20;
                $reasons[] = 'Dans votre domaine';
            }

            // C. Popularité (+15)
            $rating = $course->getRating() ?? 0;
            $score += ($rating / 5) * 15;

            // D. Cost Penalty / Bonus
            if ($budget !== null) {
                $price = $course->getPrice() ?? 0;
                if ($price > $budget) {
                    $score -= 50; // Pénalisation pour dépassement de budget
                } else {
                    $score += 10;
                    $reasons[] = 'Dans votre budget';
                }
            }

            // E. Probabilité de Succès (ML stat mock)
            $successProbability = $this->predictSuccessProbability($user, $course, $userViews);
            
            // On ajuste légèrement le score global par la probabilité de succès (ex: x 1.0 à x 1.5)
            $adjustedScore = $score * (0.5 + ($successProbability * 0.5));

            // Si le score ajusté est très bas et qu'on a un budget, on risque d'être négatif, on filtre ou on garde en bas
            if ($budget !== null && $adjustedScore < 0) {
                continue; // Filtrer si ça dépasse le budget et que le score est ruiné
            }

            $recommendations[] = [
                'course' => $course,
                'score' => round($adjustedScore, 2),
                'probability' => round($successProbability * 100, 1),
                'reasons' => $reasons
            ];
        }

        // Sort by score descending
        usort($recommendations, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $recommendations;
    }

    /**
     * Prédit la probabilité de succès d'un cours pour un étudiant.
     * Basé sur ses précédents engagements et scores.
     */
    private function predictSuccessProbability(User $user, Course $course, array $userViews): float
    {
        $relevantEngagement = 0;
        $count = 0;
        
        foreach ($userViews as $view) {
            $viewCourse = $view->getCourse();
            // On accorde plus de poids si la catégorie correspond
            $weight = ($viewCourse && $viewCourse->getCategory() === $course->getCategory()) ? 1.5 : 1.0;
            
            $engagement = strtolower($view->getEngagementLevel() ?? '');
            if ($engagement === 'high') {
                $relevantEngagement += 1.0 * $weight;
            } elseif ($engagement === 'medium') {
                $relevantEngagement += 0.6 * $weight;
            } elseif ($engagement === 'low') {
                $relevantEngagement += 0.2 * $weight;
            }
            
            $count += $weight;
        }
        
        if ($count > 0) {
            return min(1.0, $relevantEngagement / $count);
        }

        // Base de 0.6 s'il n'y a pas d'historique
        return 0.6;
    }
}
