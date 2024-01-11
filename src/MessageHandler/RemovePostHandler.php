<?php

namespace App\MessageHandler;

use App\Message\RemovePostMessage;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RemovePostHandler
{
    public function __construct(
        private LemmyApi $api,
    ) {
    }

    public function __invoke(RemovePostMessage $message): void
    {
        $this->api->moderator()->removePost(post: $message->postId);
    }
}
