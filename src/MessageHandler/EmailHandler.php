<?php

namespace App\MessageHandler;

use App\Message\Email;
use App\Service\Email\StudyOnMailer;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class EmailHandler
{
    public function __construct(
        private StudyOnMailer $mailer,
    ) {
    }

    public function __invoke(Email $email): void
    {
        $orderMail = (new SymfonyEmail())
            ->to($email->getTo())
            ->subject($email->getSubject())
            ->html($email->getContentHTML());
        $this->mailer->send($orderMail);
    }
}
