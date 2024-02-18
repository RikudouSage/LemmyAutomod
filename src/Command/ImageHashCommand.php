<?php

namespace App\Command;

use App\Service\ImageFetcher;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:image:hash')]
final class ImageHashCommand extends Command
{
    public function __construct(
        private readonly ImageFetcher $imageFetcher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'url',
                mode: InputArgument::REQUIRED,
                description: 'The URL of the image to get a hash for. The hash can be used directly with the banned images table.',
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $url = $input->getArgument('url');
        $hash = $this->imageFetcher->getImageHash($url);

        $io->success('The has was successfully generated! Copy it below:');
        $io->writeln($hash);

        return Command::SUCCESS;
    }
}
