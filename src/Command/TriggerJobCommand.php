<?php

namespace App\Command;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use LogicException;
use ReflectionClass;
use ReflectionNamedType;
use Rikudou\LemmyApi\LemmyApi;
use Rikudou\LemmyApi\Response\Model\Person;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use TypeError;

#[AsCommand(name: 'app:trigger-job', description: 'Triggers an arbitrary job by providing a class and its arguments')]
final class TriggerJobCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly LemmyApi $api,
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
        $message = $this->createInstance($class, $args);
        $this->messageBus->dispatch($message, $badges);

        $io->success('Successfully dispatched the job.');
        return Command::SUCCESS;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @param array<string> $args
     * @return T
     */
    private function createInstance(string $class, array $args): object
    {
        try {
            return new $class(...$args);
        } catch (TypeError) {
            $reflection = new ReflectionClass($class);
            $arguments = $reflection->getConstructor()?->getParameters() ?? throw new LogicException('Not constructor found');
            $namedParameters = !array_is_list($args);
            $i = 0;
            foreach ($arguments as $argument) {
                $type = $argument->getType();
                if (!$type instanceof ReflectionNamedType) {
                    throw new LogicException('Can only handle single (or nullable) types for construction');
                }
                $value = $namedParameters ? $args[$argument->getName()] : $args[$i];
                $type = $type->getName();

                if (is_a($type, DateTime::class, true)) {
                    $value = new DateTime($value);
                } else if (is_a($type, DateTimeInterface::class, true)) {
                    $value = new DateTimeImmutable($value);
                } else if (is_a($type, Person::class, true)) {
                    $value = $this->api->user()->get($value);
                } else if ($type === 'bool') {
                    $value = $value === 'true';
                } else if (class_exists($type) && is_string($value) && is_array(json_decode($value, true))) {
                    $value = $this->createInstance($type, json_decode($value, true));
                }

                $namedParameters ? ($args[$argument->getName()] = $value) : ($args[$i] = $value);
                ++$i;
            }

            return new $class(...$args);
        }
    }
}
