<?php

namespace App\Dto\Model;

final readonly class PrivateMessage
{
    public function __construct(
        public int    $id,
        public int    $creatorId,
        public int    $recipientId,
        public bool   $local,
        public string $content,
    ) {
    }
}
