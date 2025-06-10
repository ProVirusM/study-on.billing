<?php

namespace App\Message;

class Email
{
    public function __construct(
        private string $to,
        private string $subject,
        private string $contentHTML,
    ) {
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function setTo(string $to): Email
    {
        $this->to = $to;
        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): Email
    {
        $this->subject = $subject;
        return $this;
    }

    public function getContentHTML(): string
    {
        return $this->contentHTML;
    }

    public function setContentHTML(string $contentHTML): Email
    {
        $this->contentHTML = $contentHTML;
        return $this;
    }
}
