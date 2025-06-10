<?php

namespace App\Service;

use App\Repository\CourseRepository;
use App\Service\Email\StudyOnMailer;
use App\Service\Twig\Twig;
use Symfony\Component\Mime\Email;

class PaymentReportService
{
    public function __construct(
        private StudyOnMailer $mailer,
        private CourseRepository $courseRepository,
        private Twig $twig,
        private string $reportMail,
    ) {
    }

    public function sendReport(\DateTime $date): void
    {
        $dateStart = $date->format('d.m.Y');
        $dateEnd = (clone $date)->modify('+1 month')->format('d.m.Y');
        $courses = $this->courseRepository->findAnalyzesCourses($date);
        $totalSum = 0;
        foreach ($courses as $course) {
            $totalSum += (int)$course['sum'];
        }
        $report = $this->generateReport($courses, $dateStart, $dateEnd, $totalSum);
        $this->sendEmail($report);
    }

    private function sendEmail(string $report): void
    {
        $email = (new Email())
            ->to($this->reportMail)
            ->subject('Отчет по продажам за месяц')
            ->html($report);
        $this->mailer->send($email);
    }

    private function generateReport(array $courses, string $dateStart, string $dateEnd, int $totalSum): string
    {
        return $this->twig->render(
            'payment.report.html.twig',
            [
                'courses' => $courses,
                'dateStart' => $dateStart,
                'dateEnd' => $dateEnd,
                'totalSum' => $totalSum,
            ]
        );
    }
}
