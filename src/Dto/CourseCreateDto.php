<?php

namespace App\Dto;

use App\Validator\IsTypeCourse;
use App\Validator\NotExistsCourse;
use Symfony\Component\Validator\Constraints as Assert;

class CourseCreateDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Поле название курса не должно быть пустым.')]
        #[Assert\Length(
            min: 2,
            max: 255,
            minMessage: 'Название должно сотоять как минимум из 2 символов.',
            maxMessage: 'Название должно состоять максимум из 255 символов.'
        )]
        private string $title,
        #[Assert\NotBlank(message: 'Поле код курса не может быть пустым.')]
        #[NotExistsCourse]
        private string $code,
        #[IsTypeCourse]
        private string $type,
        private ?float $price,
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): CourseCreateDto
    {
        $this->title = $title;
        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): CourseCreateDto
    {
        $this->code = $code;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): CourseCreateDto
    {
        $this->type = $type;
        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): CourseCreateDto
    {
        $this->price = $price;
        return $this;
    }
}
