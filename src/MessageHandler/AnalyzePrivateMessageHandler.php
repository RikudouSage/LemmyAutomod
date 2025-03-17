<?php

namespace App\MessageHandler;

use App\Automod\Automod;
use App\Message\AnalyzePrivateMessageMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class AnalyzePrivateMessageHandler
{
    public function __construct(
        private Automod $automod
    ) {
    }

    public function __invoke(AnalyzePrivateMessageMessage $message): void
    {
        $this->automod->analyze($message->message);
    }
}
