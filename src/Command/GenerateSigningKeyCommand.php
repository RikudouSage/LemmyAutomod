<?php

namespace App\Command;

use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:generate:signing-key', description: 'Generates signing key that can be used for signing incoming webhook requests.')]
final class GenerateSigningKeyCommand extends Command
{
    #[Override]
    protected function configure(): void
    {
        $this
            ->addOption(
                name: 'bits',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The amount of bits the key will have. Must be divisible by 8.',
                default: 512,
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $bits = $input->getOption('bits');
        if (!is_numeric($bits) || $bits % 8 !== 0) {
            $io->error('The value of --bits must be a number divisible by 8.');
            return Command::FAILURE;
        }

        $key = base64_encode(random_bytes($bits / 8));
        $key = "whsec_{$key}";

        $io->writeln($key);

        return Command::SUCCESS;
    }
}
