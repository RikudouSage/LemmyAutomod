<?php

namespace App\MessageHandler;

use App\Message\CensureInstanceOnFediseerMessage;
use App\Service\Fediseer;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CensureInstanceOnFediseerHandler
{
    public function __construct(
        private Fediseer $fediseer,
    ) {
    }

    public function __invoke(CensureInstanceOnFediseerMessage $message): void
    {
        $this->fediseer->censureInstance($message->domain, $message->reason, $message->evidence);
    }
}
