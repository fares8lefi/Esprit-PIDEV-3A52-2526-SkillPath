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
     * @return Reclamation[]
     */
    public function findBySearchAndSort(?string $search, ?string $sort = 'id', ?string $direction = 'desc', ?User $user = null, ?string $status = null): array
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
            $qb->andWhere('r.sujet LIKE :search OR u.email.value LIKE :search OR r.description LIKE :search')
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
