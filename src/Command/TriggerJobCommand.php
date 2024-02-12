<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

#[AsCommand(name: 'app:trigger-job', description: 'Triggers an arbitrary job by providing a class and its arguments')]
final class TriggerJobCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'job-class',
                mode: InputArgument::REQUIRED,
                description: 'The job class to generate the job from',
            )
            ->addOption(
                name: 'arg',
                mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                description: 'The arguments to supply to the constructor of the job',
            )
            ->addOption(
                name: 'sync',
                mode: InputOption::VALUE_NONE,
                description: 'When this flag is present, the job will run synchronously instead',
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $class = $input->getArgument('job-class');
        $args = $input->getOption('arg');

        if (!class_exists($class)) {
            $io->error("The class '{$class}' does not exist.");
            return Command::FAILURE;
        }

        $badges = $input->getOption('sync') ? [new TransportNamesStamp(['sync'])] : [];
        $message = new $class(...$args);
        $this->messageBus->dispatch($message, $badges);

        $io->success('Successfully dispatched the job.');
        return Command::SUCCESS;
    }
}
