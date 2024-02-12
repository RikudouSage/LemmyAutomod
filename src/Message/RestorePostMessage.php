<?php

namespace App\Message;

final readonly class RestorePostMessage
{
    public function __construct(
        public int $postId,
    ) {
    }
}
