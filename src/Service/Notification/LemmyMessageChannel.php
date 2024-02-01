<?php

namespace App\Service\Notification;

use App\Service\LemmyverseLinkReplacer;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class LemmyMessageChannel implements NotificationChannel
{
    public function __construct(
        #[Autowire('%app.lemmy.notify_users%')]
        private array $usernames,
        #[Autowire('%app.notify.lemmy.lemmyverse_link%')]
        private bool $useLemmyverseLink,
        private LemmyApi $api,
        private LemmyverseLinkReplacer $linkReplacer,
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
        if ($this->useLemmyverseLink) {
            $message = $this->linkReplacer->replace($message);
        }
        foreach ($this->usernames as $username) {
            $this->api->currentUser()->sendPrivateMessage(
                $this->api->user()->get($username),
                $message,
            );
        }
    }
}
