<?php

namespace App\MessageHandler;

use App\Automod\Automod;
use App\Message\AnalyzeCommunityMessage;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class AnalyzeCommunityHandler
{
    public function __construct(
        private LemmyApi $api,
        private Automod $automod,
    ) {
    }

    public function __invoke(AnalyzeCommunityMessage $message): void
    {
        $community = $this->api->community()->get($message->communityId);
        $this->automod->analyze($community->community);
    }
}
