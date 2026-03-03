<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return User[]
     */
    public function findByAdvancedSearch(?string $query, ?string $role, ?string $status): array
    {
        $qb = $this->createQueryBuilder('u');

        if ($query !== null && $query !== '') {
            $qb->andWhere('(u.username LIKE :query OR u.email.value LIKE :query)')
               ->setParameter('query', '%' . $query . '%');
        }

        if ($role !== null && $role !== '') {
            $qb->andWhere('u.role = :role')
               ->setParameter('role', $role);
        }

        if ($status !== null && $status !== '') {
            $qb->andWhere('u.status = :status')
               ->setParameter('status', $status);
        }

        return $qb->orderBy('u.createdAt', 'DESC')
                  ->addOrderBy('u.id', 'DESC')
                  ->getQuery()
                  ->getResult();
    }
}
