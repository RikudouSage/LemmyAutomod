<?php

namespace App\MessageHandler;

use App\Message\BanUserMessage;
use App\Message\RemoveCommunityMessage;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

#[AsMessageHandler]
final readonly class RemoveCommunityHandler
{
    public function __construct(
        private LemmyApi            $api,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(RemoveCommunityMessage $message): void
    {
        if ($message->banMods) {
            $mods = $this->api->community()->getModerators($message->community);
            foreach ($mods as $mod) {
                $this->messageBus->dispatch(new BanUserMessage(
                    user: $mod,
                    reason: $message->reason,
                    removePosts: true,
                    removeComments: true,
                ), [new DispatchAfterCurrentBusStamp()]);
            }
        }

        $this->api->moderator()->removeCommunity(
            community: $message->community,
            reason: $message->reason,
        );
    }
}
