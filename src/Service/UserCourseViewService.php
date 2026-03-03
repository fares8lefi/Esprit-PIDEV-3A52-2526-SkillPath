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
            $view->setMaxModuleReached(0); // Nouveau champ initialisé
            $this->entityManager->persist($view);
            $this->entityManager->flush();
        }

        // Recalculer dynamiquement le niveau et le domaine de l'utilisateur à chaque clic/vue
        $this->updateUserLevel($user);
        $this->updateUserPreferredDomain($user);

        return $view;
    }

    /**
     * Enregistre la vue d'un module spécifique et met à jour l'index max atteint.
     */
    public function recordModuleView(User $user, \App\Entity\Module $module): UserCourseView
    {
        $view = $this->recordView($user, $module->getCourse());
        
        // Trouver la position du module actuel (1-indexed, trié par ID)
        $courseModules = $module->getCourse()->getModules()->toArray();
        usort($courseModules, fn($a, $b) => $a->getId() <=> $b->getId());
        
        $currentIndex = 0;
        foreach ($courseModules as $index => $m) {
            if ($m->getId() === $module->getId()) {
                $currentIndex = $index + 1;
                break;
            }
        }

        // Mettre à jour si on a atteint un nouveau sommet
        if ($currentIndex > $view->getMaxModuleReached()) {
            $view->setMaxModuleReached($currentIndex);
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

    public function markCourseAsCompleted(User $user, Course $course): void
    {
        $view = $this->recordView($user, $course);
        if (!$view->isCompleted()) {
            $view->setIsCompleted(true);
            $this->updateUserLevel($user);
            $this->entityManager->flush();
        }
    }

    public function updateUserLevel(User $user): void
    {
        $userViews = $this->repository->findByUser($user->getId());
        
        $levelCounts = [
            'Débutant' => 0,
            'Intermédiaire' => 0,
            'Avancé' => 0
        ];
        
        $totalViews = 0;
        
        foreach ($userViews as $view) {
            if ($view->getCourse() && $view->getCourse()->getLevel()) {
                $level = $view->getCourse()->getLevel();
                foreach (array_keys($levelCounts) as $definedLevel) {
                    if (strtolower($level) === strtolower($definedLevel)) {
                        // Un cours consulté vaut 1 point, un cours terminé vaut 3 points pour le calcul du niveau
                        $weight = $view->isCompleted() ? 3 : 1; 
                        $levelCounts[$definedLevel] += $weight;
                        $totalViews++;
                        break;
                    }
                }
            }
        }
        
        if ($totalViews > 0) {
            $majorityLevel = array_keys($levelCounts, max($levelCounts))[0];
            
            // On s'assure de ne flusher que s'il y a un réel changement
            if ($user->getNiveau() !== $majorityLevel) {
                $user->setNiveau($majorityLevel);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }
        }
    }

    public function updateUserPreferredDomain(User $user): void
    {
        // Si le domaine est déjà renseigné, on ne le touche pas
        if (trim($user->getDomaine() ?? '') !== '') {
            return;
        }

        $userViews = $this->repository->findByUser($user->getId());
        if (empty($userViews)) {
            return;
        }

        $domainCounts = [];
        foreach ($userViews as $view) {
            $course = $view->getCourse();
            if ($course && $course->getCategory()) {
                $cat = trim($course->getCategory());
                if ($cat !== '') {
                    $domainCounts[$cat] = ($domainCounts[$cat] ?? 0) + 1;
                }
            }
        }

        if (empty($domainCounts)) {
            return;
        }

        arsort($domainCounts);
        $majorityDomain = (string) array_key_first($domainCounts);

        if ($user->getDomaine() !== $majorityDomain) {
            $user->setDomaine($majorityDomain);
            $this->entityManager->flush();
        }
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
