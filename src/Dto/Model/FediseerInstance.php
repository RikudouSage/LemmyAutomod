<?php

namespace App\Dto\Model;

final readonly class FediseerInstance
{
    public function __construct(
        public int $id,
        public string $domain,
    ) {
    }
}
