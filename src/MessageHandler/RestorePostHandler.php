<?php

namespace App\MessageHandler;

use App\Message\RestorePostMessage;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RestorePostHandler
{
    public function __construct(
        private LemmyApi $api,
    ) {
    }

    public function __invoke(RestorePostMessage $message): void
    {
        $this->api->moderator()->restoreRemovedPost($message->postId);
    }
}
