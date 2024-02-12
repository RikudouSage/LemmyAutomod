<?php

namespace App\Message;

final readonly class UnbanUserMessage
{
    public function __construct(
        public string $username,
        public string $instance,
    ) {
    }
}
