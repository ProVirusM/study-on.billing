<?php

namespace App\Validator;

use App\Enum\CourseType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsTypeCourseValidator extends ConstraintValidator
{


    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value === null) {
            return;
        }

        if (CourseType::fromLabel($value) !== null) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
    }
}
