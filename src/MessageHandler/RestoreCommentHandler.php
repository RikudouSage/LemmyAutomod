<?php

namespace App\MessageHandler;

use App\Message\RestoreCommentMessage;
use App\Message\RestorePostMessage;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RestoreCommentHandler
{
    public function __construct(
        private LemmyApi $api,
    ) {
    }

    public function __invoke(RestoreCommentMessage $message): void
    {
        $this->api->moderator()->restoreRemovedComment($message->commentId);
    }
}
