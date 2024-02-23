<?php

namespace App\Message;

final readonly class AnalyzeInstanceMessage
{
    public function __construct(
        public string $instance,
    ) {
    }
}
