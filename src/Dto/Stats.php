<?php

namespace App\Dto;

final readonly class Stats
{
    public function __construct(
        public ?int $messageCount,
        public array $notificationChannels,
        public ?array $lemmyNotificationUsers,
        public bool $notifyNewUsers,
        public bool $notifyFirstPostComment,
        public bool $notifyReports,
        public bool $usesFediseer,
        public bool $aiHordeConfigured,
        public bool $signedWebhooks,
        public string $logLevel,
    ) {
    }
}
