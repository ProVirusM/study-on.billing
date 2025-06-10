<?php
namespace App\Service;

use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Course;
use App\Enum\TransactionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Service\OrderNotificationsService;
class PaymentService
{
    public function __construct(
        private EntityManagerInterface $em,
        private OrderNotificationsService $orderNotificationsService,
    ) {}

    public function deposit(User $user, float $amount): void
    {
        $this->em->wrapInTransaction(function () use ($user, $amount) {
            $transaction = new Transaction();
            $transaction->setUsers($user);
            $transaction->setAmount($amount);
            //$transaction->setTypeOperations(TransactionType::DEPOSIT->value);
            $transaction->setTypeOperations(0);
            $transaction->setCreatedAt(new \DateTimeImmutable());

            $user->setBalance($user->getBalance() + $amount);

            $this->em->persist($transaction);
            $this->em->persist($user);
        });
    }

    public function pay(User $user, Course $course): Transaction
    {
        $price = $course->getPrice() ?? 0.0;

        if ($user->getBalance() < $price) {
            throw new AccessDeniedHttpException('Недостаточно средств');
        }

        return $this->em->wrapInTransaction(function () use ($user, $course, $price) {
            $transaction = new Transaction();
            $transaction->setUsers($user);
            $transaction->setCourse($course);
            $transaction->setAmount($price);
            $transaction->setTypeOperations(TransactionType::PAYMENT->value);
            $transaction->setCreatedAt(new \DateTimeImmutable());

            if ($course->getType() === 1) { // RENT
                $transaction->setTimeArend((new \DateTimeImmutable())->modify('+7 days'));
            }
            if ($course->getType() === 2) { // RENT
                $transaction->setTimeArend(null);
            }
            if ($course->getType() === 3) { // RENT
                $transaction->setTimeArend(null);
            }

            $user->setBalance($user->getBalance() - $price);

            $this->em->persist($transaction);
            $this->em->persist($user);
            $this->orderNotificationsService->sendNotify($user, $transaction);
            return $transaction;
        });
    }
}
