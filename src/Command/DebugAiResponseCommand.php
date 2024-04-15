<?php

namespace App\Command;

use App\Enum\AiModel;
use App\Service\AiHorde\AiHorde;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:debug-ai')]
final class DebugAiResponseCommand extends Command
{
    public function __construct(
        private readonly AiHorde $aiHorde,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('model', 'm', InputOption::VALUE_REQUIRED, 'Model name')
            ->addArgument('prompt', InputArgument::OPTIONAL)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $question = new Question('Prompt');
        $question->setMultiline(true);
        $prompt = $input->getArgument('prompt') ?? $io->askQuestion($question);
        $requestedModel = $input->getOption('model');

        if (!$prompt) {
            $io->error('No prompt');
            return Command::FAILURE;
        }

        $models = array_filter(AiModel::cases(), function (AiModel $model) use ($requestedModel) {
            return !$requestedModel || $model->value === $requestedModel;
        });
        $models = array_filter($models, function (AiModel $model) {
            return count($this->aiHorde->findModels($model)) > 0;
        });
        shuffle($models);

        if (!count($models)) {
            $io->error("No available worker for given model");
            return Command::FAILURE;
        }
        $model = $models[array_key_first($models)];

        $io->comment("Prompt: {$prompt}");
        $io->comment("Using model: {$model->value}");

        $io->success($this->aiHorde->getResponse(
            $prompt,
            $model,
        ));

        return Command::SUCCESS;
    }
}
