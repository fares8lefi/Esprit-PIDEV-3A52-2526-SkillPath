<?php

namespace App\Repository;

use App\Entity\Module;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Module>
 */
class ModuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Module::class);
    }

    public function qbSearch(?string $search): QueryBuilder
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.course', 'c')
            ->addSelect('c');

        if ($search) {
            $qb->andWhere('m.title LIKE :s OR m.description LIKE :s')
               ->setParameter('s', '%' . $search . '%');
        }

        return $qb->orderBy('m.id', 'DESC');
    }
}
