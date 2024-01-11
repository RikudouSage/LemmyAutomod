<?php

namespace App\Message;

final readonly class RemovePostMessage
{
    public function __construct(
        public int $postId,
    ) {
    }
}
