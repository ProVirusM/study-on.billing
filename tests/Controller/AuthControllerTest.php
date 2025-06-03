<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthControllerTest extends WebTestCase
{
    private ?EntityManagerInterface $entityManager = null;
    private UserPasswordHasherInterface $hasher;
    private $client;


    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
        $passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        // Очистка базы
        $purger = new ORMPurger($this->entityManager);
        $purger->purge();

        // Загрузка фикстур
        $loader = new Loader();
        $loader->addFixture(new \App\DataFixtures\TransactionFixtures($passwordHasher));

        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute($loader->getFixtures());


    }

    public function testAuthSuccess()
    {
        $this->client->request(
            'POST',
            '/api/v1/auth',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => 'user@example.com',
                'password' => 'user_password'
            ])
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);
    }

    public function testAuthInvalidCredentials()
    {
        $this->client->request(
            'POST',
            '/api/v1/auth',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => 'user@example.com',
                'password' => 'wrong_password'
            ])
        );

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testCurrentUser()
    {
        // Аутентификация
        $this->client->request(
            'POST',
            '/api/v1/auth',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => 'user@example.com',
                'password' => 'user_password'
            ])
        );

        $token = json_decode($this->client->getResponse()->getContent(), true)['token'];

        // Запрос с токеном
        $this->client->request(
            'GET',
            '/api/v1/users/current',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('user@example.com', $data['username']);
    }
    public function testRegisterSuccess(): void
    {
        $this->client->request(
            'POST',
            '/api/v1/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => 'newuser@example.com',
                'password' => 'new_password'
            ])
        );

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);
    }

    public function testRegisterDuplicateEmail(): void
    {
        // Повторная регистрация того же email
        $this->client->request(
            'POST',
            '/api/v1/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => 'user@example.com',
                'password' => 'another_password'
            ])
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('User already exists', $data['error']);
    }

    public function testRegisterValidationError(): void
    {
        // Пустые поля
        $this->client->request(
            'POST',
            '/api/v1/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => '',
                'password' => ''
            ])
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertNotEmpty($data['errors']);
    }

    public function testUnauthorizedAccessToCurrentUser(): void
    {
        $this->client->request('GET', '/api/v1/users/current');

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('JWT Token not found', $data['message']);
    }


}
