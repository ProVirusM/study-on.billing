<?php

namespace App\Dto;

use App\Validator\IsTypeCourse;

class CourseEditDto
{
    public function __construct(
        private ?string $title = null,
        private ?string $code = null,
        #[IsTypeCourse]
        private ?string $type = null,
        private ?float $price = null,
    ) {
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): CourseEditDto
    {
        $this->title = $title;
        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): CourseEditDto
    {
        $this->code = $code;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): CourseEditDto
    {
        $this->type = $type;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): CourseEditDto
    {
        $this->price = $price;
        return $this;
    }

}
