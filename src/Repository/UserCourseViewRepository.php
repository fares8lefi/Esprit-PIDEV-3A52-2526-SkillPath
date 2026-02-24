<?php

namespace App\Repository;

use App\Entity\UserCourseView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserCourseView>
 *
 * @method UserCourseView|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserCourseView|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserCourseView[]    findAll()
 * @method UserCourseView[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserCourseViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserCourseView::class);
    }

    /**
     * @return UserCourseView[]
     */
    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    public function findByUserAndCourse(int $userId, int $courseId): ?UserCourseView
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.user = :userId')
            ->andWhere('u.course = :courseId')
            ->setParameter('userId', $userId)
            ->setParameter('courseId', $courseId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return \App\Entity\Course[]
     */
    public function findUnseenCoursesByUser(int $userId): array
    {
        $subQuery = $this->createQueryBuilder('uv')
            ->select('c.id')
            ->innerJoin('uv.course', 'c')
            ->where('uv.user = :userId')
            ->getDQL();

        return $this->getEntityManager()->createQueryBuilder()
            ->select('course')
            ->from(\App\Entity\Course::class, 'course')
            ->where($this->getEntityManager()->createQueryBuilder()->expr()->notIn('course.id', $subQuery))
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return UserCourseView[]
     */
    public function findEnrolledCoursesByUser(int $userId): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.user = :userId')
            ->andWhere('u.isEnrolled = :enrolled')
            ->setParameter('userId', $userId)
            ->setParameter('enrolled', true)
            ->getQuery()
            ->getResult();
    }

    public function getMLFeaturesForUser(int $userId, int $courseId): array
    {
        $view = $this->findByUserAndCourse($userId, $courseId);
        $course = $this->getEntityManager()->getRepository(\App\Entity\Course::class)->find($courseId);

        if (!$course) return [];

        return [
            'time_spent' => $view ? $view->getTimeSpent() : 0,
            'quiz_score' => $view ? $view->getQuizScore() : 0,
            'engagement_level' => $view ? $view->getEngagementLevel() : 'none',
            'course_duration' => $course->getDuration(),
            'course_price' => $course->getPrice(),
            'course_rating' => $course->getRating()
        ];
    }
}
