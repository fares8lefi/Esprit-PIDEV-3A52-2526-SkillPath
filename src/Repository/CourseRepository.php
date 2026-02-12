<?php

namespace App\Repository;

use App\Entity\Course;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Course>
 */
class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Course::class);
    }

    /**
     * Recherche de cours avec filtres
     * 
     * @param string|null $search
     * @param string|null $level
     * @param string|null $category
     * @return Course[]
     */
    public function findByFilters(?string $search = null, ?string $level = null, ?string $category = null): array
    {
        $qb = $this->createQueryBuilder('c');

        if ($search) {
            $qb->andWhere('c.title LIKE :search OR c.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($level) {
            $qb->andWhere('c.level = :level')
               ->setParameter('level', $level);
        }

        if ($category) {
            $qb->andWhere('c.category = :category')
               ->setParameter('category', $category);
        }

        return $qb->orderBy('c.id', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Récupère le nombre de cours par catégorie
     * 
     * @return array
     */
    public function countByCategories(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.category as name, COUNT(c.id) as count')
            ->where('c.category IS NOT NULL')
            ->groupBy('c.category')
            ->getQuery()
            ->getArrayResult();
    }

    public function findLatest(int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
