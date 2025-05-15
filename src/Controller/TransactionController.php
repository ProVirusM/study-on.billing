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


class TransactionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
    ) {
    }

    #[Route('/api/v1/transactions', name: 'api_transactions_list', methods: ['GET'])]
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
                'type' => TransactionType::from($t->getTypeOperations())->label(),
                'course_code' => $t->getCourse()?->getCode(),
                'amount' => number_format($t->getAmount(), 2),
            ];
        }, $transactions);

        return $this->json($response);
    }

}