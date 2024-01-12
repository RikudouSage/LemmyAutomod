<?php

namespace App\Service\Notification;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.notification.channel')]
interface NotificationChannel
{
    public function getName(): string;
    public function isEnabled(): bool;
    public function notify(string $message): void;
}
