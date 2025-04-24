<?php
namespace App\Tests\DataFixtures;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixturesTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $hasher;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testPasswordHashing()
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $plainPassword = 'plain_password';
        $hashedPassword = $this->hasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->em->persist($user);
        $this->em->flush();

        // Проверка свежезахешированного пароля
        $this->assertTrue($this->hasher->isPasswordValid($user, $plainPassword));
        $this->assertFalse($this->hasher->isPasswordValid($user, 'wrong_password'));

        // Проверка после получения из БД
        $dbUser = $this->em->getRepository(User::class)->findOneBy(['email' => 'test@example.com']);
        $this->assertTrue($this->hasher->isPasswordValid($dbUser, $plainPassword));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Очистка БД
        $this->em->createQuery('DELETE FROM App\Entity\User')->execute();
    }
}