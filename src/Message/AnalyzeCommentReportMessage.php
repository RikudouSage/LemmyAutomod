<?php

namespace App\Message;

final readonly class AnalyzeCommentReportMessage
{
    public function __construct(
        public int $commentId,
    ) {
    }
}
