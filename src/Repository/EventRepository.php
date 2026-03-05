<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @return Event[]
     */
    public function findByFilters(?string $search, ?int $rating, ?int $locationId): array
    {
        // Do not filter out past events here — let the front-office show all events
        $qb = $this->createQueryBuilder('e')
            ->orderBy('e.eventDate', 'ASC');

        if ($search) {
            $qb->andWhere('e.title LIKE :search OR e.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($rating) {
            $qb->andWhere('e.averageRating >= :rating')
               ->setParameter('rating', $rating);
        }

        if ($locationId) {
            $qb->andWhere('e.location = :locationId')
               ->setParameter('locationId', $locationId);
        }

        return $qb->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
