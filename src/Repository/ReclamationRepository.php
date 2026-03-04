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

    /**
     * @return array<int, Reclamation>
     */
    public function findBySearchAndSort(?string $search, ?string $sort = 'id', ?string $direction = 'desc', ?\App\Entity\User $user = null, ?string $status = null): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->addSelect('u');

        if ($user) {
            $qb->andWhere('u.email.value = :userEmail')
               ->setParameter('userEmail', $user->getEmail());
        }

        if ($status && $status !== 'all') {
            $qb->andWhere('r.statut = :status')
               ->setParameter('status', $status);
        }

        if ($search) {
            $qb->andWhere('r.sujet LIKE :search OR u.email LIKE :search OR r.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // Handle sorting
        $validSortFields = ['id', 'sujet', 'statut'];
        $actualSort = in_array($sort, $validSortFields) ? 'r.' . $sort : 'r.id';
        $actualDirection = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $qb->orderBy($actualSort, $actualDirection);

        return $qb->getQuery()->getResult();
    }
}
