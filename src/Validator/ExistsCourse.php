<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ExistsCourse extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public string $message = 'Курс {{ value }} не существует';
}
