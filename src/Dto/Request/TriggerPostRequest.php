<?php

namespace App\Dto\Request;

final readonly class TriggerPostRequest
{
    public function __construct(
        public int $id,
    ) {
    }
}
