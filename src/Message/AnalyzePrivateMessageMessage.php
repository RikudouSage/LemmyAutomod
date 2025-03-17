<?php

namespace App\Message;

use App\Dto\Model\PrivateMessage;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(transport: 'async')]
final readonly class AnalyzePrivateMessageMessage
{
    public function __construct(
        public PrivateMessage $message,
    ) {
    }
}
