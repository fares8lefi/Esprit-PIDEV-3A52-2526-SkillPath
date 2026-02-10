<?php

namespace App\Repository;

use App\Entity\Cours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cours>
 */
class CoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cours::class);
    }

    /**
     * Recherche de contenus avec filtres
     * 
     * @param string|null $search Texte de recherche
     * @param int|null $moduleId ID du module
     * @param string|null $type Type de contenu
     * @return Cours[]
     */
    public function findByFilters(?string $search = null, ?int $moduleId = null, ?string $type = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.module', 'm')
            ->addSelect('m');

        if ($search) {
            $qb->andWhere('c.titre LIKE :search OR c.contenu LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($moduleId) {
            $qb->andWhere('c.module = :module')
               ->setParameter('module', $moduleId);
        }

        if ($type) {
            $qb->andWhere('c.type = :type')
               ->setParameter('type', $type);
        }

        return $qb->orderBy('c.id', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Récupère les contenus par module
     * 
     * @param Module $module
     * @return Cours[]
     */
    public function findByModule($module): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.module = :module')
            ->setParameter('module', $module)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les contenus par type
     * 
     * @param string $type
     * @return Cours[]
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.module', 'm')
            ->addSelect('m')
            ->andWhere('c.type = :type')
            ->setParameter('type', $type)
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre de contenus par type
     * 
     * @return array
     */
    public function countByType(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.type, COUNT(c.id) as total')
            ->groupBy('c.type')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les derniers contenus créés
     * 
     * @param int $limit
     * @return Cours[]
     */
    public function findLatest(int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.module', 'm')
            ->addSelect('m')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}