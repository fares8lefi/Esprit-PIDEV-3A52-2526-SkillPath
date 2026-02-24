<?php

namespace App\Repository;

use App\Entity\Reclamation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reclamation>
 */
class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reclamation::class);
    }

    //    /**
    //     * @return Reclamation[] Returns an array of Reclamation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reclamation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
<<<<<<< Updated upstream
    public function findBySearchAndSort(?string $search, ?string $sortOrder): array
=======
    public function findBySearchAndSort(?string $search, ?string $sort = 'id', ?string $direction = 'desc', ?\App\Entity\User $user = null, ?string $status = null): array
>>>>>>> Stashed changes
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->addSelect('u');

<<<<<<< Updated upstream
=======
        if ($user) {
            $qb->andWhere('r.user = :user')
               ->setParameter('user', $user);
        }

        if ($status && $status !== 'all') {
            $qb->andWhere('r.statut = :status')
               ->setParameter('status', $status);
        }

>>>>>>> Stashed changes
        if ($search) {
            $qb->andWhere('u.email LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($sortOrder === 'desc') {
            $qb->orderBy('u.email', 'DESC');
        } else {
            $qb->orderBy('u.email', 'ASC');
        }

        return $qb->getQuery()->getResult();
    }
}
