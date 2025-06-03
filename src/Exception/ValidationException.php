<?php

namespace App\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends \Exception
{
    public function __construct(
        string $message,
        public ConstraintViolationListInterface $validationResult,
        int $code = 0,
    ) {
        parent::__construct($message, $code);
    }
}
