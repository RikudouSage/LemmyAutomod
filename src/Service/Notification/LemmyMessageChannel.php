<?php

namespace App\Service\Notification;

use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class LemmyMessageChannel implements NotificationChannel
{
    public function __construct(
        #[Autowire('%app.lemmy.notify_users%')]
        private array $usernames,
        private LemmyApi $api,
    ) {
    }

    public function getName(): string
    {
        return 'lemmy_message';
    }

    public function isEnabled(): bool
    {
        return count($this->usernames) > 0;
    }

    public function notify(string $message): void
    {
        foreach ($this->usernames as $username) {
            $this->api->currentUser()->sendPrivateMessage(
                $this->api->user()->get($username),
                $message,
            );
        }
    }
}
