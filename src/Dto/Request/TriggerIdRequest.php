<?php

namespace App\Dto\Request;

final readonly class TriggerIdRequest
{
    public function __construct(
        public int $id,
    ) {
    }
}
