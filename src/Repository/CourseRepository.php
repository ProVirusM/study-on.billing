<?php

namespace App\Repository;

use App\Entity\Course;
use App\Enum\CourseType;
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
    public function persistCourse(Course $course): bool
    {
        try {
            $entityManager = $this->getEntityManager();
            $entityManager->persist($course);
            $entityManager->flush();
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function findAnalyzesCourses(\DateTime $date): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('c.title', 'c.type', 'COUNT(c.id) as count', 'SUM(t.amount) as sum')
            ->from(Course::class, 'c')
            ->join('c.transactions', 't')
            ->where('t.created_at BETWEEN :start AND :end')
            ->setParameter('start', $date)
            ->setParameter('end', (clone $date)->modify('+1 month'))
            ->groupBy('c.id');
        $result = $qb->getQuery()->getResult();
        foreach ($result as $key => $course) {
            $result[$key]['type'] = CourseType::byCode($course['type'])->title();
        }
        return $result;
    }

    //    /**
    //     * @return Course[] Returns an array of Course objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Course
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
