<?php

namespace App\Repository;

use App\Entity\Reclamation;
use App\Entity\User;
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

<<<<<<< HEAD
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
    /**
     * @return array<int, Reclamation>
     */
    public function findBySearchAndSort(?string $search, ?string $sort = 'id', ?string $direction = 'desc', ?\App\Entity\User $user = null, ?string $status = null): array
=======
    /**
     * @return Reclamation[]
     */
    public function findBySearchAndSort(?string $search, ?string $sort = 'id', ?string $direction = 'desc', ?User $user = null, ?string $status = null): array
>>>>>>> 6dc6223bc4c92aedcb66466f9176530fc89cdd44
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->addSelect('u');

        if ($user) {
            $qb->andWhere('r.user = :user')
               ->setParameter('user', $user);
        }

        if ($status && $status !== 'all') {
            $qb->andWhere('r.statut = :status')
               ->setParameter('status', $status);
        }

        if ($search) {
<<<<<<< HEAD
            $qb->andWhere('u.email LIKE :search OR r.description LIKE :search OR r.sujet LIKE :search')
=======
            $qb->andWhere('r.sujet LIKE :search OR u.email LIKE :search OR r.description LIKE :search')
>>>>>>> 6dc6223bc4c92aedcb66466f9176530fc89cdd44
               ->setParameter('search', '%' . $search . '%');
        }

        // Handle sorting
<<<<<<< HEAD
        $sortField = 'r.' . $sort;
        if ($sort === 'email') {
            $sortField = 'u.email';
        }
=======
        $validSortFields = ['id', 'sujet', 'statut'];
        $actualSort = in_array($sort, $validSortFields) ? 'r.' . $sort : 'r.id';
        $actualDirection = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $qb->orderBy($actualSort, $actualDirection);
>>>>>>> 6dc6223bc4c92aedcb66466f9176530fc89cdd44

        $qb->orderBy($sortField, $direction === 'asc' ? 'ASC' : 'DESC');

        return $qb->getQuery()->getResult();
    }
}
