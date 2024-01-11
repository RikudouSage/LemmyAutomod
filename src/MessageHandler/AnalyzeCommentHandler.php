<?php

namespace App\MessageHandler;

use App\Automod\Automod;
use App\Message\AnalyzeCommentMessage;
use App\Message\AnalyzePostMessage;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class AnalyzeCommentHandler
{
    public function __construct(
        private LemmyApi $api,
        private Automod $automod,
    ) {
    }

    public function __invoke(AnalyzeCommentMessage $message): void
    {
        $comment = $this->api->comment()->get($message->commentId);
        $this->automod->analyze($comment);
    }
}
