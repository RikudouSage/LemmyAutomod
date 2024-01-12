<?php

namespace App\Service\Notification;

use App\Service\Notification\NotificationChannel;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final readonly class NotificationSender
{
    /**
     * @param iterable<NotificationChannel> $channels
     */
    public function __construct(
        #[TaggedIterator('app.notification.channel')]
        private iterable $channels,
    ) {
    }

    public function sendNotification(string $message): void
    {
        foreach ($this->channels as $channel) {
            if (!$channel->isEnabled()) {
                continue;
            }

            $channel->notify($message);
        }
    }

    public function hasEnabledChannels(): bool
    {
        foreach ($this->channels as $channel) {
            if ($channel->isEnabled()) {
                return true;
            }
        }

        return false;
    }
}
