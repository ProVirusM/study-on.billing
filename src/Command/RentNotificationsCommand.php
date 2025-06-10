<?php

namespace App\Command;

use App\Service\RentNotificationsService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('payment:ending:notification')]
class RentNotificationsCommand extends Command
{
    public function __construct(
        private RentNotificationsService $rentNotificationsService,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->rentNotificationsService->sendNotifications();
        return Command::SUCCESS;
    }
}
