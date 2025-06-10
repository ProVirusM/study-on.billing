<?php


namespace App\Service;

use App\Dto\CourseCreateDto;
use App\Dto\CourseEditDto;
use App\Entity\Course;
use App\Enum\CourseType;
use App\Exception\IsExistsCourseException;
use App\Repository\CourseRepository;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CourseService
{
    public function __construct(
        private CourseRepository $courseRepository,
    )
    {
    }

    public function create(CourseCreateDto $courseDto): bool
    {
        $course = $this->courseRepository->findOneBy(['code' => $courseDto->getCode()]);
        if ($course !== null) {
            throw new IsExistsCourseException;
        }
        $course = new Course();
        $course->setTitle($courseDto->getTitle())
            ->setCode($courseDto->getCode())
            ->setType(CourseType::byString($courseDto->getType())->code())
            ->setPrice($courseDto->getPrice());
        return $this->courseRepository->persistCourse($course);
    }
    public function edit(string $code, CourseEditDto $courseDto): bool
    {
        $course = $this->courseRepository->findOneBy(['code' => $code]);
        if ($course === null) {
            throw new HttpException(404, 'Курс не найден');
        }

        // Проверяем, меняется ли код
        if ($courseDto->getCode() !== null && $courseDto->getCode() !== $course->getCode()) {
            // Проверяем, что новый код не занят другим курсом
            $existingCourse = $this->courseRepository->findOneBy(['code' => $courseDto->getCode()]);
            if ($existingCourse !== null) {
                throw new HttpException(422, 'Курс с таким кодом уже существует');
            }
        }

        $course = $this->updateFieldCourse($course, $courseDto);

        return $this->courseRepository->persistCourse($course);
    }

    private function updateFieldCourse(Course $course, CourseEditDto $courseDto): Course
    {
        $type = null;
        if ($courseDto->getType() !== null) {
            $type = CourseType::fromLabel($courseDto->getType())->code();
        }

        $course->setTitle($courseDto->getTitle() ?? $course->getTitle());

        // Обновляем code только если он передан в dto и отличается от текущего
        if ($courseDto->getCode() !== null && $courseDto->getCode() !== $course->getCode()) {
            $course->setCode($courseDto->getCode());
        }

        $course->setType($type ?? $course->getType())
            ->setPrice($courseDto->getPrice() ?? $course->getPrice());

        return $course;
    }

}