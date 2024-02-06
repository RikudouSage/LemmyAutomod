<?php

namespace App\Message;

use App\Dto\Model\LocalUser;

final readonly class AnalyzeLocalUserMessage
{
    public function __construct(
        public LocalUser $localUser,
    ) {
    }
}
