<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Course;
use App\Entity\Certificate;
use App\Entity\UserCourseView;
use App\Repository\UserCourseViewRepository;
use Doctrine\ORM\EntityManagerInterface;

class PredictionService
{
    private UserCourseViewRepository $userCourseViewRepository;
    private AIService $aiService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserCourseViewRepository $userCourseViewRepository,
        AIService $aiService,
        EntityManagerInterface $entityManager
    ) {
        $this->userCourseViewRepository = $userCourseViewRepository;
        $this->aiService = $aiService;
        $this->entityManager = $entityManager;
    }

    /**
     * Retourne les statistiques globales (Certifs, Progression moyenne) pour l'affichage Home.
     */
    public function getGlobalStats(User $user): array
    {
        $certifsCount = $this->entityManager->getRepository(Certificate::class)->count(['user' => $user]);
        
        $userViews = $this->userCourseViewRepository->findByUser($user->getId());
        $avgProgression = 0;
        
        if (!empty($userViews)) {
            $totalProg = 0;
            foreach ($userViews as $view) {
                $course = $view->getCourse();
                if ($course) {
                    $totalModules = count($course->getModules());
                    if ($totalModules > 0) {
                        $prog = ($view->getMaxModuleReached() / $totalModules) * 100;
                        $totalProg += $view->isCompleted() ? 100 : min(100, $prog);
                    }
                }
            }
            $avgProgression = $totalProg / count($userViews);
        }

        return [
            'certifs' => $certifsCount,
            'progression' => round($avgProgression, 1)
        ];
    }

    /**
     * Calcule la probabilité de réussite pour un utilisateur et un cours spécifique.
     */
    public function predictSuccessProbability(User $user, Course $course): float
    {
        $userViews = $this->userCourseViewRepository->findByUser($user->getId());
        $features = $this->buildFeaturesArray($user, $course, $userViews);

        try {
            $resultat = $this->aiService->getPrediction(array_values($features), 'Random_Forest');
            $scoreIA = $resultat['prediction']['probabilities'][1] ?? 0.0; 
            $finalScore = round($scoreIA * 100, 1);
            
            // Log raw result
            $logMsg = date('[Y-m-d H:i:s]') . " AI RESPONSE - Course: {$course->getId()}, Raw Success Proba: $scoreIA -> $finalScore%\n";
            file_put_contents(__DIR__ . '/../../var/log/ai_debug.log', $logMsg, FILE_APPEND);
            
            return $finalScore;
        } catch (\Exception $e) {
            // Log full error
            $logMsg = date('[Y-m-d H:i:s]') . " AI ERROR - Course: {$course->getId()}, Error: " . $e->getMessage() . "\n";
            file_put_contents(__DIR__ . '/../../var/log/ai_debug.log', $logMsg, FILE_APPEND);
            return 0.0;
        }
    }

    /**
     * Détecte le domaine effectif de l'utilisateur (Profil ou Clics).
     */
    public function getEffectiveDomain(User $user, ?array $userViews = null): string
    {
        // 1. Si le domaine est renseigné dans le profil, on le prend en priorité
        $profileDomain = trim($user->getDomaine() ?? '');
        if ($profileDomain !== '') {
            return $profileDomain;
        }

        // 2. Sinon, on analyse les interactions (clics/vues)
        if ($userViews === null) {
            $userViews = $this->userCourseViewRepository->findByUser($user->getId());
        }

        if (empty($userViews)) {
            return '';
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
            return '';
        }

        // Retourne la catégorie la plus fréquente
        arsort($domainCounts);
        return (string) array_key_first($domainCounts);
    }

    /**
     * Compile et encode les features pour le modèle ML.
     */
    private function buildFeaturesArray(User $user, Course $course, array $userViews): array
    {
        // 1. Nombre de certificats
        $certifsCount = $this->entityManager->getRepository(Certificate::class)->count(['user' => $user]);

        // 2. Niveau (Détecté dynamiquement dans UserCourseViewService)
        $encodeLevel = function(?string $level) {
            $l = mb_strtolower(trim($level ?? ''), 'UTF-8');
            if (str_contains($l, 'avanc')) return 2;
            if (str_contains($l, 'inter')) return 1;
            return 0; // Débutant par défaut
        };

        // 3. Progression spécifique au cours
        $courseProgression = 0;
        foreach ($userViews as $view) {
            if ($view->getCourse() && $view->getCourse()->getId() === $course->getId()) {
                $totalModules = count($course->getModules());
                if ($totalModules > 0) {
                    $courseProg = ($view->getMaxModuleReached() / $totalModules) * 100;
                    $courseProgression = $view->isCompleted() ? 100 : min(100, $courseProg);
                }
                break;
            }
        }

        // 4. Bonus de domaine (Détection Dynamique !)
        $effectiveDomain = $this->getEffectiveDomain($user, $userViews);
        $courseCat = trim($course->getCategory() ?? '');
        
        $categoryMatch = 0;
        if ($effectiveDomain !== '' && $courseCat !== '') {
            if (mb_strtolower($effectiveDomain, 'UTF-8') === mb_strtolower($courseCat, 'UTF-8')) {
                $categoryMatch = 1;
            }
        }

        // DEBUG LOG
        $logMsg = date('[Y-m-d H:i:s]') . " AI DATA - User: {$user->getId()}, Course: {$course->getId()}, Certs: $certifsCount, Lvl: {$encodeLevel($user->getNiveau())}, Prog: $courseProgression, CatMatch: $categoryMatch | EffectiveDomain: \"$effectiveDomain\" vs Course: \"$courseCat\"\n";
        file_put_contents(__DIR__ . '/../../var/log/ai_debug.log', $logMsg, FILE_APPEND);

        return [
            'certifs' => $certifsCount,
            'niveau' => $encodeLevel($user->getNiveau()),
            'progression' => $courseProgression,
            'cat_match' => $categoryMatch
        ];
    }
}
