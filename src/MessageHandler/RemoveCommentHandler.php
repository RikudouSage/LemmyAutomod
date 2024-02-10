<?php

namespace App\MessageHandler;

use App\Message\RemoveCommentMessage;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RemoveCommentHandler
{
    public function __construct(
        private LemmyApi $api,
    ) {
    }

    public function __invoke(RemoveCommentMessage $message): void
    {
        $this->api->moderator()->removeComment(comment: $message->commentId);
    }
}
