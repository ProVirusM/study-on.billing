<?php


namespace App\Controller;

use App\Attribute\ValidateDeserialize;
use App\Dto\CourseCreateDto;
use App\Dto\CourseEditDto;
use App\Enum\CourseType;
use App\Exception\IsExistsCourseException;
use App\Repository\CourseRepository;
use App\Service\CourseService;
use App\Service\PaymentService;
//use http\Client\Response;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
class CourseController extends AbstractController
{


    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security               $security,
        private PaymentService         $paymentService,
        private CourseService          $courseService,

    ) {
    }
    #[Route('/api/v1/courses', name: 'api_courses_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/courses',
        description: 'Get list of all available courses',
        summary: 'Get courses list',
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of courses',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'code', type: 'string', example: 'course-1'),
                            new OA\Property(property: 'type', type: 'string', enum: ['free', 'rent', 'buy'], example: 'rent'),
                            new OA\Property(property: 'price', type: 'string', example: '99.99', nullable: true)
                        ],
                        type: 'object'
                    )
                )
            )
        ]
    )]
    public function list(CourseRepository $repository): JsonResponse
    {
        $courses = $repository->findAll();
        $data = [];

        foreach ($courses as $course) {
            $item = [
                'code' => $course->getCode(),
//                'type' => match ($course->getType()) {
//                    0 => 'free',
//                    1 => 'rent',
//                    2 => 'buy',
//                },
                'type' => CourseType::from($course->getType())->label(),
            ];

            if ($course->getPrice() !== null) {
                $item['price'] = number_format($course->getPrice(), 2);
            }

            $data[] = $item;
        }

        return $this->json($data);
    }
    #[Route('/api/v1/courses/{code}', name: 'api_course_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/courses/{code}',
        description: 'Get detailed information about specific course',
        summary: 'Get course details',
        parameters: [
            new OA\Parameter(
                name: 'code',
                description: 'Course code',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Course details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'code', type: 'string', example: 'course-1'),
                        new OA\Property(property: 'type', type: 'string', enum: ['free', 'rent', 'buy'], example: 'rent'),
                        new OA\Property(property: 'price', type: 'string', example: '99.99', nullable: true)
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Course not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Курс не найден')
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    public function show(string $code, CourseRepository $repository): JsonResponse
    {
        $course = $repository->findOneBy(['code' => $code]);
        if (!$course) {
            return $this->json(['error' => 'Курс не найден'], 404);
        }

        $response = [
            'code' => $course->getCode(),
//            'type' => match ($course->getType()) {
//                0 => 'free',
//                1 => 'rent',
//                2 => 'buy',
//            },
            'type' => CourseType::from($course->getType())->label(),
        ];

        if ($course->getPrice() !== null) {
            $response['price'] = number_format($course->getPrice(), 2);
        }

        return $this->json($response);
    }
    #[Route('/api/v1/courses/{code}/pay', name: 'api_course_pay', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/courses/{code}/pay',
        description: 'Purchase or rent a course',
        summary: 'Pay for course',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'code',
                description: 'Course code to purchase',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Payment successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'course_type', type: 'string', enum: ['free', 'rent', 'buy'], example: 'rent'),
                        new OA\Property(property: 'expires_at', type: 'string', format: 'date-time', nullable: true)
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Course not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Курс не найден')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 406,
                description: 'Payment error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Недостаточно средств')
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    public function pay(string $code, CourseRepository $repository, PaymentService $paymentService): JsonResponse
    {
        $course = $repository->findOneBy(['code' => $code]);
        if (!$course) {
            return $this->json(['error' => 'Курс не найден'], 404);
        }

        try {
            $transaction = $paymentService->pay($this->getUser(), $course);
        } catch (AccessDeniedHttpException $e) {
            return $this->json(['message' => $e->getMessage()], 406);
        }

        return $this->json([
            'success' => true,
//            'course_type' => match ($course->getType()) {
//                1 => 'rent',
//                2 => 'buy',
//                default => 'free',
//            },
            'course_type' => CourseType::from($course->getType())->label(),
            'expires_at' => $transaction->getTimeArend()?->format(\DateTimeInterface::ATOM),
        ]);
    }

    /**
     * @throws IsExistsCourseException
     */
    #[Route('/api/v1/courses', name: 'app_create_course', methods: ['POST'])]
    public function create(
        #[ValidateDeserialize]
        CourseCreateDto $course
    ): JsonResponse {
        $result = $this->courseService->create($course);
        if ($result) {
            return $this->json([
                'success' => true,
            ], Response::HTTP_CREATED);
        }
        return $this->json([
            'success' => false,
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    #[Route('/api/v1/courses/{code}', name: 'app_edit_course', methods: ['POST'])]
    public function edit(
        string $code,
        #[ValidateDeserialize]
        CourseEditDto $course,
    ): JsonResponse {
        $result = $this->courseService->edit($code, $course);
        if ($result) {
            return $this->json([
                'success' => true,
            ], Response::HTTP_OK);
        }
        return $this->json([
            'success' => false,
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
