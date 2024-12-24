<?php

namespace App\Service\AiHorde;

use App\Enum\AiActor;
use App\Enum\AiModel;
use App\Service\AiHorde\Message\Message;
use App\Service\AiHorde\Message\MessageHistory;
use App\Service\AiHorde\MessageFormatter\MessageFormatter;
use LogicException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class AiHorde
{
    /**
     * @param iterable<MessageFormatter> $formatters
     */
    public function __construct(
        private HttpClientInterface $httpClient,
        #[TaggedIterator('app.message_formatter')]
        private iterable $formatters,
        #[Autowire('%app.ai_horde.api_key%')]
        private string $apiKey,
    ) {
    }

    public function getResponse(
        string $message,
        AiModel $model,
        MessageHistory $history = new MessageHistory(),
    ): Message {
        if (!$this->apiKey) {
            throw new LogicException('There is no api key set, cannot use AI actions');
        }

        $models = $this->findModels($model);
        if (!count($models)) {
            throw new LogicException('There was an error while looking for available models - no model able to handle your message seems to be online. Please try again later.');
        }
        $formatter = $this->findFormatter($model) ?? throw new LogicException("Could not find formatter for {$model->value}");
        [$maxLength, $maxContextLength] = $this->getMaxLength($model);

        $response = $this->httpClient->request(Request::METHOD_POST, 'https://aihorde.net/api/v2/generate/text/async', [
            'json' => [
                'prompt' => $formatter->getPrompt(new MessageHistory(
                    ...[...$history, new Message(role: AiActor::User, content: $message)],
                )),
                'params' => [
                    'max_length' => $maxLength,
                    'max_context_length' => $maxContextLength,
                ],
                'models' => $models,
            ],
            'headers' => [
                'apikey' => $this->apiKey,
            ],
        ]);
        $json = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $jobId = $json['id'];

        do {
            $response = $this->httpClient->request(Request::METHOD_GET, "https://aihorde.net/api/v2/generate/text/status/{$jobId}", [
                'headers' => [
                    'apikey' => $this->apiKey,
                ],
            ]);
            $json = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);
            if (!$json['done']) {
                sleep(1);
            }
        } while (!$json['done']);

        if (!isset($json['generations'][0])) {
            throw new LogicException('Missing generations output');
        }

        return $formatter->formatOutput($json['generations'][0]['text']);
    }

    /**
     * @return array<string>
     */
    public function findModels(AiModel $model): array
    {
        $response = $this->httpClient->request(Request::METHOD_GET, 'https://aihorde.net/api/v2/status/models?type=text');
        $json = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);

        return array_values(array_map(
            fn (array $modelData) => $modelData['name'],
            array_filter($json, fn (array $modelData) => fnmatch("*/{$model->value}", $modelData['name'])),
        ));
    }

    private function findFormatter(AiModel $model): ?MessageFormatter
    {
        foreach ($this->formatters as $formatter) {
            if ($formatter->supports($model)) {
                return $formatter;
            }
        }

        return null;
    }

    private function getMaxLength(AiModel $model): array
    {
        $response = $this->httpClient->request(Request::METHOD_GET, 'https://aihorde.net/api/v2/workers?type=text');
        $json = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $workers = array_filter(
            $json,
            fn (array $worker) => count(array_filter(
                    $worker['models'],
                    fn (string $modelName) => fnmatch("*/{$model->value}", $modelName),
                )) > 0,
        );
        $targetLength = 512;
        $targetContext = 2048;

        if (!count(array_filter($workers, fn(array $worker) => $worker['max_length'] >= $targetLength))) {
            $targetLength = max(array_map(fn (array $worker) => $worker['max_length'], $workers));
        }
        if (!count(array_filter($workers, fn(array $worker) => $worker['max_context_length'] >= $targetContext))) {
            $targetContext = max(array_map(fn (array $worker) => $worker['max_context_length'], $workers));
        }

        if ($targetLength > $targetContext / 2) {
            $targetLength = $targetContext  / 2;
        }

        return [$targetLength, $targetContext];
    }
}
