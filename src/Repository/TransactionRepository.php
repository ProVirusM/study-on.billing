<?php

namespace App\Repository;

use App\Entity\Course;
use App\Entity\Transaction;
use App\Entity\User;
use App\Enum\TransactionType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }
    public function findFilteredByUser(User $user, array $filter): array
    {
        $qb = $this->createQueryBuilder('t')
            ->andWhere('t.users = :user')
            ->setParameter('user', $user);

        if (!empty($filter['type'])) {
            $typeEnum = TransactionType::fromLabel($filter['type']);
            if ($typeEnum !== null) {
                $qb->andWhere('t.type_operations = :type')
                    ->setParameter('type', $typeEnum->value);
            }
        }

        if (!empty($filter['course_code'])) {
            $qb->join('t.course', 'c')
                ->andWhere('c.code = :code')
                ->setParameter('code', $filter['course_code']);
        }

        if (!empty($filter['skip_expired'])) {
            $qb->andWhere('t.time_arend IS NULL OR t.time_arend > :now')
                ->setParameter('now', new \DateTimeImmutable());
        }

        return $qb->orderBy('t.created_at', 'DESC')->getQuery()->getResult();
    }
    public function findActiveAccessForCourse(User $user, Course $course): ?Transaction
    {
        $qb = $this->createQueryBuilder('t')
            ->andWhere('t.users = :user')
            ->andWhere('t.course = :course')
            ->andWhere('t.type_operations = :type')
            ->setParameters([
                'user' => $user,
                'course' => $course,
                'type' => TransactionType::PAYMENT->value,
            ])
            ->orderBy('t.created_at', 'DESC');

        $results = $qb->getQuery()->getResult();

        foreach ($results as $transaction) {
            if ($transaction->getTimeArend() === null || $transaction->getTimeArend() > new \DateTimeImmutable()) {
                return $transaction;
            }
        }

        return null;
    }
    //    /**
    //     * @return Transaction[] Returns an array of Transaction objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Transaction
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
