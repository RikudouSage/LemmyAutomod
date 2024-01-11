<?php

namespace App\Message;

final readonly class AnalyzePostMessage
{
    public function __construct(
        public int $postId,
    ) {
    }
}
