<?php

namespace App\MessageHandler;

use App\Automod\Automod;
use App\Message\AnalyzeUserMessage;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class AnalyzeUserMessageHandler
{
    public function __construct(
        private LemmyApi $api,
        private Automod $automod,
    ) {
    }

    public function __invoke(AnalyzeUserMessage $message): void
    {
        $person = $this->api->user()->get($message->userId);
        $this->automod->analyze($person);
    }
}
