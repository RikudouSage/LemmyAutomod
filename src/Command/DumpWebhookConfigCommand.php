<?php

namespace App\Command;

use App\Attribute\WebhookConfig;
use LogicException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Yaml\Yaml;

#[AsCommand('app:dump-webhooks')]
final class DumpWebhookConfigCommand extends Command
{
    /**
     * @param iterable<object> $controllers
     */
    public function __construct(
        #[TaggedIterator('controller.service_arguments')]
        private readonly iterable $controllers,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'baseUrl',
                mode: InputArgument::OPTIONAL,
                description: "The base url that will be used as a prefix. Can be a full valid URL if you communicate over the internet, or something like http://docker_container_name if you're communicating over the internal docker network.",
                default: 'http://automod',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $baseUrl = $input->getArgument('baseUrl');

        $result = ['webhooks' => []];
        foreach ($this->controllers as $controller) {
            $reflection = new ReflectionObject($controller);
            $controllerRouteConfig = $this->getAttribute($reflection, Route::class);
            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if (!$webhookConfig = $this->getAttribute($method, WebhookConfig::class)) {
                    continue;
                }
                if (!$routeConfig = $this->getAttribute($method, Route::class)) {
                    continue;
                }
                if (!$routeConfig->getName()) {
                    throw new LogicException('Route without name found: ' . $reflection->getName() . '::' . $method->getName());
                }
                if (count($routeConfig->getMethods()) !== 1) {
                    throw new LogicException('Exactly one method is required: ' . $reflection->getName() . '::' . $method->getName());
                }

                $url = $routeConfig->getPath();
                if (!str_starts_with($url, '/')) {
                    $url = "/{$url}";
                }
                if ($controllerRouteConfig !== null) {
                    $url = "{$controllerRouteConfig->getPath()}{$url}";
                }
                if (!str_starts_with($url, '/')) {
                    $url = "/{$url}";
                }

                $config = [
                    'uniqueMachineName' => $routeConfig->getName(),
                    'url' => "{$baseUrl}{$url}",
                    'method' => $routeConfig->getMethods()[array_key_first($routeConfig->getMethods())],
                    'objectType' => $webhookConfig->objectType,
                    'operation' => $webhookConfig->operation,
                ];
                if ($webhookConfig->bodyExpression) {
                    $config['bodyExpression'] = $webhookConfig->bodyExpression;
                }
                if ($webhookConfig->filterExpression) {
                    $config['filterExpression'] = $webhookConfig->filterExpression;
                }
                if ($webhookConfig->enhancedFilter) {
                    $config['enhancedFilterExpression'] = $webhookConfig->enhancedFilter;
                }
                $result['webhooks'][] = $config;
            }
        }

        $yaml = Yaml::dump($result, inline: 5, flags: Yaml::DUMP_NULL_AS_TILDE);

        $io->success('The yaml config has been successfully generated! Please copy the whole code block to the UI:');
        $io->writeln($yaml);

        return Command::SUCCESS;
    }

    /**
     * @template TAttribute of object
     * @param ReflectionMethod|ReflectionClass<object> $target
     * @param class-string<TAttribute> $attribute
     * @return TAttribute|null
     */
    private function getAttribute(ReflectionMethod|ReflectionClass $target, string $attribute): ?object
    {
        $attributes = $target->getAttributes($attribute);
        if (!count($attributes)) {
            return null;
        }

        return $attributes[array_key_first($attributes)]->newInstance();
    }
}
