<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class NotExistsCourse extends Constraint
{
    public string $message = 'Курс с кодом {{ value }} уже существует';
}
