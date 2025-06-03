<?php


namespace App\Controller;

use App\Entity\Transaction;
use App\Enum\TransactionType;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Attributes as OA;

class TransactionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
    ) {
    }

    #[Route('/api/v1/transactions', name: 'api_transactions_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/transactions',
        description: 'Get user transaction history with optional filtering',
        summary: 'Get transactions',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'filter[type]',
                description: 'Filter by transaction type',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['payment', 'deposit'])
            ),
            new OA\Parameter(
                name: 'filter[course_code]',
                description: 'Filter by course code',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'filter[skip_expired]',
                description: 'Skip expired rentals',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'boolean')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of transactions',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'type', type: 'string', enum: ['payment', 'deposit'], example: 'payment'),
                            new OA\Property(property: 'course_code', type: 'string', example: 'course-1', nullable: true),
                            new OA\Property(property: 'amount', type: 'string', example: '100.00')
                        ],
                        type: 'object'
                    )
                )
            )
        ]
    )]
    public function transactions(Request $request): JsonResponse
    {
        //$filter = $request->query->get('filter', []);
        $filter = $request->query->all('filter');

        //$user = $this->security->getUser();
        $user = $this->getUser();
        $transactions = $this->entityManager->getRepository(Transaction::class)->findFilteredByUser($user, $filter);

        $response = array_map(function (Transaction $t) {
            return [
                'id' => $t->getId(),
                'created_at' => $t->getCreatedAt()->format(\DateTimeInterface::ATOM),
                'time_arend' => $t->getTimeArend()?->format(\DateTimeInterface::ATOM),
                'type' => TransactionType::from($t->getTypeOperations())->label(),
                'course_code' => $t->getCourse()?->getCode(),
                'amount' => number_format($t->getAmount(), 2),
            ];
        }, $transactions);

        return $this->json($response);
    }

}