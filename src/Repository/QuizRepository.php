<?php

namespace App\Repository;

use App\Entity\Quiz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Quiz>
 */
class QuizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quiz::class);
    }
    public function searchAndSortQuery(?string $search, ?string $sort)
    {
        $qb = $this->createQueryBuilder('q');

        if ($search) {
            $qb->andWhere('q.titre LIKE :search OR q.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        switch ($sort) {
            case 'titre_asc':
                $qb->orderBy('q.titre', 'ASC');
                break;
            case 'titre_desc':
                $qb->orderBy('q.titre', 'DESC');
                break;
            case 'duree_asc':
                $qb->orderBy('q.duree', 'ASC');
                break;
            case 'duree_desc':
                $qb->orderBy('q.duree', 'DESC');
                break;
            case 'note_asc':
                $qb->orderBy('q.noteMax', 'ASC');
                break;
            case 'note_desc':
                $qb->orderBy('q.noteMax', 'DESC');
                break;
            default:
                $qb->orderBy('q.titre', 'ASC'); // Default sort
        }

        return $qb;
    }

    public function searchAndSort(?string $search, ?string $sort): array
    {
        return $this->searchAndSortQuery($search, $sort)->getQuery()->getResult();
    }

    public function findAllWithRelations(): array
    {
        return $this->createQueryBuilder('q')
            ->leftJoin('q.course', 'c')
            ->addSelect('c')
            ->leftJoin('q.questions', 'qu')
            ->addSelect('qu')
            ->orderBy('q.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
