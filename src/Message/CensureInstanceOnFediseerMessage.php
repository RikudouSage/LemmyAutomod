<?php

namespace App\Message;

final readonly class CensureInstanceOnFediseerMessage
{
    public function __construct(
        public string $domain,
        public ?string $reason,
        public ?string $evidence,
    ) {
    }
}
