<?php

namespace App\Service;

use App\Repository\UserRepository;
use App\Service\Email\StudyOnMailer;
use App\Service\Twig\Twig;
use Symfony\Component\Mime\Email;

class RentNotificationsService
{
    public function __construct(
        private Twig $twig,
        private StudyOnMailer $mailer,
        private UserRepository $userRepository
    ) {
    }

    public function sendNotifications(): void
    {
        $courses = $this->userRepository->findUsersWithExpiresCourses();
        foreach ($courses as $user => $course) {
            $this->sendEmail($user, $course);
        }
    }

    /**
     * @param string $to
     * @param array<string, mixed> $data
     * @return void
     */
    private function sendEmail(string $to, array $data): void
    {
        $notify = $this->generateNotifications($data);
        $email = (new Email())
            ->to($to)
            ->subject('Уведомление об окончании аренды курсов')
            ->html($notify);
        $this->mailer->send($email);
    }

    private function generateNotifications(array $data): string
    {
        foreach ($data as $key => $item) {
            $data[$key]['time_arend'] = date("d.m.Y H:i", $item['time_arend']->getTimestamp());
        }
        return $this->twig->render(
            'rent.notify.html.twig',
            [
                'courses' => $data,
            ]
        );
    }
}
