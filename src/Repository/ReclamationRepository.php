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
    public function findBySearchAndSort(?string $search, ?string $sort = 'id', ?string $direction = 'desc', ?\App\Entity\User $user = null, ?string $status = null): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->addSelect('u');

        if ($status && $status !== 'all') {
            $qb->andWhere('r.statut = :status')
               ->setParameter('status', $status);
        }

        if ($user) {
            $qb->andWhere('r.user = :user')
               ->setParameter('user', $user);
        }
        if ($search) {
            $qb->andWhere('r.sujet LIKE :search OR u.email LIKE :search OR r.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // Validate allowed sort fields
        $allowedSorts = ['id', 'statut', 'sujet'];
        $sortField = in_array($sort, $allowedSorts) ? 'r.' . $sort : 'r.id';
        $dir = (strtoupper($direction) === 'ASC') ? 'ASC' : 'DESC';

        $qb->orderBy($sortField, $dir);

        return $qb->getQuery()->getResult();
    }
}
