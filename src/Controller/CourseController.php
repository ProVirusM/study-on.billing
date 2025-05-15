<?php


namespace App\Controller;

use App\Enum\CourseType;
use App\Repository\CourseRepository;
use App\Service\PaymentService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
class CourseController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security               $security,
        private PaymentService         $paymentService,

    ) {
    }
    #[Route('/api/v1/courses', name: 'api_courses_list', methods: ['GET'])]
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
}
