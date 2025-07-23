<?php

namespace App\Command;

use App\Service\ExternalRegexListManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:debug:fetch-external-list')]
final class FetchExternalListCommand extends Command
{
    public function __construct(
        private readonly ExternalRegexListManager $listManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'list-name',
                mode: InputArgument::REQUIRED,
                description: 'The name of the list to fetch',
            )
            ->addOption(
                name: 'limit',
                mode: InputOption::VALUE_REQUIRED,
                description: 'How many items to display',
                default: 100,
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $list = $this->listManager->getList(
            $this->listManager->findByName(
                $input->getArgument('list-name'),
            )
        );
        $total = count($list);

        $list = array_slice($list, 0, $input->getOption('limit'));
        foreach ($list as $item) {
            $io->writeln($item);
        }

        $io->info(sprintf('Total rows: %d', $total));
        return Command::SUCCESS;
    }
}
