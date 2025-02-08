<?php

namespace App\Message;

use Rikudou\LemmyApi\Response\Model\Community;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(transport: 'async')]
final readonly class RemoveCommunityMessage
{
    public function __construct(
        public Community $community,
        public ?string $reason,
        public bool $banMods,
    ) {
    }
}
