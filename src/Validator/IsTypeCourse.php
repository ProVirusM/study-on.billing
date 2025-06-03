<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute()]
class IsTypeCourse extends Constraint
{
    public string $message = 'Тип курса {{ value }} не существует.';
}
