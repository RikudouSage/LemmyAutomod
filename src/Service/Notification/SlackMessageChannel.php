<?php

namespace App\Service\Notification;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class SlackMessageChannel implements NotificationChannel
{
    /**
     * @param array<string> $channels
     */
    public function __construct(
        #[Autowire('%app.notify.slack.token%')]
        private string $token,
        #[Autowire('%app.notify.slack.channels%')]
        private array $channels,
        private HttpClientInterface $httpClient,
    ) {
    }

    public function getName(): string
    {
        return 'slack';
    }

    public function isEnabled(): bool
    {
        return $this->token && $this->channels;
    }

    public function notify(string $message): void
    {
        foreach ($this->channels as $channel) {
            $this->httpClient->request(Request::METHOD_POST, 'https://slack.com/api/chat.postMessage', [
                'json' => [
                    'channel' => $channel,
                    'text' => $message,
                ],
                'headers' => [
                    'Authorization' => "Bearer {$this->token}",
                ],
            ]);
        }
    }
}
