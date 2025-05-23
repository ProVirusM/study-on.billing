<?php
// src/Controller/AuthController.php
namespace App\Controller;

use App\Dto\UserDto;
use App\Entity\User;

use App\Service\PaymentService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use JMS\Serializer\SerializerBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use OpenApi\Attributes as OA;
use JMS\Serializer\SerializerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;


use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class AuthController extends AbstractController
{
    private float $initialBalance;
    private PaymentService $paymentService;

    public function __construct(
        PaymentService $paymentService,
        #[Autowire('%app.initial_balance%')] float $initialBalance,
    ) {
        $this->paymentService = $paymentService;
        $this->initialBalance = $initialBalance;
    }
    #[Route('/api/v1/auth', name: 'api_auth', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/auth',
        description: 'Authenticates user and returns JWT token',
        summary: 'Authenticate user',
        requestBody: new OA\RequestBody(
            description: 'User credentials',
            content: new OA\JsonContent(
                required: ['username', 'password'],
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Authentication successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: 'jwt.token.here'),
new OA\Property(property: 'refresh_token', type: 'string', example: 'your-refresh-token-here')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid credentials',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'code', type: 'integer', example: 401),
                        new OA\Property(property: 'message', type: 'string', example: 'Invalid credentials.')
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    public function auth(): JsonResponse
    {
        // Фактическая аутентификация обрабатывается LexikJWTAuthenticationBundle
        // Этот маршрут — просто заполнитель для маршрутизации
        return new JsonResponse(['message' => 'Authentication should be handled by JSON login']);
    }

    #[Route('/api/v1/register', name: 'api_register', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/register',
        summary: 'Register new user',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['username', 'password'],
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'newuser@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: 'jwt.token.here'),
                        new OA\Property(property: 'refresh_token', type: 'string', example: 'your-refresh-token-here')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Validation errors',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(type: 'string'))
                    ]
                )
            )
        ]
    )]
    public function register(
        Request $request,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $JWTManager,
        RefreshTokenGeneratorInterface $refreshTokenGenerator,
        RefreshTokenManagerInterface $refreshTokenManager,

    ): JsonResponse {
        $serializer = SerializerBuilder::create()->build();
        $userDto = $serializer->deserialize($request->getContent(), UserDto::class, 'json');

        $errors = $validator->validate($userDto);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        // Check if user already exists
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $userDto->username]);
        if ($existingUser) {
            return new JsonResponse(['error' => 'User already exists'], 400);
        }

        $user = User::fromDto($userDto);
        $user->setPassword($passwordHasher->hashPassword($user, $userDto->password));
        $user->setRoles(['ROLE_USER']);
        $refreshToken = $refreshTokenGenerator->createForUserWithTtl(
            $user,
            (new \DateTime())->modify('+1 month')->getTimestamp()
        );
        $refreshTokenManager->save($refreshToken);
        $entityManager->persist($user);
        $entityManager->flush();
        $this->paymentService->deposit($user, $this->initialBalance);

        return new JsonResponse(['token' => $JWTManager->create($user),'refresh_token' => $refreshToken->getRefreshToken()], 201);
    }
    #[Route('/api/v1/users/current', name: 'api_current_user', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/users/current',
        summary: 'Get current user info',
        security: [['Bearer' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User info',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'username', type: 'string', example: 'user@example.com'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string')),
                        new OA\Property(property: 'balance', type: 'number', format: 'float', example: 100.0)
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'User not authenticated')
                    ]
                )
            )
        ]
    )]
    #[Security(name: 'Bearer')]
    public function getCurrentUser(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], 401);
        }

        return new JsonResponse([
            'username' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
            'balance' => $user->getBalance()
        ]);
    }
    #[Route('/api/v1/token/refresh', name: 'api_token_refresh', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/token/refresh',
        summary: 'Refresh JWT token',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'refresh_token', type: 'string', example: 'your-refresh-token-here')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'New JWT token',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: 'new.jwt.token.here')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid or expired refresh token',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'code', type: 'integer', example: 401),
                        new OA\Property(property: 'message', type: 'string', example: 'Not privileged to request the resource.')
                    ]
                )
            )
        ]
    )]
    public function refreshTokenDocPlaceholder(): void
    {
        // Этот метод только для документации
    }
}
