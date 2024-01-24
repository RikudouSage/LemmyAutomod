<?php

namespace App\Command;

use App\Service\Notification\NotificationSender;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:test:notification')]
final class TestNotificationCommand extends Command
{
    public function __construct(
        private readonly NotificationSender $notificationSender,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(name: 'message', mode: InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->notificationSender->sendNotification($input->getArgument('message'));

        return Command::SUCCESS;
    }
}
