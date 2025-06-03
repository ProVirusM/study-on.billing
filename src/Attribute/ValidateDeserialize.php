<?php

namespace App\Attribute;

use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Validator\Constraints\GroupSequence;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class ValidateDeserialize extends MapRequestPayload
{
    public function __construct(
        string $acceptFormat = 'json',
        array|GroupSequence|string|null $validationGroups = null
    ) {
        parent::__construct(
            acceptFormat: $acceptFormat,
            validationGroups: $validationGroups
        );
    }
}