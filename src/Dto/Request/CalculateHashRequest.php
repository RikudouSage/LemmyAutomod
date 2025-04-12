<?php

namespace App\Dto\Request;

final readonly class CalculateHashRequest
{
    public function __construct(
        public string $imageUrl,
    ) {
    }
}
