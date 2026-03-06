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

    public function recordView(User $user, Course $course, bool $flush = true): UserCourseView
    {
        $view = $this->repository->findOneBy([
            'user' => $user->getId(),
            'course' => $course->getId()
        ]);

        if (!$view) {
            $view = new UserCourseView();
            $view->setUser($user);
            $view->setCourse($course);
            $view->setTimeSpent(0);
            
            // Si l'utilisateur est déjà dans la collection du cours, on marque comme inscrit
            $isAlreadyInCollection = $user->getCourses()->contains($course);
            $view->setIsEnrolled($isAlreadyInCollection);
            
            $view->setMaxModuleReached(0);
            $this->entityManager->persist($view);
            
            if ($flush) {
                try {
                    $this->entityManager->flush();
                } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                    $this->entityManager->detach($view);
                    $view = $this->repository->findOneBy(['user' => $user, 'course' => $course]);
                    if (!$view) throw $e;
                }
            }
        }

        // Recalculer le niveau et domaine (sans flusher immédiatement)
        $this->updateUserLevel($user, false);
        $this->updateUserPreferredDomain($user, false);

        if ($flush && $this->entityManager->isOpen()) {
            $this->entityManager->flush();
        }

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
        $view = $this->recordView($user, $course, false);
        $view->setIsEnrolled(true);

        if (!$user->getCourses()->contains($course)) {
            $user->addCourse($course);
        }

        try {
            $this->entityManager->flush();
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            // Si une erreur de contrainte unique survient, c'est que l'entrée existe déjà
            // On rafraîchit l'entity manager et on ignore l'erreur
            $this->entityManager->clear();
            // On ne fait rien de plus car l'utilisateur est déjà techniquement "inscrit" s'il y a un doublon
        }
    }

    public function isUserEnrolled(User $user, Course $course): bool
    {
        // Les admins ont accès à tout
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        $view = $this->repository->findByUserAndCourse($user, $course);
        if ($view && $view->isEnrolled()) {
            return true;
        }

        // Fallback: vérifier la collection ManyToMany
        return $user->getCourses()->contains($course);
    }

    public function markCourseAsCompleted(User $user, Course $course): void
    {
        $view = $this->recordView($user, $course, false);
        if (!$view->isCompleted()) {
            $view->setIsCompleted(true);
            $this->updateUserLevel($user, false);
            $this->entityManager->flush();
        }
    }

    public function updateUserLevel(User $user, bool $flush = true): void
    {
        $userViews = $this->repository->findByUser($user);
        
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
                if ($flush) {
                    $this->entityManager->flush();
                }
            }
        }
    }

    public function updateUserPreferredDomain(User $user, bool $flush = true): void
    {
        // Si le domaine est déjà renseigné, on ne le touche pas
        if (trim($user->getDomaine() ?? '') !== '') {
            return;
        }

        $userViews = $this->repository->findByUser($user);
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
            if ($flush) {
                $this->entityManager->flush();
            }
        }
    }

    /**
     * @return Course[]
     */
    public function getRecommendations(User $user, int $topN = 1): array
    {
        $unseenCourses = $this->repository->findUnseenCoursesByUser($user);
        $scoredCourses = [];

        foreach ($unseenCourses as $course) {
            $features = $this->repository->getMLFeaturesForUser($user, $course);
            
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
