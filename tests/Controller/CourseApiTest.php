<?php

namespace App\Tests\Controller;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CourseApiTest extends WebTestCase
{
    private ?EntityManagerInterface $entityManager = null;
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        self::ensureKernelShutdown(); // безопасно завершаем ядро, если оно уже было запущено

        $this->client = static::createClient();
        $container = $this->client->getContainer();

        $this->entityManager = $container->get('doctrine')->getManager();
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        // Очистка базы
        $purger = new ORMPurger($this->entityManager);
        $purger->purge();

        // Загрузка фикстур
        $loader = new Loader();
        $loader->addFixture(new \App\DataFixtures\TransactionFixtures($passwordHasher));
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }

    public function testCoursesListReturnsValidJson(): void
    {
        $this->client->request('GET', '/api/v1/courses');

        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        foreach ($data as $item) {
            $this->assertArrayHasKey('code', $item);
            $this->assertArrayHasKey('type', $item);
        }
    }

    public function testCourseShow(): void
    {
        $this->client->request('GET', '/api/v1/courses/web-development');
        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('web-development', $data['code']);
    }

    public function testCoursePay(): void
    {
        $this->client->request('POST', '/api/v1/auth', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'username' => 'user@example.com',
            'password' => 'user_password'
        ]));
        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);
        $token = $data['token'];


        $this->client->request('POST', '/api/v1/courses/web-development/pay', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
    }

    public function testTransactionsList(): void
    {
        $this->client->request('POST', '/api/v1/auth', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'username' => 'user@example.com',
            'password' => 'user_password'
        ]));

        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);
        $token = $data['token'];

        $this->client->request('GET', '/api/v1/transactions', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ]);

        $this->assertResponseIsSuccessful();

        $transactions = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($transactions);
    }
}
