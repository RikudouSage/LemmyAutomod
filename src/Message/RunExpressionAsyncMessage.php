<?php

namespace App\Message;

final readonly class RunExpressionAsyncMessage
{
    public function __construct(
        public array $context,
        public string $expression,
    ) {
    }
}
