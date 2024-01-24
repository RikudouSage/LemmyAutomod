<?php

namespace App\Message;

final readonly class AnalyzePostReportMessage
{
    public function __construct(
        public int $postId,
    ) {
    }
}
