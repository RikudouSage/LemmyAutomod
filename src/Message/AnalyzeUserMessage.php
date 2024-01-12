<?php

namespace App\Message;

final readonly class AnalyzeUserMessage
{
    public function __construct(
        public int $userId,
    ) {
    }
}
