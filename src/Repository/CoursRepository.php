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
    public function findByFilters(?string $search = null): array
    {
        $qb = $this->createQueryBuilder('c');

        if ($search) {
            $qb->andWhere('c.titre LIKE :search OR c.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // Module and Type filters removed as they are no longer on Cours entity directly
        // To filter by Child Module Type, we would need a Join.

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