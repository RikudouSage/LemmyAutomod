<?php

namespace App\Message;

final readonly class AnalyzeRegistrationApplicationMessage
{
    public function __construct(
        public int $id,
    ) {
    }
}
