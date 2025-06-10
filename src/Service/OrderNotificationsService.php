<?php

namespace App\Service;

use App\Entity\Course;
use App\Entity\Transaction;
use App\Entity\User;
use App\Message\Email;
use App\Service\Twig\Twig;
use Symfony\Component\Messenger\MessageBusInterface;

class OrderNotificationsService
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private Twig $twig,
    ) {
    }

    public function sendNotify(User $user, Transaction $transaction): void
    {
        $course = $transaction->getCourse();
        if (!$course) {
            return;
        }
        $contentHTML = $this->generateNotify($transaction);
        $orderEmail = new Email(
            to: $user->getEmail(),
            subject: 'Уведомление о покупке/аренде курса',
            contentHTML: $contentHTML,
        );
        $this->messageBus->dispatch($orderEmail);
    }

    public function generateNotify(Transaction $transaction): string
    {
        return $this->twig->render(
            'order.notify.html.twig',
            [
                'courseTitle' => $transaction->getCourse()->getTitle(),
                'date' => $transaction->getCreatedAt()->format('d.m.Y H:i'),
                'sum' => $transaction->getAmount(),
            ]
        );
    }
}
