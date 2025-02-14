<?php

namespace App\Command;

use App\Service\ImageFetcher;
use SapientPro\ImageComparator\ImageComparator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:image:similarity', description: 'Calculates the similarity between two images by their URL')]
final class ImageSimilarityCommand extends Command
{

    public function __construct(
        private readonly ImageFetcher $imageFetcher, private readonly ImageComparator $imageComparator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('image1', InputArgument::REQUIRED, description: 'The first image to compare')
            ->addArgument('image2', InputArgument::REQUIRED, description: 'The second image to compare')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $imageUrl1 = $input->getArgument('image1');
        $imageUrl2 = $input->getArgument('image2');

        $hash1 = $this->imageFetcher->getImageHash($imageUrl1);
        $hash2 = $this->imageFetcher->getImageHash($imageUrl2);

        $similarity = $this->imageComparator->compareHashStrings($hash1, $hash2);

        $io->success("The similarity is: {$similarity}%");

        return Command::SUCCESS;
    }
}
