<?php

namespace App\Command;

use App\Service\PaymentReportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('payment:report')]
class PaymentReportCommand extends Command
{
    public function __construct(
        private PaymentReportService $paymentReportService,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addOption('month', 'm', InputOption::VALUE_OPTIONAL, 'Месяц');
        $this->addOption('year', 'y', InputOption::VALUE_OPTIONAL, 'Год');
    }


    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = new \DateTime('first day of this month');
        $month = $input->getOption('month') ?? null;
        $year = $input->getOption('year') ?? null;
        if ($month !== null && $year !== null) {
            $date = new \DateTime('01.' . $month . '.' . $year);
        }
        $this->paymentReportService->sendReport($date);
        return Command::SUCCESS;
    }
}