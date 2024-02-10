<?php

namespace App\Message;

final readonly class RemoveCommentMessage
{
    public function __construct(
        public int $commentId,
    ) {
    }
}
