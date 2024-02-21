<?php

namespace App\MessageHandler;

use App\Automod\Automod;
use App\Message\AnalyzeUserMessage;
use App\Service\IgnoredUserManager;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class AnalyzeUserMessageHandler
{
    public function __construct(
        private LemmyApi $api,
        private Automod $automod,
        private IgnoredUserManager $ignoredUserManager,
    ) {
    }

    public function __invoke(AnalyzeUserMessage $message): void
    {
        $person = $this->api->user()->get($message->userId);
        if ($this->ignoredUserManager->shouldBeIgnored($person)) {
            return;
        }
        $this->automod->analyze($person);
    }
}
