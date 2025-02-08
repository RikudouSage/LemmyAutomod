<?php

namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(transport: 'async')]
final readonly class AnalyzeCommunityMessage
{
    public function __construct(
        public int $communityId,
    ) {
    }
}
