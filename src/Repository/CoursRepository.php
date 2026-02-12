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
     * @param string|null $search
     * @param string|null $level
     * @param string|null $category
     * @return Cours[]
     */
    public function findByFilters(?string $search = null, ?string $level = null, ?string $category = null, ?string $sort = 'date_desc'): array
    {
        $qb = $this->createQueryBuilder('c');

        if ($search) {
            $qb->andWhere('c.titre LIKE :search OR c.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($level) {
            $qb->andWhere('c.level = :level')
               ->setParameter('level', $level);
        }

        if ($category) {
            $qb->andWhere('c.categorie = :category')
               ->setParameter('category', $category);
        }

        // Sorting logic
        switch ($sort) {
            case 'date_asc':
                $qb->orderBy('c.createdAt', 'ASC');
                break;
            case 'title_asc':
                $qb->orderBy('c.titre', 'ASC');
                break;
            case 'title_desc':
                $qb->orderBy('c.titre', 'DESC');
                break;
            case 'date_desc':
            default:
                $qb->orderBy('c.createdAt', 'DESC');
                break;
        }

        return $qb->getQuery()
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
            ->select('c.categorie as name, COUNT(c.id) as count')
            ->where('c.categorie IS NOT NULL')
            ->groupBy('c.categorie')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Récupère les contenus par module
     * 
     * @param Module $module
     * @return Cours[]
     */
    // findByModule removed
    
    // findByType removed

    /**
     * Récupère les contenus par type
     * 
     * @param string $type
     * @return Cours[]
     */
    // findByType implementation removed

    /**
     * Compte le nombre de contenus par type
     * 
     * @return array
     */
    // countByType removed

    /**
     * Récupère les derniers contenus créés
     * 
     * @param int $limit
     * @return Cours[]
     */
    public function findLatest(int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC') // Assuming createdAt exists? Entity has dateCreation? No, Entity has createdAt/updatedAt? 
            // Checking Cours.php again...
            // It has __construct with $this->createdAt = new \DateTime();
            // It has getters for createdAt.
            // But wait, the previous code used c.createdAt?
            // Yes.
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}