<?php
namespace App\Tests\DataFixtures;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixturesTest extends KernelTestCase
{
    public function testPasswordHashing()
    {
        self::bootKernel();
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail('test@example.com');
        $hashedPassword = $hasher->hashPassword($user, 'plain_password');

        $this->assertTrue($hasher->isPasswordValid($user, 'plain_password'));
        $this->assertFalse($hasher->isPasswordValid($user, 'wrong_password'));
    }
}