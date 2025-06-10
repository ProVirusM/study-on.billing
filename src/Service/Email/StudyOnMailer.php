<?php

namespace App\Service\Email;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;

#[AsDecorator(MailerInterface::class)]
class StudyOnMailer implements MailerInterface
{
    public function __construct(
        #[AutowireDecorated]
        private MailerInterface $inner,
        private string $mailFrom
    ) {
    }

    /**
     * @param \Symfony\Component\Mime\Email $message
     * @param Envelope|null $envelope
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     *
     * @return void
     */
    public function send(RawMessage $message, ?Envelope $envelope = null): void
    {
        $message->from($this->mailFrom);
        $this->inner->send($message, $envelope);
    }
}
