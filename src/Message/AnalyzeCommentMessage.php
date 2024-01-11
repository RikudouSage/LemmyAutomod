<?php

namespace App\Message;

final readonly class AnalyzeCommentMessage
{
    public function __construct(
        public int $commentId,
    ) {
    }
}
