<?php

namespace App\Message;

use DateTimeInterface;

final readonly class ReanalyzePostsMessage
{
    public function __construct(
        public DateTimeInterface $since,
    ) {
    }
}
