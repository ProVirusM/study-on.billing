<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function findUsersWithExpiresCourses(): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('u.id', 'u.email', 't.id', 't.time_arend', 'c.title')
            ->from('App\Entity\User', 'u')
            ->join('u.transactions', 't')
            ->join('t.course', 'c')
            ->where('t.time_arend BETWEEN :tomorrow_start AND :tomorrow_end')
            ->setParameter('tomorrow_start', new \DateTime('tomorrow'))
            ->setParameter('tomorrow_end', (new \DateTime('tomorrow'))->modify('+1 day'));
        $result = $qb->getQuery()->getResult();
        $courses = [];
        foreach ($result as $course) {
            $courses[$course['email']][] = [
                'time_arend' => $course['time_arend'],
                'title' => $course['title'],
            ];
        }
        return $courses;
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
