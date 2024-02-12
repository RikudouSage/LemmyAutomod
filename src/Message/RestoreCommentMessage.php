<?php

namespace App\Message;

final readonly class RestoreCommentMessage
{
    public function __construct(
        public int $commentId,
    ) {
    }
}
