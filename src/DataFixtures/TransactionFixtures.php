<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TransactionFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setBalance(45213.12);
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            'user_password'
        );
        $user->setPassword($hashedPassword);
        $manager->persist($user);

        // Создаем супер-администратора
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_SUPER_ADMIN']);
        $admin->setBalance(54213.12);
        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            'admin_password'
        );
        $admin->setPassword($hashedPassword);
        $manager->persist($admin);

//        $manager->flush();
        ////////////////////////////
        //$user = $manager->getRepository(User::class)->find(1);
//        if (!$user) {
//            throw new \RuntimeException('User with ID 1 not found. Please load User fixtures first.');
//        }

        $coursesData = [
            ['code' => 'web-development', 'type' => 1, 'price' => 990.0],
            ['code' => 'python-basics', 'type' => 1, 'price' => 850.0],
            ['code' => 'databases-sql', 'type' => 2, 'price' => 1200.0],
        ];

        foreach ($coursesData as $data) {
            $course = new Course();
            $course->setCode($data['code']);
            $course->setType($data['type']);
            $course->setPrice($data['price']);
            $manager->persist($course);

            $transaction = new Transaction();
            $transaction->setUsers($user);
            $transaction->setCourse($course);
            $transaction->setTypeOperations(1); // PAYMENT
            $transaction->setAmount($data['price']);
            $transaction->setCreatedAt(new \DateTimeImmutable('-2 days'));
            $transaction->setTimeArend((new \DateTimeImmutable())->modify('+30 days'));

            $manager->persist($transaction);
        }

        $manager->flush();
    }
}
