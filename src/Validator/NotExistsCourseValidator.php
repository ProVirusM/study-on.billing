<?php

namespace App\Validator;

use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NotExistsCourseValidator extends ConstraintValidator
{

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var ExistsCourse $constraint */
        $course = $this->entityManager->getRepository(Course::class)->findOneBy(['code' => $value]);

        if (null === $course) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
    }
}
