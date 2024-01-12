<?php

namespace App\Message;

final readonly class SendNotificationAsyncMessage
{
    public function __construct(
        public string $message,
    ) {
    }
}
