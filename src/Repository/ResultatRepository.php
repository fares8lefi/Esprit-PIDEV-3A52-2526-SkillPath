<?php

namespace App\Repository;

use App\Entity\Resultat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Resultat>
 */
class ResultatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Resultat::class);
    }

    /**
     * @return array{totalAttempts: int, passedCount: int, average: float|int}
     */
    public function getUserStats(\App\Entity\User $user): array
    {
        $results = $this->createQueryBuilder('r')
            ->select('r.score', 'r.noteMax')
            ->where('r.etudiant = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $totalAttempts = count($results);
        $passedCount = 0;
        $totalScore = 0;
        $totalMax = 0;

        foreach ($results as $result) {
            if ($result['noteMax'] > 0) {
                // On cap à la note max pour ne pas dépasser 100% en moyenne globale
                $totalScore += min($result['score'], $result['noteMax']);
                $totalMax += $result['noteMax'];
                if (($result['score'] / $result['noteMax'] * 100) >= 50) {
                    $passedCount++;
                }
            }
        }

        return [
            'totalAttempts' => $totalAttempts,
            'passedCount' => $passedCount,
            'average' => $totalMax > 0 ? round(($totalScore / $totalMax) * 100) : 0
        ];
    }
}
