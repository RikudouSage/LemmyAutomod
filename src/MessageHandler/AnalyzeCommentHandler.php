<?php

namespace App\MessageHandler;

use App\Automod\Automod;
use App\Message\AnalyzeCommentMessage;
use App\Message\AnalyzePostMessage;
use App\Service\IgnoredUserManager;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class AnalyzeCommentHandler
{
    public function __construct(
        private LemmyApi $api,
        private Automod $automod,
        private IgnoredUserManager $ignoredUserManager,
    ) {
    }

    public function __invoke(AnalyzeCommentMessage $message): void
    {
        $comment = $this->api->comment()->get($message->commentId);
        if ($this->ignoredUserManager->shouldBeIgnored($comment->creator)) {
            return;
        }
        $this->automod->analyze($comment);
    }
}
