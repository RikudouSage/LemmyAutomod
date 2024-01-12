<?php

namespace App\MessageHandler;

use App\Message\SendNotificationAsyncMessage;
use App\Service\Notification\NotificationSender;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendNotificationAsyncHandler
{
    public function __construct(
        private NotificationSender $notificationSender,
    ) {
    }

    public function __invoke(SendNotificationAsyncMessage $message): void
    {
        $this->notificationSender->sendNotification($message->message);
    }
}
