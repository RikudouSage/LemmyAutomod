<?php

namespace App\Service\Notification;

use App\Message\SendNotificationAsyncMessage;
use App\Service\Notification\NotificationChannel;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class NotificationSender
{
    /**
     * @param iterable<NotificationChannel> $channels
     */
    public function __construct(
        #[TaggedIterator('app.notification.channel')]
        private iterable $channels,
        private MessageBusInterface $messageBus,
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

    public function sendNotificationAsync(string $message): void
    {
        $this->messageBus->dispatch(new SendNotificationAsyncMessage($message));
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
