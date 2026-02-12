<?php

namespace App\Repository;

use App\Entity\Module;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class ModuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Module::class);
    }

    public function qbSearch(?string $search, ?int $coursId = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.cours', 'c')
            ->addSelect('c');

        if ($search) {
            $qb->andWhere('m.name LIKE :s OR m.description LIKE :s')
               ->setParameter('s', '%' . $search . '%');
        }

        if ($coursId) {
            $qb->andWhere('c.id = :coursId')
               ->setParameter('coursId', $coursId);
        }

        // Level filter removed

        return $qb->orderBy('m.id', 'DESC');
    }
}
